<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Límite general por IP (además del bloqueo por cuenta de RF001), uno por formulario
        $demasiados = fn () => back()->withInput()
            ->withErrors(['telefono' => 'Demasiadas solicitudes. Espera un minuto e inténtalo de nuevo.'])
            ->with('err', 'Demasiadas solicitudes. Espera un minuto e inténtalo de nuevo.');

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(20)->by('login|' . $r->ip())->response($demasiados));
        RateLimiter::for('recuperar', fn (Request $r) => Limit::perMinute(5)->by('recuperar|' . $r->ip())->response($demasiados));
    }
}
