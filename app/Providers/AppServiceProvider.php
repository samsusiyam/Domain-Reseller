<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Services\Currency\CurrencyConverter;
use App\Services\Registrars\RegistrarManager;
use App\Services\Payments\PaymentManager;
use App\Services\Audit\ActivityLogger;
use App\Models\Currency;
use App\Models\SystemSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyConverter::class, function ($app) {
            return new CurrencyConverter();
        });

        $this->app->singleton(RegistrarManager::class, function ($app) {
            return new RegistrarManager($app);
        });

        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager($app);
        });

        $this->app->singleton(ActivityLogger::class, function ($app) {
            return new ActivityLogger();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useTailwind();

        // Share active currencies and settings across views if DB is connected
        View::composer('*', function ($view) {
            static $sharedData = null;

            if ($sharedData === null) {
                try {
                    $currencies = Currency::where('is_active', true)->get();
                    $settings = SystemSetting::pluck('value', 'key')->toArray();
                    $sharedData = [
                        'availableCurrencies' => $currencies,
                        'systemSettings'      => $settings,
                        'currentCurrency'     => session('currency', config('currencies.default', 'USD')),
                    ];
                } catch (\Throwable $e) {
                    $sharedData = [
                        'availableCurrencies' => collect(),
                        'systemSettings'      => [],
                        'currentCurrency'     => 'USD',
                    ];
                }
            }

            $view->with($sharedData);
        });
    }
}
