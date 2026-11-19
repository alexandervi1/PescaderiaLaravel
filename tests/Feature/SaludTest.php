<?php

namespace Tests\Feature;

use Tests\TestCase;

class SaludTest extends TestCase
{
    public function test_endpoint_de_salud_responde_ok(): void
    {
        $this->getJson('/api/salud')
            ->assertOk()
            ->assertExactJson(['estado' => 'ok']);
    }
}
