<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario',
            'correo' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'required|string|max:100',
            'password' => 'required|string|min:8',
        ]);

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

    public function update(Request $request, Usuario $administrador)
    {
        $this->soloAdmin($administrador);

        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:255|unique:usuario,nombre_usuario,' . $administrador->usuario_id . ',usuario_id',
            'correo' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'required|string|max:100',
            'password' => 'nullable|string|min:8',
        ]);

        $cambios = [
            'nombre_completo' => $data['nombre_completo'],
            'nombre_usuario' => $data['nombre_usuario'],
            'correo' => $data['correo'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'],
        ];

        if (!empty($data['password'])) {
            $cambios['password'] = Hash::make($data['password']);
            $cambios['password_changed_at'] = now();
        }

        $administrador->update($cambios);

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
}
