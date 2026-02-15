<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboard
    ) {}

    public function revenue(Request $request): JsonResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');
        if (!$from || !$to) {
            return response()->json(['data' => []], 400);
        }
        $data = $this->dashboard->revenueBetween($from, $to);
        return response()->json(['data' => $data]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role ?? 'waiter';

        $sections = $this->sectionsForRole($role);
        $data = ['sections' => $sections];

        if ($sections['kpis']) {
            $data['kpis'] = $this->dashboard->getKpis();
            $data['performanceIndicator'] = $this->dashboard->performanceIndicator();
            $data['revenueThisMonth'] = $this->dashboard->revenueThisMonth();
        }
        if ($sections['charts']) {
            $data['revenueByDay1'] = $this->dashboard->revenueByDay(0); // today only
            $data['revenueByDay7'] = $this->dashboard->revenueByDay(7);
            $data['revenueByDay30'] = $this->dashboard->revenueByDay(30);
            $data['paymentBreakdown'] = $this->dashboard->paymentMethodBreakdown();
        }
        if ($sections['kitchen']) {
            $data['kitchenPerformance'] = $this->dashboard->getKitchenPerformance();
            $data['kpis'] = $data['kpis'] ?? $this->dashboard->getKpis();
        }
        if ($sections['staff']) {
            $data['staffOnShift'] = $this->dashboard->staffOnShiftToday();
            $data['nextShiftChange'] = $this->dashboard->nextShiftChange();
        }
        if ($sections['alerts']) {
            $data['alerts'] = $this->dashboard->getAlerts();
        }
        if ($sections['top_performers']) {
            $data['topDishes'] = $this->dashboard->topDishesToday(5);
            $data['bestWaiter'] = $this->dashboard->bestWaiterByRevenueToday();
            $data['mostUsedPaymentMethod'] = $this->dashboard->mostUsedPaymentMethodToday();
        }

        return view('dashboard.index', $data);
    }

    private function sectionsForRole(string $role): array
    {
        return match ($role) {
            'manager' => [
                'kpis' => true,
                'charts' => true,
                'kitchen' => true,
                'staff' => true,
                'alerts' => true,
                'top_performers' => true,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            'chef' => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => true,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            'waiter' => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => false,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            default => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => false,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
        };
    }
}
