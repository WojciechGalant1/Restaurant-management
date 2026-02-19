<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Table;
use App\Models\MenuItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $filter = $request->query('filter', 'all'); // all | today | pending

        $query = Order::with(['table', 'waiter', 'orderItems.menuItem.dish'])->latest();

        if ($filter === 'today') {
            $query->whereDate('ordered_at', today());
        } elseif ($filter === 'pending') {
            $query->where('status', 'pending');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filter' => $filter,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Order::class);
        $preselectedTableId = $request->query('table_id') ? (int) $request->query('table_id') : null;
        $tables = Table::where('status', 'available')
            ->orWhere('id', $preselectedTableId)
            ->orderBy('table_number')
            ->get();
        $menuItems = MenuItem::with('dish')->where('is_available', true)->get();
        return view('orders.create', compact('tables', 'menuItems', 'preselectedTableId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
        ]);

        $order = $this->orderService->createOrder($validated, auth()->user());

        return redirect()->route('orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['table', 'waiter', 'orderItems.menuItem.dish']);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        $order->load(['orderItems.menuItem.dish']);
        $tables = Table::where('status', 'available')->orWhere('id', $order->table_id)->orderBy('table_number')->get();
        $menuItems = MenuItem::with('dish')->where('is_available', true)->get();
        return view('orders.edit', compact('order', 'tables', 'menuItems'));
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        $validated = $request->validate([
            'table_id' => 'sometimes|exists:tables,id',
            'items' => 'sometimes|array|min:1',
            'items.*.id' => 'sometimes|nullable|exists:order_items,id',
            'items.*.menu_item_id' => 'required_with:items|exists:menu_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric',
            'items.*.notes' => 'nullable|string',
        ]);

        $order = $this->orderService->updateOrder($order, $validated);
        return redirect()->route('orders.show', $order)->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }
}
