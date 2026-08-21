<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(): View
    {
        abort_unless(in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']), 403, 'No tienes permisos para ver los reportes.');

        $prestamosDiarios = $this->api->getReportePrestamosDiarios();
        $librosMasSolicitados = $this->api->getReporteLibrosMasSolicitados();
        $multasCobradas = $this->api->getReporteMultasCobradas();

        return view('reportes.index', [
            'prestamosHoy' => $prestamosDiarios,
            'librosTop'    => $librosMasSolicitados['libros'] ?? [],
            'multasMes'    => $multasCobradas,
        ]);
    }
}