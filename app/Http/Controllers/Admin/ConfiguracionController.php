<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $data = $request->validate([
            'iva_actual' => 'required|numeric|min:0|max:100',
            'negocio_nombre' => 'required|string|max:255',
            'negocio_ruc' => 'required|string|max:13',
            'negocio_direccion' => 'required|string|max:255',
            'negocio_telefono' => 'required|string|max:20',
            'negocio_leyenda' => 'nullable|string|max:255',
        ]);

        foreach ($data as $clave => $valor) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor ?? '']
            );
        }

        return redirect()->route('admin.configuracion.edit')
            ->with('success', 'Parámetros del negocio actualizados con éxito.');
    }

    public function updateSecurity(Request $request)
    {
        /** @var \App\Models\Usuario $user */
        $user = auth()->user();

        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario,' . $user->usuario_id . ',usuario_id',
            'password' => 'nullable|string|min:8'
        ], [
            'nombre_usuario.unique' => 'El nombre de usuario ya está ocupado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'nombre_completo.required' => 'El nombre completo es obligatorio.'
        ]);

        // Capturar antes de guardar: getOriginal() ya devuelve el valor nuevo tras save().
        $nombreUsuarioAnterior = $user->nombre_usuario;

        $user->nombre_completo = $data['nombre_completo'];
        $user->nombre_usuario = $data['nombre_usuario'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->password_changed_at = now();
        }

        $user->save();

        if (!empty($data['password']) || $data['nombre_usuario'] !== $nombreUsuarioAnterior) {
            // Log out the user to force them to re-authenticate with new credentials
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Credenciales actualizadas correctamente. Por favor inicie sesión nuevamente.');
        }

        return redirect()->route('admin.configuracion.edit')
            ->with('success', 'Configuración de seguridad actualizada con éxito.');
    }
}
