<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Usuario::with('rol')->where('rol_id', 2)->get();
        return view('admin.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('admin.clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario'  => 'required|string|max:255|unique:usuario,nombre_usuario',
            'password'        => 'required|string|min:6',
            'direccion'       => 'required|string|max:100',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:10',
        ]);

        Usuario::create([
            'nombre_usuario'  => $request->nombre_usuario,
            'nombre_completo' => $request->nombre_completo,
            'password'        => Hash::make($request->password),
            'rol_id'          => 2, // Siempre cliente desde este panel
            'direccion'       => $request->direccion,
            'correo'          => $request->correo,
            'telefono'        => $request->telefono,
        ]);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Usuario $cliente)
    {
        return view('admin.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Usuario $cliente)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'nombre_usuario'  => 'required|string|max:255|unique:usuario,nombre_usuario,' . $cliente->usuario_id . ',usuario_id',
            'direccion'       => 'required|string|max:100',
            'correo'          => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:10',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $cliente->update($data);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Usuario $cliente)
    {
        $cliente->delete();
        return redirect()->route('admin.clientes.index')->with('success', 'Cliente eliminado.');
    }
}
