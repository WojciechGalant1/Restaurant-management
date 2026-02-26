<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemCancellationRequest;
use App\Models\Reservation;
use App\Models\Shift;
use App\Models\ShiftClockIn;
use App\Models\Table;
use App\Models\User;
use App\Services\Dashboard\KitchenStatsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    private const CACHE_TTL = 60;
    private const CANCELLED_ALERT_MINUTES = 5;
    private const PENDING_CRITICAL_MINUTES = 20;
    private const HOST_SEATED_RECENT_MINUTES = 10;
    private const UNSERVED_TABLE_MINUTES = 10;
    private const UPCOMING_RESERVATION_MIN = 10;
    private const UPCOMING_RESERVATION_MAX = 20;
    private const NO_SHOW_MINUTES = 20;
    private const STAFF_LATE_CLOCKIN_MINUTES = 15;

    public function __construct(
        private KitchenStatsService $kitchenStats
    ) {}

    public function getAlertsForUser(User $user): array
    {
        $cacheKey = 'notifications:user:' . $user->id;
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $alerts = [];

            if (in_array($user->role, [UserRole::Chef, UserRole::Bartender])) {
                $alerts = array_merge($alerts, $this->getChefAlerts());
            }
            if ($user->role === UserRole::Waiter) {
                $alerts = array_merge($alerts, $this->getWaiterAlerts($user));
            }
            if ($user->role === UserRole::Host) {
                $alerts = array_merge($alerts, $this->getHostAlerts());
            }
            if ($user->role === UserRole::Manager) {
                $alerts = array_merge($alerts, $this->getManagerAlerts());
            }

            return $alerts;
        });
    }

    public function clearCacheForUser(int $userId): void
    {
        Cache::forget('notifications:user:' . $userId);
    }

    public function clearAllNotificationCaches(): void
    {
        // Called when data changes; in production you might use cache tags
        Cache::flush();
    }

    /** @return array<int, array{type: string, message: string, link: string, severity: string, data?: array}> */
    private function getChefAlerts(): array
    {
        $alerts = [];

        // Order item cancelled (last 5 min)
        $cutoff = now()->subMinutes(self::CANCELLED_ALERT_MINUTES);
        $cancelledItems = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', OrderItemStatus::Cancelled)
            ->where('updated_at', '>=', $cutoff)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        foreach ($cancelledItems as $item) {
            $tableNum = $item->order?->table?->table_number ?? '?';
            $dishName = $item->menuItem?->dish?->name ?? __('Item');
            $alerts[] = [
                'type' => 'order_item_cancelled',
                'message' => __(':emoji Pilne: Stolik nr :table – Anulowano :dish', [
                    'emoji' => '⚠️',
                    'table' => $tableNum,
                    'dish' => $dishName,
                ]),
                'link' => route('kitchen.index'),
                'severity' => 'critical',
                'data' => ['order_item_id' => $item->id],
            ];
        }

        // Order pending 20+ min
        $cutoff20 = now()->subMinutes(self::PENDING_CRITICAL_MINUTES);
        $longWaiting = OrderItem::with(['order.table'])
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing])
            ->where('created_at', '<', $cutoff20)
            ->get()
            ->groupBy(fn ($i) => $i->order?->table?->table_number ?? $i->order_id);

        foreach ($longWaiting as $tableKey => $items) {
            $first = $items->first();
            $tableNum = $first->order?->table?->table_number ?? $tableKey;
            $minutes = (int) $first->created_at->diffInMinutes(now());
            $alerts[] = [
                'type' => 'order_pending_20min',
                'message' => __('Stolik nr :table – Czekają już :min minut!', [
                    'table' => $tableNum,
                    'min' => $minutes,
                ]),
                'link' => route('kitchen.index'),
                'severity' => 'warning',
                'data' => ['table_number' => $tableNum],
            ];
        }

        return $alerts;
    }

    /** @return array<int, array{type: string, message: string, link: string, severity: string, data?: array}> */
    private function getWaiterAlerts(User $waiter): array
    {
        $alerts = [];
        $tableIds = Table::forWaiter($waiter)->pluck('id');

        if ($tableIds->isEmpty()) {
            return $alerts;
        }

        // Dishes ready
        $readyItems = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', OrderItemStatus::Ready)
            ->whereHas('order', fn ($q) => $q->whereIn('table_id', $tableIds))
            ->get()
            ->groupBy(fn ($i) => $i->order?->table_id);

        foreach ($readyItems as $tableId => $items) {
            $table = $items->first()->order?->table;
            $tableNum = $table?->table_number ?? $tableId;
            $alerts[] = [
                'type' => 'dishes_ready',
                'message' => __('Dania dla stolika nr :table są gotowe do odbioru!', ['table' => $tableNum]),
                'link' => route('waiter.index'),
                'severity' => 'critical',
                'data' => ['table_id' => $tableId],
            ];
        }

        // Host seated guests (reservation Seated in last 10 min, table assigned to waiter)
        $recentSeated = now()->subMinutes(self::HOST_SEATED_RECENT_MINUTES);
        $seatedReservations = Reservation::with('table')
            ->whereIn('table_id', $tableIds)
            ->whereIn('status', [ReservationStatus::Seated, ReservationStatus::WalkInSeated])
            ->where('updated_at', '>=', $recentSeated)
            ->get();

        foreach ($seatedReservations as $r) {
            $tableNum = $r->table?->table_number ?? $r->table_id;
            $alerts[] = [
                'type' => 'host_seated_guests',
                'message' => __('Host posadził gości przy Twoim stoliku nr :table', ['table' => $tableNum]),
                'link' => route('waiter.index'),
                'severity' => 'info',
                'data' => ['reservation_id' => $r->id],
            ];
        }

        // Unserved table: assigned table with Seated/WalkInSeated, no order or order with 0 items, >10 min
        $cutoff = now()->subMinutes(self::UNSERVED_TABLE_MINUTES);
        $tablesWithReservations = Reservation::whereIn('table_id', $tableIds)
            ->whereIn('status', [ReservationStatus::Seated, ReservationStatus::WalkInSeated])
            ->whereDate('reservation_date', today())
            ->pluck('table_id')
            ->unique();

        foreach ($tablesWithReservations as $tableId) {
            $order = Order::where('table_id', $tableId)
                ->where('status', \App\Enums\OrderStatus::Open)
                ->first();

            $referenceTime = null;
            if ($order) {
                if ($order->orderItems()->count() === 0) {
                    $referenceTime = $order->created_at;
                }
            } else {
                $res = Reservation::where('table_id', $tableId)
                    ->whereIn('status', [ReservationStatus::Seated, ReservationStatus::WalkInSeated])
                    ->whereDate('reservation_date', today())
                    ->first();
                $referenceTime = $res?->updated_at ?? $res?->reservation_time;
            }

            if ($referenceTime && Carbon::parse($referenceTime)->lt($cutoff)) {
                $table = Table::find($tableId);
                $tableNum = $table?->table_number ?? $tableId;
                $alerts[] = [
                    'type' => 'unserved_table',
                    'message' => __('Goście przy nr :table siedzą od 10 min bez zamówienia', ['table' => $tableNum]),
                    'link' => route('waiter.index'),
                    'severity' => 'warning',
                    'data' => ['table_id' => $tableId],
                ];
            }
        }

        return $alerts;
    }

    /** @return array<int, array{type: string, message: string, link: string, severity: string, data?: array}> */
    private function getHostAlerts(): array
    {
        $alerts = [];
        $today = today()->toDateString();
        $now = now();

        // Upcoming reservation (10-20 min)
        $windowStart = $now->copy()->addMinutes(self::UPCOMING_RESERVATION_MIN);
        $windowEnd = $now->copy()->addMinutes(self::UPCOMING_RESERVATION_MAX);

        $upcoming = Reservation::with('table')
            ->whereDate('reservation_date', $today)
            ->where('status', ReservationStatus::Confirmed)
            ->get()
            ->filter(function ($r) use ($windowStart, $windowEnd) {
                $timePart = $r->reservation_time instanceof \DateTimeInterface
                    ? $r->reservation_time->format('H:i:s')
                    : (string) $r->reservation_time;
                $resTime = Carbon::parse($r->reservation_date->format('Y-m-d') . ' ' . $timePart);
                return $resTime->between($windowStart, $windowEnd);
            });

        foreach ($upcoming as $r) {
            $tableNum = $r->table?->table_number ?? '?';
            $alerts[] = [
                'type' => 'upcoming_reservation',
                'message' => __('Za 15 minut zaczyna się rezerwacja na :count osób (Stolik :table)', [
                    'count' => $r->party_size,
                    'table' => $tableNum,
                ]),
                'link' => route('reservations.index'),
                'severity' => 'info',
                'data' => ['reservation_id' => $r->id],
            ];
        }

        // No-show: Confirmed, reservation_time > 20 min ago
        $noShowCutoff = $now->copy()->subMinutes(self::NO_SHOW_MINUTES);
        $noShows = Reservation::with('table')
            ->whereDate('reservation_date', $today)
            ->where('status', ReservationStatus::Confirmed)
            ->get()
            ->filter(function ($r) use ($noShowCutoff) {
                $timePart = $r->reservation_time instanceof \DateTimeInterface
                    ? $r->reservation_time->format('H:i:s')
                    : (string) $r->reservation_time;
                $resTime = Carbon::parse($r->reservation_date->format('Y-m-d') . ' ' . $timePart);
                return $resTime->lt($noShowCutoff);
            });

        foreach ($noShows as $r) {
            $timeStr = $r->reservation_time instanceof \DateTimeInterface
                ? $r->reservation_time->format('H:i')
                : substr((string) $r->reservation_time, 0, 5);
            $alerts[] = [
                'type' => 'no_show_alert',
                'message' => __('Rezerwacja \':name\' (:time) spóźnia się 20 minut. Anulować?', [
                    'name' => $r->customer_name,
                    'time' => $timeStr,
                ]),
                'link' => route('reservations.edit', $r),
                'severity' => 'warning',
                'data' => ['reservation_id' => $r->id],
            ];
        }

        return $alerts;
    }

    /** @return array<int, array{type: string, message: string, link: string, severity: string, data?: array}> */
    private function getManagerAlerts(): array
    {
        $alerts = [];

        // Cancellation requests
        $requests = OrderItemCancellationRequest::with(['orderItem.menuItem.dish', 'orderItem.order.table', 'requestedByUser'])
            ->where('status', 'pending')
            ->get();

        foreach ($requests as $req) {
            $waiterName = $req->requestedByUser?->name ?? __('Unknown');
            $alerts[] = [
                'type' => 'cancellation_request',
                'message' => __('Kelner :name prosi o zatwierdzenie anulowania pozycji (:amount zł)', [
                    'name' => $waiterName,
                    'amount' => number_format($req->amount, 0),
                ]),
                'link' => route('manager.cancellation-requests.index'),
                'severity' => 'warning',
                'data' => ['request_id' => $req->id],
            ];
        }

        // Staff late clock-in
        $activeShifts = Shift::with(['user', 'clockIns'])
            ->activeNow()
            ->get();

        $lateCutoff = now()->subMinutes(self::STAFF_LATE_CLOCKIN_MINUTES);

        foreach ($activeShifts as $shift) {
            $shiftStart = Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time);
            if ($shiftStart->gt($lateCutoff)) {
                continue; // Shift started less than 15 min ago
            }

            $hasClockIn = ShiftClockIn::where('shift_id', $shift->id)
                ->where('user_id', $shift->user_id)
                ->exists();

            if (!$hasClockIn) {
                $userName = $shift->user?->name ?? __('Unknown');
                $minutesLate = (int) $shiftStart->diffInMinutes(now());
                $alerts[] = [
                    'type' => 'staff_late_clockin',
                    'message' => __(':name nie zalogował się na zmianę (spóźnienie :min min)', [
                        'name' => $userName,
                        'min' => $minutesLate,
                    ]),
                    'link' => route('shifts.index'),
                    'severity' => 'warning',
                    'data' => ['shift_id' => $shift->id, 'user_id' => $shift->user_id],
                ];
            }
        }

        return $alerts;
    }
}
