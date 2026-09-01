<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrController extends Controller
{
    private const STAFF = ['ADMIN', 'BIBLIOTECARIO'];

    public function __construct(protected ApiClient $api) {}

    public function index(): View
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403, 'No tienes permisos para gestionar códigos QR.');

        $qrs = $this->api->getQrCodigos();

        return view('qr-codigos.index', [
            'qrs' => $qrs ?? [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        $request->validate([
            'libroId' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->api->crearQr((int) $request->input('libroId'));
            return redirect()->route('qr-codigos.index')
                ->with('success', 'Código QR creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggle(int $id, Request $request): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        $activo = $request->input('activo') === '1' || $request->input('activo') === 'true';

        try {
            $this->api->toggleQr($id, (bool) $activo);
            return redirect()->route('qr-codigos.index')
                ->with('success', 'Estado del código QR actualizado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function regenerar(int $id): RedirectResponse
    {
        abort_unless(in_array(session('rol'), self::STAFF), 403);

        try {
            $this->api->regenerarQr($id);
            return redirect()->route('qr-codigos.index')
                ->with('success', 'Código QR regenerado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function validar(Request $request): View
    {
        $codigo = trim((string) $request->query('codigo', ''));
        $resultado = null;
        $error = null;

        if ($codigo !== '') {
            try {
                $resultado = $this->api->validarQr($codigo);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return view('qr-codigos.validar', [
            'codigo'    => $codigo,
            'resultado' => $resultado,
            'error'     => $error,
        ]);
    }
}
