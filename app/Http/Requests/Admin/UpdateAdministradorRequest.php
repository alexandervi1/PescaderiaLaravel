<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdministradorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        /** @var \App\Models\Usuario|null $administrador */
        $administrador = $this->route('administrador');
        $id = $administrador?->usuario_id;

        $rules = [
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario,' . $id . ',usuario_id',
            'correo' => 'required|email|max:100|unique:usuario,correo,' . $id . ',usuario_id',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'required|string|max:100',
            'password' => 'nullable|string|min:8',
        ];

        // Cambiar la propia contraseña exige confirmar la actual, igual que en Configuración.
        if ($administrador && $administrador->usuario_id === auth()->id() && $this->filled('password')) {
            $rules['current_password'] = 'required|current_password';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
        ];
    }
}
