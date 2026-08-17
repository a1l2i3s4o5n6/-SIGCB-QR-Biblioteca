<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index(Request $request): View
    {
        $q = $request->query('q', '');
        $page = max(0, (int) $request->query('page', 0));
        $size = min(50, max(5, (int) $request->query('size', 10)));

        $params = ['page' => $page, 'size' => $size];

        if ($q !== '') {
            $data = $this->api->buscarLibros($q, $params);
        } else {
            $data = $this->api->getLibros($params);
        }

        return view('catalogo.index', [
            'libros'      => $data['content'] ?? [],
            'page'        => $data['page'] ?? $page,
            'size'        => $data['size'] ?? $size,
            'total'       => $data['totalElements'] ?? 0,
            'totalPages'  => $data['totalPages'] ?? 0,
            'first'       => $data['first'] ?? true,
            'last'        => $data['last'] ?? true,
            'q'           => $q,
        ]);
    }

    public function show(int $id): View
    {
        $data = $this->api->getLibro($id);
        $libro = $data['data'] ?? $data;

        return view('catalogo.show', ['libro' => $libro]);
    }

    public function create(): View
    {
        return view('catalogo.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            $this->api->crearLibro($data);
            return redirect()->route('catalogo.index')
                ->with('success', 'Libro creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(int $id): View
    {
        $data = $this->api->getLibro($id);
        $libro = $data['data'] ?? $data;

        return view('catalogo.edit', array_merge($this->formData(), ['libro' => $libro]));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            $this->api->actualizarLibro($id, $data);
            return redirect()->route('catalogo.show', $id)
                ->with('success', 'Libro actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->api->eliminarLibro($id);
            return redirect()->route('catalogo.index')
                ->with('success', 'Libro desactivado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function formData(): array
    {
        $autores = $this->api->getAutores(['size' => 200]);
        $editoriales = $this->api->getEditoriales(['size' => 200]);
        $categorias = $this->api->getCategorias(['size' => 200]);

        return [
            'autores'     => $autores['content'] ?? [],
            'editoriales' => $editoriales['content'] ?? [],
            'categorias'  => $categorias['content'] ?? [],
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'titulo'             => ['required', 'string', 'max:255'],
            'isbn'               => ['nullable', 'string', 'max:50'],
            'anioPublicacion'    => ['nullable', 'integer', 'between:1500,2100'],
            'edicion'            => ['nullable', 'string', 'max:50'],
            'ejemplaresTotales'  => ['nullable', 'integer', 'min:0'],
            'ubicacion'          => ['nullable', 'string', 'max:100'],
            'descripcion'        => ['nullable', 'string'],
            'categoriaId'        => ['nullable', 'integer'],
            'editorialId'        => ['nullable', 'integer'],
            'autorIds'           => ['nullable', 'array'],
            'autorIds.*'         => ['integer'],
        ]);
    }
}