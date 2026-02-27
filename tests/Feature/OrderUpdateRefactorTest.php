<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderUpdateRefactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_items_omitted_from_payload_are_not_deleted(): void
    {
        $order = $this->seedOrderWithTwoItems();
        $orderService = app(OrderService::class);

        $firstItem = $order->orderItems()->orderBy('id')->first();

        $result = $orderService->updateOrder($order, [
            'items' => [[
                'id' => $firstItem->id,
                'menu_item_id' => $firstItem->menu_item_id,
                'quantity' => 3,
                'unit_price' => $firstItem->unit_price,
                'notes' => 'updated',
            ]],
        ]);

        $updatedOrder = $result['order']->fresh('orderItems');

        $this->assertCount(2, $updatedOrder->orderItems);
        $this->assertTrue($updatedOrder->orderItems->contains('id', $firstItem->id));
    }

    public function test_waiter_can_void_existing_item_via_cancel_action(): void
    {
        $order = $this->seedOrderWithTwoItems();
        $orderService = app(OrderService::class);

        $target = $order->orderItems()->orderByDesc('id')->first();

        $orderService->updateOrder($order, [
            'items' => [[
                'id' => $target->id,
                'menu_item_id' => $target->menu_item_id,
                'quantity' => $target->quantity,
                'unit_price' => $target->unit_price,
                'notes' => $target->notes,
                'cancel_action' => OrderItemStatus::Voided->value,
            ]],
        ]);

        $this->assertEquals(OrderItemStatus::Voided, $target->fresh()->status);
    }

    private function seedOrderWithTwoItems(): Order
    {
        $waiter = User::factory()->create(['role' => UserRole::Waiter]);
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'user_id' => $waiter->id,
            'status' => OrderStatus::Open,
        ]);

        $menuItemA = MenuItem::factory()->create(['is_available' => true]);
        $menuItemB = MenuItem::factory()->create(['is_available' => true]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItemA->id,
            'status' => OrderItemStatus::Pending,
            'quantity' => 1,
            'unit_price' => 10,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItemB->id,
            'status' => OrderItemStatus::Pending,
            'quantity' => 2,
            'unit_price' => 15,
        ]);

        return $order->fresh('orderItems');
    }
}
