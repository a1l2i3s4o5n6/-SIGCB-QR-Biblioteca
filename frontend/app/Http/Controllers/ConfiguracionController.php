<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(): View
    {
        abort_unless(session('rol') === 'ADMIN', 403, 'Solo el administrador puede gestionar la configuración.');

        return view('configuracion.index', [
            'configuraciones' => $this->api->getConfiguracion(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(session('rol') === 'ADMIN', 403);

        $valores = $request->input('config', []);
        $actualizadas = 0;

        foreach ($valores as $id => $valor) {
            if (!is_numeric($id) || trim((string) $valor) === '') {
                continue;
            }

            try {
                $this->api->actualizarConfiguracion((int) $id, trim((string) $valor));
                $actualizadas++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return redirect()->route('configuracion.index')
            ->with('success', "Configuración actualizada ({$actualizadas} parámetro(s)).");
    }
}