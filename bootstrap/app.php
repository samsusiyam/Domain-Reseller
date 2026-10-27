<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin panel routes
            Route::middleware(['web', 'auth', 'active', 'admin', 'track.activity'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Client panel routes
            Route::middleware(['web', 'auth', 'active', 'client', 'track.activity'])
                ->prefix('client')
                ->name('client.')
                ->group(base_path('routes/client.php'));

            // Reseller panel routes
            Route::middleware(['web', 'auth', 'active', 'reseller', 'track.activity'])
                ->prefix('reseller')
                ->name('reseller.')
                ->group(base_path('routes/reseller.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'             => \App\Http\Middleware\EnsureAdmin::class,
            'client'            => \App\Http\Middleware\EnsureClient::class,
            'reseller'          => \App\Http\Middleware\EnsureReseller::class,
            'active'            => \App\Http\Middleware\EnsureActive::class,
            'verified'          => \App\Http\Middleware\EnsureEmailVerified::class,
            'api.auth'          => \App\Http\Middleware\ApiAuthenticate::class,
            'api.ratelimit'     => \App\Http\Middleware\ApiRateLimit::class,
            'api.ip_whitelist'  => \App\Http\Middleware\ApiIpWhitelist::class,
            'track.activity'    => \App\Http\Middleware\TrackActivity::class,
            '2fa.verified'      => \App\Http\Middleware\TwoFactorVerify::class,
            'maintenance.check' => \App\Http\Middleware\MaintenanceMode::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetCurrency::class,
            \App\Http\Middleware\MaintenanceMode::class,
        ]);

        $middleware->encryptCookies(except: [
            'currency',
            'theme',
        ]);

        // Disable CSRF verification for payment webhooks & external callbacks
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/v1/webhook/*',
            'payment/*',
            'payment/bkash/callback',
            'payment/stripe/webhook',
            'payment/binance/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling for API and standard requests
        $exceptions->render(function (\App\Exceptions\DomainNotAvailableException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'DOMAIN_NOT_AVAILABLE',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (\App\Exceptions\InsufficientBalanceException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'INSUFFICIENT_FUNDS',
                    'message' => $e->getMessage(),
                ], 402);
            }
            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (\App\Exceptions\InvalidApiKeyException $e, $request) {
            return response()->json([
                'success' => false,
                'error'   => 'UNAUTHORIZED_API_KEY',
                'message' => $e->getMessage(),
            ], 401);
        });
    })->create();
