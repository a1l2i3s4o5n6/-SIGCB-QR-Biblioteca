<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP centralizado para consumir la API REST de Spring Boot.
 * Todas las peticiones al backend pasan por esta clase.
 */
class ApiClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('api.base_url'), '/');
    }

    /**
     * Realiza una petición autenticada con el JWT almacenado en sesión.
     */
    protected function withAuth(): \Illuminate\Http\Client\PendingRequest
    {
        $token = session('api_token');
        return Http::baseUrl($this->baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * Petición sin autenticación (para login, etc.)
     */
    protected function withoutAuth(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(15);
    }

    // ─────────────────────────────────────────────
    // AUTH
    // ─────────────────────────────────────────────

    /**
     * Autentica al usuario contra el backend y retorna el token JWT.
     * @throws \Exception si las credenciales son incorrectas o el API falla.
     */
    public function login(string $email, string $password): array
    {
        $response = $this->withoutAuth()->post('/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        if ($response->status() === 401) {
            throw new \Exception('Credenciales incorrectas. Verifica tu correo y contraseña.');
        }

        throw new \Exception('Error al conectar con el servidor. Intenta más tarde.');
    }

    /**
     * Cierra sesión en el backend (invalida el JWT en Redis).
     */
    public function logout(): void
    {
        try {
            $this->withAuth()->post('/auth/logout');
        } catch (\Exception $e) {
            Log::warning('No se pudo invalidar el token en el backend: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // USUARIOS
    // ─────────────────────────────────────────────

    public function getUsuarios(array $params = []): array
    {
        return $this->withAuth()->get('/usuarios', $params)->json() ?? [];
    }

    public function getUsuario(int $id): array
    {
        return $this->withAuth()->get("/usuarios/{$id}")->json() ?? [];
    }

    public function crearUsuario(array $data): array
    {
        return $this->withAuth()->post('/usuarios', $data)->json() ?? [];
    }

    public function actualizarUsuario(int $id, array $data): array
    {
        return $this->withAuth()->put("/usuarios/{$id}", $data)->json() ?? [];
    }

    public function eliminarUsuario(int $id): array
    {
        return $this->withAuth()->delete("/usuarios/{$id}")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // LIBROS
    // ─────────────────────────────────────────────

    public function getLibros(array $params = []): array
    {
        return $this->withAuth()->get('/libros', $params)->json() ?? [];
    }

    public function getLibro(int $id): array
    {
        return $this->withAuth()->get("/libros/{$id}")->json() ?? [];
    }

    public function buscarLibros(string $q, array $params = []): array
    {
        return $this->withAuth()->get('/libros/buscar', array_merge(['q' => $q], $params))->json() ?? [];
    }

    public function crearLibro(array $data): array
    {
        return $this->withAuth()->post('/libros', $data)->json() ?? [];
    }

    public function actualizarLibro(int $id, array $data): array
    {
        return $this->withAuth()->put("/libros/{$id}", $data)->json() ?? [];
    }

    public function eliminarLibro(int $id): array
    {
        return $this->withAuth()->delete("/libros/{$id}")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // CATÁLOGOS (autores, editoriales, categorías)
    // ─────────────────────────────────────────────

    public function getAutores(array $params = []): array
    {
        return $this->withAuth()->get('/autores', $params)->json() ?? [];
    }

    public function getEditoriales(array $params = []): array
    {
        return $this->withAuth()->get('/editoriales', $params)->json() ?? [];
    }

    public function getCategorias(array $params = []): array
    {
        return $this->withAuth()->get('/categorias', $params)->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // PRÉSTAMOS
    // ─────────────────────────────────────────────

    public function getPrestamos(array $params = []): array
    {
        return $this->withAuth()->get('/prestamos', $params)->json() ?? [];
    }

    public function getPrestamo(int $id): array
    {
        return $this->withAuth()->get("/prestamos/{$id}")->json() ?? [];
    }

    public function crearPrestamo(int $usuarioId, int $inventarioId): array
    {
        return $this->withAuth()->post('/prestamos', [
            'usuarioId'    => $usuarioId,
            'inventarioId' => $inventarioId,
        ])->json() ?? [];
    }

    public function devolverPrestamo(int $id): array
    {
        return $this->withAuth()->put("/prestamos/{$id}/devolver")->json() ?? [];
    }

    public function renovarPrestamo(int $id): array
    {
        return $this->withAuth()->put("/prestamos/{$id}/renovar")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // INVENTARIO (ejemplares disponibles)
    // ─────────────────────────────────────────────

    public function getInventarioDisponible(array $params = []): array
    {
        return $this->withAuth()->get('/inventario/disponibles', $params)->json() ?? [];
    }

    public function getReservas(array $params = []): array
    {
        return $this->withAuth()->get('/reservas', $params)->json() ?? [];
    }

    public function crearReserva(int $usuarioId, int $libroId): array
    {
        return $this->withAuth()->post('/reservas', [
            'usuarioId' => $usuarioId,
            'libroId'   => $libroId,
        ])->json() ?? [];
    }

    public function cancelarReserva(int $id): array
    {
        return $this->withAuth()->delete("/reservas/{$id}")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // DASHBOARD / ESTADÍSTICAS
    // ─────────────────────────────────────────────

    public function getEstadisticas(): array
    {
        $response = $this->withAuth()->get('/dashboard/stats');

        if (!$response->successful()) {
            return [];
        }

        return $response->json('data') ?? [];
    }
}
