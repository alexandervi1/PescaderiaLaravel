<?php

namespace App\Providers;

use App\Http\View\Composers\AdminMenuComposer;
use App\Models\Configuracion;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->aplicarZonaHoraria();

        View::composer('layouts.admin', AdminMenuComposer::class);

        // Se calcula en cada render para reflejar cambios de configuración sin reiniciar.
        View::composer('*', function ($view) {
            $view->with('negocio', $this->datosDelNegocio());
        });
    }

    /**
     * Aplica la zona horaria configurada. Si la tabla aún no existe
     * (instalación o migraciones) se conserva la de config/app.php.
     */
    private function aplicarZonaHoraria(): void
    {
        try {
            $zona = Configuracion::obtenerZonaHoraria();

            if (in_array($zona, timezone_identifiers_list(), true)) {
                date_default_timezone_set($zona);
                config(['app.timezone' => $zona]);
            }
        } catch (\Throwable $e) {
            // Base de datos no disponible todavía.
        }
    }

    /**
     * @return array{nombre: string, ruc: string, direccion: string, telefono: string, leyenda: string, logo: string}
     */
    private function datosDelNegocio(): array
    {
        try {
            return [
                'nombre' => (string) Configuracion::obtenerValor('negocio_nombre', 'Pescadería Don Walter'),
                'ruc' => (string) Configuracion::obtenerValor('negocio_ruc', ''),
                'direccion' => (string) Configuracion::obtenerValor('negocio_direccion', 'Manta, Ecuador'),
                'telefono' => (string) Configuracion::obtenerValor('negocio_telefono', ''),
                'leyenda' => (string) Configuracion::obtenerValor('negocio_leyenda', ''),
                'logo' => Configuracion::obtenerLogo(),
            ];
        } catch (\Throwable $e) {
            return [
                'nombre' => 'Pescadería Don Walter',
                'ruc' => '',
                'direccion' => 'Manta, Ecuador',
                'telefono' => '',
                'leyenda' => '',
                'logo' => 'img/Pescaderia Don Walter logo.png',
            ];
        }
    }
}
