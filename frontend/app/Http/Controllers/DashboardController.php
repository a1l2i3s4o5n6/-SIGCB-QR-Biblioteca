<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(): View
    {
        $stats = $this->api->getEstadisticas();
        $reservas = $this->api->getReservas(['page' => 0, 'size' => 3]);
        $prestamos = $this->api->getPrestamos(['page' => 0, 'size' => 4]);

        return view('dashboard', [
            'stats' => $stats,
            'reservas' => $reservas['content'] ?? [],
            'prestamos' => $prestamos['content'] ?? [],
        ]);
    }
}