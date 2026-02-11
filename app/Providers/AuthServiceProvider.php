<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Dish::class => \App\Policies\DishPolicy::class,
        \App\Models\MenuItem::class => \App\Policies\MenuItemPolicy::class,
        \App\Models\Shift::class => \App\Policies\ShiftPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
        \App\Models\Order::class => \App\Policies\OrderPolicy::class,
        \App\Models\Table::class => \App\Policies\TablePolicy::class,
        \App\Models\Reservation::class => \App\Policies\ReservationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
