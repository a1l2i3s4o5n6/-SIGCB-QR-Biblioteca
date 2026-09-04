<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(Request $request): View
    {
        $params = [];
        if ($request->filled('desde')) {
            $params['desde'] = $request->query('desde');
        }
        if ($request->filled('hasta')) {
            $params['hasta'] = $request->query('hasta');
        }

        $resumen = $this->api->getDashboardResumen($params);
        $rol = session('rol');

        $misReservas = null;
        if ($rol === 'ESTUDIANTE') {
            $misReservas = $this->api->getReservasMias(['size' => 5]);
        }

        return view('dashboard', [
            'resumen'     => $resumen,
            'rol'         => $rol,
            'misReservas' => $misReservas,
        ]);
    }
}