<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\ShiftClockIn;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboard
    ) {}

    public function revenue(Request $request): JsonResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');
        if (!$from || !$to) {
            return response()->json(['data' => []], 400);
        }
        $data = $this->dashboard->revenueBetween($from, $to);
        return response()->json(['data' => $data]);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $role = $user->role ?? UserRole::Waiter;
        $sections = $role->dashboardSections();
        $data = ['sections' => $sections];

        if ($sections['kpis']) {
            $data['kpis'] = $this->dashboard->getKpis();
            $data['performanceIndicator'] = $this->dashboard->performanceIndicator();
            $data['revenueThisMonth'] = $this->dashboard->revenueThisMonth();
        }
        if ($sections['charts']) {
            $data['revenueByDay7'] = $this->dashboard->revenueByDay(7);
            $data['revenueByDay30'] = $this->dashboard->revenueByDay(30);
            $data['paymentBreakdown'] = $this->dashboard->paymentMethodBreakdown();
        }
        if ($sections['kitchen']) {
            $data['kitchenPerformance'] = $this->dashboard->getKitchenPerformance();
            $data['kpis'] = $data['kpis'] ?? $this->dashboard->getKpis();
        }
        if ($sections['staff']) {
            $data['staffOnShift'] = $this->dashboard->staffOnShiftToday();
            $data['nextShiftChange'] = $this->dashboard->nextShiftChange();
        }
        if ($sections['alerts']) {
            $data['alerts'] = $this->dashboard->getAlerts();
        }
        if ($sections['top_performers']) {
            $data['topDishes'] = $this->dashboard->topDishesToday(5);
            $data['bestWaiter'] = $this->dashboard->bestWaiterByRevenueToday();
            $data['mostUsedPaymentMethod'] = $this->dashboard->mostUsedPaymentMethodToday();
        }

        // Active shift needing clock-in (for non-managers)
        $data['shiftNeedingClockIn'] = null;
        if ($user->role !== UserRole::Manager) {
            $activeShift = Shift::where('user_id', $user->id)->activeNow()->first();
            if ($activeShift && !ShiftClockIn::where('shift_id', $activeShift->id)->where('user_id', $user->id)->exists()) {
                $data['shiftNeedingClockIn'] = $activeShift;
            }
        }

        return response()
            ->view('dashboard.index', $data)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
