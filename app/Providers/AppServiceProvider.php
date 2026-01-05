<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        // Register web middleware group
        $router->middlewareGroup('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Register api middleware group
        $router->middlewareGroup('api', [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Register route middleware aliases
        $router->aliasMiddleware('auth', \App\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('auth.basic', \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class);
        $router->aliasMiddleware('bindings', \Illuminate\Routing\Middleware\SubstituteBindings::class);
        $router->aliasMiddleware('cache.headers', \Illuminate\Http\Middleware\SetCacheHeaders::class);
        $router->aliasMiddleware('can', \Illuminate\Auth\Middleware\Authorize::class);
        $router->aliasMiddleware('guest', \App\Http\Middleware\RedirectIfAuthenticated::class);
        $router->aliasMiddleware('signed', \Illuminate\Routing\Middleware\ValidateSignature::class);
        $router->aliasMiddleware('throttle', \Illuminate\Routing\Middleware\ThrottleRequests::class);
        $router->aliasMiddleware('verified', \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        Paginator::useBootstrap();

        // Extend SQL Server Connection to use Legacy Grammar for SQL 2008
        \Illuminate\Database\Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            $conn = new \Illuminate\Database\SqlServerConnection($connection, $database, $prefix, $config);
            $conn->setQueryGrammar(new \App\Database\LegacySqlServerGrammar);
            return $conn;
        });

        view()->composer('*', function ($view) {
            $view->with('user', Auth::user());
        });

        // Compatibility shim for deleted SimpleQrCode package
        if (!class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            class_alias(\App\Services\QrCodeAdapter::class, 'SimpleSoftwareIO\QrCode\Facades\QrCode');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
