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

    // ─────────────────────────────────────────────
    // PRÉSTAMOS
    // ─────────────────────────────────────────────

    public function getPrestamos(array $params = []): array
    {
        return $this->withAuth()->get('/prestamos', $params)->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // DASHBOARD / ESTADÍSTICAS
    // ─────────────────────────────────────────────

    public function getEstadisticas(): array
    {
        return $this->withAuth()->get('/reportes/estadisticas')->json() ?? [];
    }
}
