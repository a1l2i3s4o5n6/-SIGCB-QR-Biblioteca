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

        return view('dashboard', [
            'resumen' => $resumen,
            'rol'     => session('rol'),
        ]);
    }
}