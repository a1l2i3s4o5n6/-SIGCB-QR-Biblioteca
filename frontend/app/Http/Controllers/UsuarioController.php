<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    private function authorizeAdmin(): void
    {
        abort_unless(session('rol') === 'ADMIN', 403, 'Solo el administrador puede gestionar usuarios.');
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();
        $page = max(0, (int) $request->query('page', 0));
        $size = min(100, max(5, (int) $request->query('size', 10)));

        $data = $this->api->getUsuarios(['page' => $page, 'size' => $size]);

        return view('usuarios.index', [
            'usuarios'    => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }

    public function show(int $id): View
    {
        $this->authorizeAdmin();

        $data = $this->api->getUsuario($id);
        $usuario = $data['data'] ?? $data;

        return view('usuarios.show', ['usuario' => $usuario]);
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('usuarios.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'nombre'  => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email'],
            'rolId'   => ['required', 'integer'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $this->api->crearUsuario([
                'nombre'    => $request->input('nombre'),
                'email'     => $request->input('email'),
                'password'  => $request->input('password') ?: null,
                'telefono'  => $request->input('telefono'),
                'rolId'     => (int) $request->input('rolId'),
                'activo'    => $request->boolean('activo'),
            ]);

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(int $id): View
    {
        $this->authorizeAdmin();

        $data = $this->api->getUsuario($id);
        $usuario = $data['data'] ?? $data;

        return view('usuarios.edit', ['usuario' => $usuario] + $this->formData());
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'nombre'  => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email'],
            'rolId'   => ['required', 'integer'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $this->api->actualizarUsuario($id, [
                'nombre'    => $request->input('nombre'),
                'email'     => $request->input('email'),
                'password'  => $request->input('password') ?: null,
                'telefono'  => $request->input('telefono'),
                'rolId'     => (int) $request->input('rolId'),
                'activo'    => $request->boolean('activo'),
            ]);

            return redirect()->route('usuarios.show', $id)
                ->with('success', 'Usuario actualizado.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            $this->api->eliminarUsuario($id);
            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario desactivado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function formData(): array
    {
        return [
            'roles' => [
                1 => 'ADMIN',
                2 => 'BIBLIOTECARIO',
                3 => 'ESTUDIANTE',
            ],
        ];
    }
}