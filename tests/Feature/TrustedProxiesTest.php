<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/probar-ip', fn () => request()->ip());
    }

    public function test_sin_proxies_de_confianza_ignora_x_forwarded_for(): void
    {
        config(['app.trusted_proxies' => null]);

        $this->get('/probar-ip', ['X-Forwarded-For' => '1.2.3.4'])
            ->assertSee('127.0.0.1');
    }

    public function test_con_proxy_de_confianza_usa_x_forwarded_for(): void
    {
        config(['app.trusted_proxies' => '*']);

        $this->get('/probar-ip', ['X-Forwarded-For' => '1.2.3.4'])
            ->assertSee('1.2.3.4');
    }
}
