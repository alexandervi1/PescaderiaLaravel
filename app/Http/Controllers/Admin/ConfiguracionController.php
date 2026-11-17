<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\ConfiguracionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConfiguracionController extends Controller
{
    private const ZONAS = [
        'America/Guayaquil',
        'America/Bogota',
        'America/Lima',
        'America/Mexico_City',
        'America/Santiago',
        'America/Argentina/Buenos_Aires',
        'America/New_York',
        'Europe/Madrid',
        'UTC',
    ];

    public function edit(Request $request)
    {
        $configuraciones = [
            'iva_actual' => Configuracion::obtenerValor('iva_actual', 15),
            'umbral_bajo_stock' => Configuracion::obtenerValor('umbral_bajo_stock', 50),
            'comprobante_serie' => Configuracion::obtenerValor('comprobante_serie', '001-001'),
            'password_rotacion_dias' => Configuracion::obtenerValor('password_rotacion_dias', 90),
            'zona_horaria' => Configuracion::obtenerZonaHoraria(),
            'negocio_nombre' => Configuracion::obtenerValor('negocio_nombre', ''),
            'negocio_ruc' => Configuracion::obtenerValor('negocio_ruc', ''),
            'negocio_direccion' => Configuracion::obtenerValor('negocio_direccion', ''),
            'negocio_telefono' => Configuracion::obtenerValor('negocio_telefono', ''),
            'negocio_leyenda' => Configuracion::obtenerValor('negocio_leyenda', ''),
            'negocio_logo' => Configuracion::obtenerLogo(),
        ];

        $zonas = self::ZONAS;
        $historial = ConfiguracionLog::with('usuario')->orderByDesc('id')->take(20)->get();
        $tab = $request->query('tab');

        if (!in_array($tab, ['negocio', 'seguridad', 'historial'], true)) {
            $tab = 'negocio';
        }

        return view('admin.configuracion.edit', compact('configuraciones', 'tab', 'zonas', 'historial'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'iva_actual' => 'required|numeric|min:0|max:100',
            'umbral_bajo_stock' => 'required|integer|min:0|max:100000',
            'comprobante_serie' => ['required', 'string', 'max:10', 'regex:/^[0-9]{3}-[0-9]{3}$/'],
            'password_rotacion_dias' => 'required|integer|min:1|max:3650',
            'zona_horaria' => ['required', 'string', Rule::in(self::ZONAS)],
            'negocio_nombre' => 'required|string|max:255',
            'negocio_ruc' => 'required|string|max:13',
            'negocio_direccion' => 'required|string|max:255',
            'negocio_telefono' => 'required|string|max:20',
            'negocio_leyenda' => 'nullable|string|max:255',
            'negocio_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'comprobante_serie.regex' => 'La serie debe tener el formato 000-000 (por ejemplo 001-001).',
        ]);

        $logo = $request->file('negocio_logo');
        unset($data['negocio_logo']);

        $logoAnterior = $logo ? Configuracion::obtenerLogo() : null;

        if ($logo) {
            $data['negocio_logo'] = $this->guardarLogo($logo);
        }

        DB::transaction(function () use ($data) {
            // Sin eventos por clave: se invalida la caché una sola vez al final.
            Configuracion::withoutEvents(function () use ($data) {
                foreach ($data as $clave => $valor) {
                    $anterior = Configuracion::obtenerValor($clave);
                    $nuevo = $valor ?? '';

                    Configuracion::updateOrCreate(['clave' => $clave], ['valor' => $nuevo]);

                    if ((string) $anterior !== (string) $nuevo) {
                        $this->registrar($clave, $anterior, $nuevo);
                    }
                }
            });
        });

        Configuracion::flush();

        // El archivo anterior solo se borra cuando la transacción ya confirmó.
        if ($logoAnterior !== null) {
            $this->borrarLogo($logoAnterior);
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
            'correo' => 'required|email|max:100|unique:usuario,correo,' . $user->usuario_id . ',usuario_id',
            'current_password' => 'required|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'nombre_usuario.unique' => 'El nombre de usuario ya está ocupado.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $nombreUsuarioAnterior = $user->nombre_usuario;
        $correoAnterior = $user->correo;
        $passwordCambiada = !empty($data['password']);

        $user->nombre_completo = $data['nombre_completo'];
        $user->nombre_usuario = $data['nombre_usuario'];
        $user->correo = $data['correo'];

        if ($passwordCambiada) {
            $user->password = Hash::make($data['password']);
            $user->password_changed_at = now();
        }

        DB::transaction(function () use ($user, $passwordCambiada, $nombreUsuarioAnterior, $correoAnterior) {
            $user->save();

            if ($passwordCambiada) {
                $this->registrar('password', null, 'actualizada');
            }

            if ($nombreUsuarioAnterior !== $user->nombre_usuario) {
                $this->registrar('nombre_usuario', $nombreUsuarioAnterior, $user->nombre_usuario);
            }

            if ($correoAnterior !== $user->correo) {
                $this->registrar('correo', $correoAnterior, $user->correo);
            }
        });

        if ($passwordCambiada || $data['nombre_usuario'] !== $nombreUsuarioAnterior) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Credenciales actualizadas correctamente. Por favor inicie sesión nuevamente.');
        }

        return redirect()->route('admin.configuracion.edit', ['tab' => 'seguridad'])
            ->with('success', 'Configuración de seguridad actualizada con éxito.');
    }

    private function guardarLogo($file): string
    {
        $filename = Str::uuid() . '.' . strtolower($file->extension());
        $file->move(public_path('img/negocio'), $filename);

        return 'img/negocio/' . $filename;
    }

    private function borrarLogo(?string $ruta): void
    {
        if (!$ruta || !Str::startsWith($ruta, 'img/negocio/')) {
            return;
        }

        $base = realpath(public_path('img/negocio'));
        $archivo = realpath(public_path($ruta));

        if ($base === false || $archivo === false || !Str::startsWith($archivo, $base)) {
            return;
        }

        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    private function registrar(string $clave, $anterior, $nuevo): void
    {
        ConfiguracionLog::create([
            'usuario_id' => auth()->id(),
            'clave' => $clave,
            'valor_anterior' => $anterior === null ? null : (string) $anterior,
            'valor_nuevo' => $nuevo === null ? null : (string) $nuevo,
        ]);
    }
}
