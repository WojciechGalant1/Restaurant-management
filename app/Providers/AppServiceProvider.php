<?php

namespace App\Providers;

use App\Policies\KitchenPolicy;
use App\Policies\WaiterPolicy;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kitchen abilities
        Gate::define('kitchen.view', [KitchenPolicy::class, 'view']);
        Gate::define('kitchen.update-item-status', [KitchenPolicy::class, 'updateItemStatus']);

        // Waiter abilities
        Gate::define('waiter.view', [WaiterPolicy::class, 'view']);
        Gate::define('waiter.serve-item', [WaiterPolicy::class, 'serveItem']);

        // Notifications for top bar
        View::composer('layouts.app', function ($view) {
            $notifications = [];
            if (auth()->check()) {
                $notifications = app(NotificationService::class)->getAlertsForUser(auth()->user());
            }
            $view->with('notifications', $notifications);
        });
    }
}
