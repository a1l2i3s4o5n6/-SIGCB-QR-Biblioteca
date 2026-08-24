<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Endpoints para búsqueda en vivo: devuelven únicamente el fragmento HTML
 * de la tabla (o JSON puntual) que el frontend inserta vía Alpine.
 * El JWT viaja en la sesión PHP, por eso el navegador pasa por aquí.
 */
class DatosController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function catalogo(Request $request): Response
    {
        $params = $this->pagina($request);
        foreach (['q', 'categoriaId', 'editorialId', 'anio', 'soloDisponibles'] as $f) {
            if ($request->filled($f)) {
                $params[$f] = $request->query($f);
            }
        }

        $data = $this->api->getLibros($params);

        return response()->view('catalogo._tabla', array_merge(
            $this->vars($data),
            ['libros' => $data['content'] ?? [], 'q' => $request->query('q', '')]
        ));
    }

    public function usuarios(Request $request): Response
    {
        $params = $this->pagina($request);
        foreach (['q', 'rol', 'activo'] as $f) {
            if ($request->filled($f)) {
                $params[$f] = $request->query($f);
            }
        }

        $data = $this->api->getUsuarios($params);

        return response()->view('usuarios._tabla', array_merge(
            $this->vars($data),
            ['usuarios' => $data['content'] ?? []]
        ));
    }

    public function prestamos(Request $request): Response
    {
        $params = $this->pagina($request);
        foreach (['q', 'estado', 'desde', 'hasta'] as $f) {
            if ($request->filled($f)) {
                $params[$f] = $request->query($f);
            }
        }

        $data = $this->api->getPrestamos($params);

        return response()->view('prestamos._tabla', array_merge(
            $this->vars($data),
            ['prestamos' => $data['content'] ?? []]
        ));
    }

    public function reservas(Request $request): Response
    {
        $params = $this->pagina($request);
        foreach (['q', 'estado'] as $f) {
            if ($request->filled($f)) {
                $params[$f] = $request->query($f);
            }
        }

        $data = $this->api->getReservas($params);

        return response()->view('reservas._tabla', array_merge(
            $this->vars($data),
            ['reservas' => $data['content'] ?? []]
        ));
    }

    public function ejemplares(Request $request): \Illuminate\Http\JsonResponse
    {
        $libroId = (int) $request->query('libroId', 0);
        if ($libroId <= 0) {
            return response()->json([]);
        }

        return response()->json(
            $this->api->getInventarioDisponible(['libroId' => $libroId])
        );
    }

    public function ejemplarPorCodigo(Request $request): \Illuminate\Http\JsonResponse
    {
        $codigo = trim((string) $request->query('codigo', ''));
        if ($codigo === '') {
            return response()->json(['mensaje' => 'Código vacío'], 422);
        }

        $ejemplar = $this->api->buscarInventarioPorCodigo($codigo);

        if ($ejemplar === null) {
            return response()->json(['mensaje' => "No existe un ejemplar con el código {$codigo}"], 404);
        }

        return response()->json($ejemplar);
    }

    protected function pagina(Request $request): array
    {
        return [
            'page' => max(0, (int) $request->query('page', 0)),
            'size' => min(50, max(5, (int) $request->query('size', 10))),
        ];
    }

    protected function vars(array $data): array
    {
        return [
            'page'       => $data['page'] ?? 0,
            'size'       => $data['size'] ?? 10,
            'total'      => $data['totalElements'] ?? 0,
            'totalPages' => $data['totalPages'] ?? 0,
            'first'      => $data['first'] ?? true,
            'last'       => $data['last'] ?? true,
        ];
    }
}
