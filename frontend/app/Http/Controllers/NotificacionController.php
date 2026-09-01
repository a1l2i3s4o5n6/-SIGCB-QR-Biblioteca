<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    private function esStaff(): bool
    {
        return in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']);
    }

    public function index(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(100, max(5, (int) $request->query('size', 10)));

        $data = $this->esStaff()
            ? $this->api->getNotificacionesTodas(['page' => $page, 'size' => $size])
            : $this->api->getNotificaciones(['page' => $page, 'size' => $size]);

        $usuarios = [];
        if ($this->esStaff()) {
            $usuarios = $this->api->getUsuarios(['page' => 0, 'size' => 100])['content'] ?? [];
        }

        return view('notificaciones.index', [
            'notificaciones' => $data['content'] ?? [],
            'page'           => $data['page'] ?? $page,
            'size'           => $data['size'] ?? $size,
            'total'          => $data['totalElements'] ?? 0,
            'totalPages'     => $data['totalPages'] ?? 0,
            'first'          => $data['first'] ?? true,
            'last'           => $data['last'] ?? true,
            'usuarios'       => $usuarios,
            'noLeidas'       => $this->api->contarNotificacionesNoLeidas(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->esStaff(), 403);

        $request->validate([
            'usuarioId' => ['required', 'integer', 'min:1'],
            'titulo'    => ['required', 'string', 'max:200'],
            'mensaje'   => ['required', 'string'],
            'tipo'      => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $this->api->crearNotificacion([
                'usuarioId' => (int) $request->input('usuarioId'),
                'titulo'    => $request->input('titulo'),
                'mensaje'   => $request->input('mensaje'),
                'tipo'      => $request->input('tipo', 'INFO'),
            ]);
            return redirect()->route('notificaciones.index')->with('success', 'Notificación enviada.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function marcarLeida(int $id): RedirectResponse
    {
        try {
            $this->api->marcarNotificacionLeida($id);
            return redirect()->route('notificaciones.index');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function leerTodas(): RedirectResponse
    {
        try {
            $this->api->marcarTodasNotificacionesLeidas();
            return redirect()->route('notificaciones.index')->with('success', 'Todas las notificaciones marcadas como leídas.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}