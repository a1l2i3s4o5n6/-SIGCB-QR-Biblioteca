<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    private function adminOnly(): void
    {
        abort_unless(session('rol') === 'ADMIN', 403, 'Solo el administrador puede ver la auditoría.');
    }

    public function index(Request $request): View
    {
        $this->adminOnly();

        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 15)));

        $desde = trim((string) $request->query('desde', ''));
        $hasta = trim((string) $request->query('hasta', ''));

        $params = ['page' => $page, 'size' => $size];
        if ($desde !== '') {
            $params['desde'] = $desde;
        }
        if ($hasta !== '') {
            $params['hasta'] = $hasta;
        }

        $data = $this->api->getAuditoria($params);

        return view('auditoria.index', [
            'registros'   => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
            'desde'       => $desde,
            'hasta'       => $hasta,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->adminOnly();

        $desde = trim((string) $request->query('desde', ''));
        $hasta = trim((string) $request->query('hasta', ''));

        $params = ['page' => 0, 'size' => 1000];
        if ($desde !== '') {
            $params['desde'] = $desde;
        }
        if ($hasta !== '') {
            $params['hasta'] = $hasta;
        }

        $data = $this->api->getAuditoria($params);
        $registros = $data['content'] ?? [];

        $pdf = Pdf::loadView('auditoria.pdf', [
            'registros' => $registros,
            'total'     => $data['totalElements'] ?? count($registros),
            'desde'     => $desde !== '' ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'Inicio del registro',
            'hasta'     => $hasta !== '' ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'Hoy',
            'generado'  => now()->format('d/m/Y H:i:s'),
            'usuario'   => session('user')['nombre'] ?? session('user.email', 'Administrador'),
        ])->setPaper('a4', 'landscape');

        $nombre = $desde !== '' && $hasta !== ''
            ? "reporte-auditoria_{$desde}_a_{$hasta}.pdf"
            : 'reporte-auditoria.pdf';

        return $pdf->download($nombre);
    }
}