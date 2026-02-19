<?php

namespace App\Providers;

use App\Models\OrderItem;
use App\Policies\KitchenPolicy;
use App\Policies\WaiterPolicy;
use Illuminate\Support\Facades\Gate;
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
    }
}
