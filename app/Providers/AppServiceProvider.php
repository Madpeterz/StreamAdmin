<?php

namespace App\Providers;

use App\Models\Staff;
use App\View\FileViewFinder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('view.finder', fn ($app) => new FileViewFinder($app['files'], $app['config']['view.paths']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // owner level staff manage system settings, staff accounts and other owner-only areas
        Gate::define('owner', fn (Staff $staff) => $staff->owner_level);

        // our version of one livewire-tables view, aliased instead of published to resources/views/vendor:
        // a vendor override folder makes every other table view look there first
        Blade::component('components.tables.collapsed-columns', 'livewire-tables::table.collapsed-columns');
    }
}
