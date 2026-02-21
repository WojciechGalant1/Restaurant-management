<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Collection;

class KitchenService
{
    /**
     * Queue items for the kitchen view: pending, preparing, ready,
     * filtered by the user's visible dish categories.
     */
    public function getQueueItems(User $user): Collection
    {
        $categories = $user->role->visibleCategories();

        return OrderItem::with(['order.table', 'menuItem.dish'])
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->whereHas('menuItem.dish', function ($query) use ($categories) {
                $query->whereIn('category', $categories);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
