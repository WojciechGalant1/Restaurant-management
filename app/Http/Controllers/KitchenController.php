<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KitchenController extends Controller
{
    public function index()
    {
        if (!in_array(auth()->user()->role, ['manager', 'chef'])) {
            abort(403);
        }

        $items = OrderItem::with(['order.table', 'menuItem.dish'])
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        if (!in_array(auth()->user()->role, ['manager', 'chef'])) {
            abort(403);
        }

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
