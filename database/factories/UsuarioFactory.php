<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition()
    {
        return [
            'nombre_usuario' => $this->faker->unique()->userName(),
            'nombre_completo' => $this->faker->name(),
            'identificacion' => $this->faker->numerify('##########'),
            'password' => Hash::make('password'),
            'rol_id' => 2,
            'direccion' => $this->faker->address(),
            'correo' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->numerify('09########'),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['rol_id' => 1]);
    }
}
