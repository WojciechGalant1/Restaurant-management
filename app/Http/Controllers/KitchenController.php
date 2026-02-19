<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        $this->authorize('kitchen.view');

        $items = OrderItem::with(['order.table', 'menuItem.dish'])
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $this->authorize('kitchen.update-item-status', $orderItem);

        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,served',
        ]);

        $data = ['status' => $validated['status']];
        if (in_array($validated['status'], ['ready', 'served']) && !$orderItem->ready_at) {
            $data['ready_at'] = now();
        }
        $orderItem->update($data);

        // Refresh the model to get latest data
        $orderItem->refresh();

        event(new \App\Events\OrderItemStatusUpdated($orderItem));

        return back()->with('success', 'Item status updated.');
    }
}
