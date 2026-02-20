<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    public function index(Request $request): Response
    {
        $user = $request->user();
        $role = $user->role ?? UserRole::Waiter;

        $sections = $this->sectionsForRole($role);
        $data = ['sections' => $sections];

        if ($sections['kpis']) {
            $data['kpis'] = $this->dashboard->getKpis();
            $data['performanceIndicator'] = $this->dashboard->performanceIndicator();
            $data['revenueThisMonth'] = $this->dashboard->revenueThisMonth();
        }
        if ($sections['charts']) {
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

        // Prevent browser caching of dashboard HTML (data changes frequently)
        return response()
            ->view('dashboard.index', $data)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function sectionsForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::Manager => [
                'kpis' => true,
                'charts' => true,
                'kitchen' => true,
                'staff' => true,
                'alerts' => true,
                'top_performers' => true,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            UserRole::Chef => [
                'kpis' => true,
                'charts' => false,
                'kitchen' => true,
                'staff' => false,
                'alerts' => false,
                'top_performers' => false,
                'live_feed' => true,
                'quick_actions' => true,
            ],
            UserRole::Waiter => [
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
