<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Table;
use App\Models\MenuItem;
use App\Enums\TableStatus;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

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

        $filter = $request->query('filter');
        $status = $request->query('status');

        $query = Order::with(['table', 'waiter', 'orderItems.menuItem.dish'])->latest();

        if ($filter === 'today') {
            $query->whereDate('ordered_at', today());
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filter' => $status ?: ($filter ?: 'all'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Order::class);
        $preselectedTableId = $request->query('table_id') ? (int) $request->query('table_id') : null;
        $tables = Table::where('status', TableStatus::Occupied->value)
            ->when($preselectedTableId, fn ($q) => $q->orWhere('id', $preselectedTableId))
            ->orderBy('table_number')
            ->get();
        $menuItems = MenuItem::with('dish')->where('is_available', true)->get();
        return view('orders.create', compact('tables', 'menuItems', 'preselectedTableId'));
    }

    public function store(StoreOrderRequest $request)
    {
        $this->authorize('create', Order::class);

        $validated = $request->validated();
        $table = Table::findOrFail($validated['table_id']);

        if ($table->status !== TableStatus::Occupied) {
            return back()->with('error', __('Orders can only be created for occupied tables.'));
        }

        try {
            $order = $this->orderService->createOrder($validated, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['table', 'waiter', 'orderItems.menuItem.dish', 'orderItems.cancellationRequest', 'bills.payments', 'bills.invoice']);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);

        if ($order->openBill()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', __('Order is locked. Cancel the open bill first to edit.'));
        }

        $order->load(['orderItems.menuItem.dish', 'orderItems.cancellationRequest']);
        $tables = Table::where('status', 'available')->orWhere('id', $order->table_id)->orderBy('table_number')->get();
        $menuItems = MenuItem::with('dish')->where('is_available', true)->get();
        return view('orders.edit', compact('order', 'tables', 'menuItems'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        if ($order->openBill()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', __('Order is locked. Cancel the open bill first to edit.'));
        }

        $result = $this->orderService->updateOrder($order, $request->validated());
        $order = $result['order'];
        $requestsCreated = $result['cancellation_requests_created'] ?? 0;

        $message = __('Order updated successfully.');
        if ($requestsCreated > 0) {
            $message = __('Order updated. :count item(s) require manager approval for cancellation.', ['count' => $requestsCreated]);
        }

        return redirect()->route('orders.show', $order)->with('success', $message);
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        $this->orderService->deleteOrder($order);

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }
}
