<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use App\Enums\UserRole;
use App\Enums\DishCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    public function index()
    {
        $this->authorize('kitchen.view');

        $categories = auth()->user()->role->visibleCategories();

        $items = OrderItem::with(['order.table', 'menuItem.dish'])
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->whereHas('menuItem.dish', function ($query) use ($categories) {
                $query->whereIn('category', $categories);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $this->authorize('kitchen.update-item-status', $orderItem);

        $categories = auth()->user()->role->visibleCategories();
        if (!in_array($orderItem->menuItem->dish->category, $categories)) {
            abort(403, 'You are not authorized to update this item category.');
        }

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
