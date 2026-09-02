<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(): View
    {
        $perfil = session('user', []);

        try {
            $perfil = $this->api->getMiPerfil() ?? $perfil;
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener el perfil desde el API: ' . $e->getMessage());
        }

        return view('perfil.index', ['perfil' => $perfil]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email'],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'foto_url'        => ['nullable', 'string', 'max:255', 'url'],
            'quitar_foto'     => ['sometimes', 'boolean'],
            'password_actual' => ['nullable', 'string', 'required_with:password_nueva'],
            'password_nueva'  => ['nullable', 'string', 'min:6', 'max:100', 'confirmed'],
        ]);

        $foto = null;
        $consumirFoto = false;

        if ($request->hasFile('foto')) {
            try {
                $path = $request->file('foto')->store('avatars', 'public');
                $foto = '/storage/' . $path;
                $consumirFoto = true;
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['error' => 'No se pudo subir la foto. ' . $e->getMessage()]);
            }
        } elseif ($request->filled('foto_url')) {
            $foto = trim($request->input('foto_url'));
            $consumirFoto = true;
        } elseif ($request->boolean('quitar_foto')) {
            $foto = null;
            $consumirFoto = true;
        }

        $data = [
            'nombre'   => trim($request->input('nombre')),
            'email'    => trim($request->input('email')),
            'telefono' => $request->filled('telefono') ? trim($request->input('telefono')) : null,
        ];

        if ($consumirFoto) {
            $data['foto'] = $foto;
        }

        if ($request->filled('password_nueva')) {
            $data['passwordActual'] = $request->input('password_actual');
            $data['passwordNueva']  = $request->input('password_nueva');
        }

        try {
            $perfil = $this->api->actualizarMiPerfil($data);

            $user = $request->session()->get('user', []);
            $user['nombre'] = $perfil['nombre'] ?? $user['nombre'] ?? null;
            $user['email']  = $perfil['email']  ?? $user['email']  ?? null;
            $user['foto']   = $perfil['foto']   ?? $user['foto']   ?? null;
            $request->session()->put('user', $user);

            return redirect()->route('perfil.index')
                ->with('success', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}