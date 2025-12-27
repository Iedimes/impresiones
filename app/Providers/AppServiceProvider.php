<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Extend SQL Server Connection to use Legacy Grammar for SQL 2008
        \Illuminate\Database\Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            $conn = new \Illuminate\Database\SqlServerConnection($connection, $database, $prefix, $config);
            $conn->setQueryGrammar(new \App\Database\LegacySqlServerGrammar);
            return $conn;
        });

        view()->composer('*', function ($view) {
            //$user = User::find(Auth::id())->get();
            //var_dump($user.'abc');
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
