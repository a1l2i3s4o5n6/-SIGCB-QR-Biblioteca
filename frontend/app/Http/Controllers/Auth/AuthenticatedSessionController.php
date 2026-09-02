<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    /**
     * Muestra el formulario de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa el login contra el backend Spring Boot.
     * Si es exitoso, guarda el JWT en sesión y redirige al dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $data = $this->api->login(
                $request->input('email'),
                $request->input('password')
            );

            // Guardar token JWT y datos del usuario en sesión
            $request->session()->regenerate();
            $request->session()->put('api_token', $data['token'] ?? null);
            $request->session()->put('user', [
                'id'     => $data['id']     ?? null,
                'nombre' => $data['nombre'] ?? null,
                'email'  => $data['email']  ?? null,
                'foto'   => $data['foto']   ?? null,
            ]);
            $request->session()->put('rol', $data['rol'] ?? 'LECTOR');

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Cierra sesión: invalida el JWT en el backend y limpia la sesión local.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->api->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
