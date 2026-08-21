<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(\Illuminate\Http\Request $request): View
    {
        abort_unless(session('rol') === 'ADMIN', 403, 'Solo el administrador puede ver la auditoría.');

        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 15)));

        $data = $this->api->getAuditoria(['page' => $page, 'size' => $size]);

        return view('auditoria.index', [
            'registros'   => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
        ]);
    }
}