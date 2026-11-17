<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAX_INTENTOS = 5;

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credencial = (string) $request->input('nombre_usuario');
        $clave = 'login|' . mb_strtolower($credencial) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            throw ValidationException::withMessages([
                'nombre_usuario' => 'Demasiados intentos fallidos. Espera un minuto antes de volver a intentarlo.',
            ]);
        }

        $usuario = Usuario::where('nombre_usuario', $credencial)
            ->orWhere('correo', $credencial)
            ->first();

        if ($usuario && Hash::check((string) $request->input('password'), $usuario->password)) {
            RateLimiter::clear($clave);
            Auth::login($usuario);
            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        RateLimiter::hit($clave, 60);

        return back()->with('error', 'Usuario o contraseña incorrectos.')->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        Usuario::create([
            'nombre_usuario'  => $data['nombre_usuario'],
            'nombre_completo' => $data['nombre_completo'],
            'identificacion'  => $data['identificacion'],
            'password'        => Hash::make($data['password']),
            'rol_id'          => 2, // Cliente
            'direccion'       => $data['direccion'],
            'correo'          => $data['correo'],
            'telefono'        => $data['telefono'] ?? null,
            'password_changed_at' => now(),
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
