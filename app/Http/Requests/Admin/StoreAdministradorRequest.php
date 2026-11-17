<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdministradorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario',
            'correo' => 'required|email|max:100|unique:usuario,correo',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'required|string|max:100',
            'password' => 'required|string|min:8',
        ];
    }
}
