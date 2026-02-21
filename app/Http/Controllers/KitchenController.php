<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use App\Http\Requests\UpdateKitchenItemStatusRequest;
use App\Services\KitchenService;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function __construct(
        private KitchenService $kitchenService
    ) {}

    public function index()
    {
        $this->authorize('kitchen.view');

        $items = $this->kitchenService->getQueueItems(auth()->user());
        return view('kitchen.index', compact('items'));
    }

    public function updateStatus(UpdateKitchenItemStatusRequest $request, OrderItem $orderItem)
    {
        $this->authorize('kitchen.update-item-status', $orderItem);

        $validated = $request->validated();
        $status = $validated['status'];
        $data = ['status' => $status];

        if (in_array($status, [OrderItemStatus::Ready->value, OrderItemStatus::Served->value]) && !$orderItem->ready_at) {
            $data['ready_at'] = now();
        }
        $orderItem->update($data);

        // Refresh the model to get latest data
        $orderItem->refresh();

        event(new \App\Events\OrderItemStatusUpdated($orderItem));

        return back()->with('success', 'Item status updated.');
    }
}
