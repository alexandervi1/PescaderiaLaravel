<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {

        $usuario = Usuario::where('nombre_usuario', $request->nombre_usuario)->first();

        if ($usuario && Hash::check($request->password, $usuario->password)) {
            Auth::login($usuario);
            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        return back()->with('error', 'Usuario o contraseña incorrectos.')->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {

        Usuario::create([
            'nombre_usuario'  => $request->nombre_usuario,
            'nombre_completo' => $request->nombre_completo,
            'identificacion'  => $request->identificacion,
            'password'        => Hash::make($request->password),
            'rol_id'          => 2, // Cliente
            'direccion'       => $request->direccion,
            'correo'          => $request->correo,
            'telefono'        => $request->telefono,
        ]);

        return redirect()->route('login')->with('success', '¡Registro exitoso! Ahora puedes iniciar sesión.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function redirectByRole()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        if ($user->esAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    }
}
