<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
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
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('store_settings')) {
                    $settings = StoreSetting::allSettings();
                    if (!isset($view->getData()['store'])) {
                        $view->with('store', $settings);
                    }
                    if (!isset($view->getData()['storeSettings'])) {
                        $view->with('storeSettings', $settings);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore during early boot/migration/testing
            }
        });
    }
}
