<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultaController extends Controller
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
            ? $this->api->getMultas(['page' => $page, 'size' => $size])
            : $this->api->getMultasMias(['page' => $page, 'size' => $size]);
        $multas = $data['content'] ?? [];

        $filtro = $request->query('estado', '');
        if ($filtro === 'pagadas') {
            $multas = array_values(array_filter($multas, fn ($m) => ($m['pagada'] ?? false) === true));
        } elseif ($filtro === 'pendientes') {
            $multas = array_values(array_filter($multas, fn ($m) => ($m['pagada'] ?? false) === false));
        }

        return view('multas.index', [
            'multas'      => $multas,
            'filtro'      => $filtro,
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }

    public function pagar(int $id): RedirectResponse
    {
        abort_unless(in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']), 403);

        try {
            $this->api->pagarMulta($id);
            return redirect()->route('multas.index')
                ->with('success', 'Multa marcada como pagada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}