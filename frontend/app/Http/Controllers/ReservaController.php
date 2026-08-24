<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservaController extends Controller
{
    private const STAFF = ['ADMIN', 'BIBLIOTECARIO'];

    public function __construct(protected ApiClient $api) {}

    public function index(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(100, max(5, (int) $request->query('size', 10)));

        $params = ['page' => $page, 'size' => $size];

        foreach (['q', 'estado'] as $filtro) {
            if ($request->filled($filtro)) {
                $params[$filtro] = $request->query($filtro);
            }
        }

        $data = $this->api->getReservas($params);

        return view('reservas.index', [
            'reservas'    => $data['content'] ?? [],
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
        $data = $this->api->getReservas(['size' => 500]);
        $reservas = $data['content'] ?? [];
        $reserva = collect($reservas)->firstWhere('id', $id) ?? [];

        return view('reservas.show', ['reserva' => $reserva]);
    }

    public function create(): View
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403, 'No tienes permisos para registrar reservas.');

        return view('reservas.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        $request->validate([
            'usuarioId' => ['required', 'integer'],
            'libroId'   => ['required', 'integer'],
        ]);

        try {
            $this->api->crearReserva(
                (int) $request->input('usuarioId'),
                (int) $request->input('libroId')
            );

            return redirect()->route('reservas.index')
                ->with('success', 'Reserva registrada exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelar(int $id): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        try {
            $this->api->cancelarReserva($id);
            return redirect()->route('reservas.index')
                ->with('success', 'Reserva cancelada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function formData(): array
    {
        $usuarios = $this->api->getUsuarios(['size' => 200]);
        $libros = $this->api->getLibros(['size' => 500]);

        return [
            'usuarios' => array_values(array_filter($usuarios['content'] ?? [], fn ($u) => ($u['rol'] ?? '') !== 'ADMIN')),
            'libros'   => $libros['content'] ?? [],
        ];
    }
}