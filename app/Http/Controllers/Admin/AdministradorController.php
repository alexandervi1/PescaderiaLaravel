<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdministradorRequest;
use App\Http\Requests\Admin\UpdateAdministradorRequest;
use App\Models\ConfiguracionLog;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdministradorController extends Controller
{
    public function index()
    {
        $administradores = Usuario::where('rol_id', 1)->orderBy('nombre_usuario')->get();

        return view('admin.administradores.index', compact('administradores'));
    }

    public function create()
    {
        return view('admin.administradores.create');
    }

    public function store(StoreAdministradorRequest $request)
    {
        $data = $request->validated();

        Usuario::create([
            'nombre_usuario' => $data['nombre_usuario'],
            'nombre_completo' => $data['nombre_completo'],
            'password' => Hash::make($data['password']),
            'rol_id' => 1,
            'direccion' => $data['direccion'],
            'correo' => $data['correo'],
            'telefono' => $data['telefono'] ?? null,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('admin.administradores.index')
            ->with('success', 'Administrador creado correctamente.');
    }

    public function edit(Usuario $administrador)
    {
        $this->soloAdmin($administrador);

        return view('admin.administradores.create', compact('administrador'));
    }

    public function update(UpdateAdministradorRequest $request, Usuario $administrador)
    {
        $this->soloAdmin($administrador);

        $data = $request->validated();
        $esPropio = $administrador->usuario_id === auth()->id();
        $passwordCambiada = !empty($data['password']);
        $usuarioAnterior = $administrador->nombre_usuario;

        $cambios = [
            'nombre_completo' => $data['nombre_completo'],
            'nombre_usuario' => $data['nombre_usuario'],
            'correo' => $data['correo'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'],
        ];

        if ($passwordCambiada) {
            $cambios['password'] = Hash::make($data['password']);
            $cambios['password_changed_at'] = now();
        }

        $administrador->update($cambios);

        // Igual que en Configuración: cambiar credenciales propias deja rastro y cierra la sesión.
        if ($esPropio && ($passwordCambiada || $data['nombre_usuario'] !== $usuarioAnterior)) {
            if ($passwordCambiada) {
                $this->registrar('password', null, 'actualizada');
            }

            if ($data['nombre_usuario'] !== $usuarioAnterior) {
                $this->registrar('nombre_usuario', $usuarioAnterior, $data['nombre_usuario']);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'Credenciales actualizadas. Inicia sesión de nuevo.');
        }

        return redirect()->route('admin.administradores.index')
            ->with('success', 'Administrador actualizado correctamente.');
    }

    public function destroy(Usuario $administrador)
    {
        $this->soloAdmin($administrador);

        if ($administrador->usuario_id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        if (Usuario::where('rol_id', 1)->count() <= 1) {
            return back()->with('error', 'Debe existir al menos un administrador en el sistema.');
        }

        try {
            $administrador->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar: el administrador tiene pedidos u otros registros asociados.');
        }

        return redirect()->route('admin.administradores.index')
            ->with('success', 'Administrador eliminado.');
    }

    private function soloAdmin(Usuario $usuario): void
    {
        abort_unless($usuario->rol_id === 1, 404);
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
