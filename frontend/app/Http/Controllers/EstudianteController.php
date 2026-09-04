<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstudianteController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
        $this->middleware(function ($request, $next) {
            abort_unless(in_array(session('rol'), ['ESTUDIANTE', 'LECTOR'], true), 403, 'No tienes permisos para acceder a esta sección.');
            return $next($request);
        });
    }

    public function misPrestamos(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 10)));

        $params = ['page' => $page, 'size' => $size];
        if ($request->filled('estado')) {
            $params['estado'] = $request->query('estado');
        }

        $data = $this->api->misPrestamos($params);

        return view('estudiante.mis-prestamos', [
            'prestamos'   => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }

    public function solicitarRenovacion(int $id): RedirectResponse
    {
        try {
            $this->api->solicitarRenovacion($id);
            return redirect()->route('estudiante.mis-prestamos')
                ->with('success', 'Solicitud de renovación enviada. Espera la aprobación del bibliotecario.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function misReservas(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 10)));

        $params = ['page' => $page, 'size' => $size];

        $data = $this->api->misReservas($params);
        $libros = $this->api->getLibros(['size' => 500]);

        return view('estudiante.mis-reservas', [
            'reservas'    => $data['content'] ?? [],
            'libros'      => $libros['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }

    public function reservarLibro(Request $request): RedirectResponse
    {
        $request->validate([
            'libroId' => ['required', 'integer'],
        ]);

        try {
            $this->api->autoReserva((int) $request->input('libroId'));
            return redirect()->route('estudiante.mis-reservas')
                ->with('success', 'Reserva registrada. Quedaste en la lista de espera.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelarReserva(int $id): RedirectResponse
    {
        try {
            $this->api->cancelarReserva($id);
            return redirect()->route('estudiante.mis-reservas')
                ->with('success', 'Reserva cancelada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
