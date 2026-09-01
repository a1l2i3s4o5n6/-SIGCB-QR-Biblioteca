<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SancionController extends Controller
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
        $activa = $request->query('activa');

        $params = ['page' => $page, 'size' => $size];
        if ($this->esStaff() && in_array($activa, ['0', '1'], true)) {
            $params['activa'] = (int) $activa;
        }

        $data = $this->esStaff()
            ? $this->api->getSanciones($params)
            : $this->api->getSancionesMias(['page' => $page, 'size' => $size]);

        $usuarios = [];
        if ($this->esStaff()) {
            $usuarios = $this->api->getUsuarios(['page' => 0, 'size' => 100])['content'] ?? [];
        }

        return view('sanciones.index', [
            'sanciones'   => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
            'usuarios'    => $usuarios,
            'activa'      => $activa,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->esStaff(), 403);

        $request->validate([
            'usuarioId'   => ['required', 'integer', 'min:1'],
            'tipo'        => ['required', 'string', 'max:50'],
            'motivo'      => ['nullable', 'string'],
            'fechaInicio' => ['required', 'date'],
            'fechaFin'    => ['nullable', 'date'],
        ]);

        try {
            $this->api->crearSancion([
                'usuarioId'   => (int) $request->input('usuarioId'),
                'tipo'        => $request->input('tipo'),
                'motivo'      => $request->input('motivo'),
                'fechaInicio' => $request->input('fechaInicio') . ':00',
                'fechaFin'    => $request->input('fechaFin') ? $request->input('fechaFin') . ':00' : null,
            ]);
            return redirect()->route('sanciones.index')->with('success', 'Sanción aplicada.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function levantar(int $id): RedirectResponse
    {
        abort_unless($this->esStaff(), 403);

        try {
            $this->api->levantarSancion($id);
            return redirect()->route('sanciones.index')->with('success', 'Sanción levantada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}