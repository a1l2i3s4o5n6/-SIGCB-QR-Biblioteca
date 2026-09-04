<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrestamoController extends Controller
{
    private const STAFF = ['ADMIN', 'BIBLIOTECARIO'];

    public function __construct(protected ApiClient $api) {}

    public function index(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 10)));

        $params = ['page' => $page, 'size' => $size];

        foreach (['q', 'estado', 'desde', 'hasta'] as $filtro) {
            if ($request->filled($filtro)) {
                $params[$filtro] = $request->query($filtro);
            }
        }

        $data = $this->api->getPrestamos($params);

        return view('prestamos.index', [
            'prestamos'   => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }

    public function devoluciones(Request $request): View
    {
        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 10)));

        $data = $this->api->getPrestamos(['estado' => 'DEVUELTO', 'page' => $page, 'size' => $size]);

        return view('devoluciones.index', [
            'prestamos'   => $data['content'] ?? [],
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
        $data = $this->api->getPrestamo($id);
        $prestamo = $data['data'] ?? $data;

        return view('prestamos.show', ['prestamo' => $prestamo]);
    }

    public function create(): View
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403, 'No tienes permisos para registrar préstamos.');

        return view('prestamos.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        $request->validate([
            'usuarioId'    => ['required', 'integer'],
            'inventarioId' => ['required', 'integer'],
        ]);

        try {
            $this->api->crearPrestamo(
                (int) $request->input('usuarioId'),
                (int) $request->input('inventarioId')
            );

            return redirect()->route('prestamos.index')
                ->with('success', 'Préstamo registrado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function devolver(int $id): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        try {
            $this->api->devolverPrestamo($id);
            return redirect()->route('prestamos.show', $id)
                ->with('success', 'Devolución registrada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function renovar(int $id): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        try {
            $this->api->renovarPrestamo($id);
            return redirect()->route('prestamos.show', $id)
                ->with('success', 'Préstamo renovado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function formData(): array
    {
        $usuarios = $this->api->getUsuarios(['size' => 200]);
        $inventario = $this->api->getInventarioDisponible(['size' => 500]);
        $pendientes = $this->api->getReservas(['estado' => 'PENDIENTE', 'size' => 500]);

        return [
            'usuarios'        => array_values(array_filter($usuarios['content'] ?? [], fn ($u) => ($u['rol'] ?? '') !== 'ADMIN')),
            'inventario'      => $inventario,
            'solicitudesReg' => $pendientes['content'] ?? [],
        ];
    }
}