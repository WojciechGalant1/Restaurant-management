<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    public function index()
    {
        if (!in_array(auth()->user()->role, ['manager', 'waiter'])) {
            abort(403);
        }

        // Get only items that are ready to be served
        $items = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', 'ready')
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('waiter.index', compact('items'));
    }

    public function markAsServed(Request $request, OrderItem $orderItem)
    {
        if (!in_array(auth()->user()->role, ['manager', 'waiter'])) {
            abort(403);
        }

        // Only allow marking items with 'ready' status as 'served'
        if ($orderItem->status !== 'ready') {
            return back()->with('error', 'Only ready items can be marked as served.');
        }

        $data = ['status' => 'served'];
        if (!$orderItem->ready_at) {
            $data['ready_at'] = now();
        }
        $orderItem->update($data);

        // Refresh the model to get latest data
        $orderItem->refresh();

        event(new \App\Events\OrderItemStatusUpdated($orderItem));

        return back()->with('success', 'Item marked as served.');
    }
}
