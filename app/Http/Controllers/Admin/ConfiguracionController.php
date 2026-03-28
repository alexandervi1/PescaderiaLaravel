<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $configuraciones = [
            'iva_actual' => Configuracion::obtenerValor('iva_actual', 15),
            'negocio_nombre' => Configuracion::obtenerValor('negocio_nombre', ''),
            'negocio_ruc' => Configuracion::obtenerValor('negocio_ruc', ''),
            'negocio_direccion' => Configuracion::obtenerValor('negocio_direccion', ''),
            'negocio_telefono' => Configuracion::obtenerValor('negocio_telefono', ''),
            'negocio_leyenda' => Configuracion::obtenerValor('negocio_leyenda', ''),
        ];

        return view('admin.configuracion.edit', compact('configuraciones'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'iva_actual' => 'required|numeric|min:0|max:100',
            'negocio_nombre' => 'required|string|max:255',
            'negocio_ruc' => 'required|string|max:13',
            'negocio_direccion' => 'required|string',
            'negocio_telefono' => 'required|string|max:20',
            'negocio_leyenda' => 'nullable|string',
        ]);

        $campos = ['iva_actual', 'negocio_nombre', 'negocio_ruc', 'negocio_direccion', 'negocio_telefono', 'negocio_leyenda'];

        foreach ($campos as $campo) {
            Configuracion::updateOrCreate(
                ['clave' => $campo],
                ['valor' => $request->$campo]
            );
        }

        return redirect()->route('admin.configuracion.edit')
            ->with('success', 'Parámetros del negocio actualizados con éxito.');
    }

    public function updateSecurity(Request $request)
    {
        /** @var \App\Models\Usuario $user */
        $user = auth()->user();

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario,' . $user->usuario_id . ',usuario_id',
            'password' => 'nullable|string|min:8'
        ], [
            'nombre_usuario.unique' => 'El nombre de usuario ya está ocupado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'nombre_completo.required' => 'El nombre completo es obligatorio.'
        ]);

        $user->nombre_completo = $request->nombre_completo;
        $user->nombre_usuario = $request->nombre_usuario;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        // Force updated_at to refresh even if only username changes or if password rotates
        $user->touch();
        $user->save();

        if ($request->filled('password') || $request->nombre_usuario !== $user->getOriginal('nombre_usuario')) {
            // Log out the user to force them to re-authenticate with new credentials
            auth()->logout();
            return redirect()->route('login')->with('success', 'Credenciales actualizadas correctamente. Por favor inicie sesión nuevamente.');
        }

        return redirect()->route('admin.configuracion.edit')
            ->with('success', 'Configuración de seguridad actualizada con éxito.');
    }
}
