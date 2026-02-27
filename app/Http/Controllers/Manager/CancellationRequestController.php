<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderItemStatus;
use App\Events\OrderItemCancelled;
use App\Http\Controllers\Controller;
use App\Models\OrderItemCancellationRequest;
use App\Enums\UserRole;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CancellationRequestController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        if ($request->user()->role !== UserRole::Manager) {
            abort(403);
        }
        $requests = OrderItemCancellationRequest::with([
            'orderItem.menuItem.dish',
            'orderItem.order.table',
            'requestedByUser',
        ])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('manager.cancellation-requests.index', compact('requests'));
    }

    public function approve(Request $request, OrderItemCancellationRequest $cancellationRequest)
    {
        if ($request->user()->role !== UserRole::Manager) {
            abort(403);
        }
        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', __('This request has already been processed.'));
        }

        $orderItem = $cancellationRequest->orderItem;
        $order = $orderItem->order;
        $orderItem->update([
            'status' => OrderItemStatus::Cancelled,
            'cancellation_reason' => $cancellationRequest->reason,
        ]);
        event(new OrderItemCancelled($orderItem));

        $order->update([
            'total_price' => $order->orderItems()
                ->whereNotIn('status', [OrderItemStatus::Cancelled, OrderItemStatus::Voided])
                ->get()
                ->sum(fn ($i) => $i->quantity * $i->unit_price),
        ]);

        $cancellationRequest->update([
            'status' => 'approved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $this->notificationService->clearCacheForUser(auth()->id());

        return back()->with('success', __('Cancellation approved.'));
    }

    public function reject(Request $request, OrderItemCancellationRequest $cancellationRequest)
    {
        if ($request->user()->role !== UserRole::Manager) {
            abort(403);
        }
        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', __('This request has already been processed.'));
        }

        $cancellationRequest->update([
            'status' => 'rejected',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $this->notificationService->clearCacheForUser(auth()->id());

        return back()->with('success', __('Cancellation rejected.'));
    }
}
