<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre_completo' => 'required|string|max:255',
            'identificacion'  => 'required|string|min:10|max:13',
            'nombre_usuario'  => 'required|string|max:255|unique:usuario,nombre_usuario',
            'password'        => 'required|string|min:6|confirmed',
            'direccion'       => 'required|string|max:100',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:10',
        ];
    }
}
