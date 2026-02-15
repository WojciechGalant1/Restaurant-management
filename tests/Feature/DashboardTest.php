<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'waiter']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_manager_sees_full_dashboard_sections(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Revenue'), false);
        $response->assertSee(__('Kitchen Performance'), false);
        $response->assertSee(__('Staff & Shifts'), false);
        $response->assertSee(__('Alert Center'), false);
        $response->assertSee(__('Top Performers Today'), false);
        $response->assertSee(__('Live Activity'), false);
        $response->assertSee(__('Quick Actions'), false);
    }

    public function test_chef_sees_kitchen_and_feed_but_not_manager_only_sections(): void
    {
        $user = User::factory()->create(['role' => 'chef']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Kitchen Performance'), false);
        $response->assertSee(__('Live Activity'), false);
        $response->assertDontSee(__('Alert Center'), false);
        $response->assertDontSee(__('Staff & Shifts'), false);
        $response->assertDontSee(__('Top Performers Today'), false);
    }

    public function test_waiter_sees_kpis_and_quick_actions_but_not_revenue_charts_or_alerts(): void
    {
        $user = User::factory()->create(['role' => 'waiter']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Orders Today'), false);
        $response->assertSee(__('Quick Actions'), false);
        $response->assertSee(__('Live Activity'), false);
        $response->assertDontSee(__('Alert Center'), false);
        $response->assertDontSee(__('Kitchen Performance'), false);
        $response->assertDontSee(__('Top Performers Today'), false);
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
