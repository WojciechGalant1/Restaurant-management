<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    public function index()
    {
        $this->authorize('kitchen.view');

        $items = OrderItem::with(['order.table', 'menuItem.dish'])
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $this->authorize('kitchen.update-item-status', $orderItem);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderItemStatus::class)],
        ]);

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
