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

    public function crearPrestamo(int $usuarioId, int $inventarioId, ?string $codigoQr = null): array
    {
        return $this->withAuth()->post('/prestamos', [
            'usuarioId'    => $usuarioId,
            'inventarioId' => $inventarioId,
            'codigoQr'     => $codigoQr,
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

    public function misPrestamos(array $params = []): array
    {
        return $this->withAuth()->get('/prestamos/mis', $params)->json() ?? [];
    }

    public function solicitarRenovacion(int $id): array
    {
        $response = $this->withAuth()->put("/prestamos/{$id}/solicitar-renovacion");

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo solicitar la renovación.');
        }

        return $response->json('data') ?? [];
    }

    public function renovacionesPendientes(array $params = []): array
    {
        return $this->withAuth()->get('/prestamos/renovaciones-pendientes', $params)->json() ?? [];
    }

    public function aprobarRenovacion(int $id): array
    {
        $response = $this->withAuth()->put("/prestamos/{$id}/aprobar-renovacion");

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo aprobar la renovación.');
        }

        return $response->json('data') ?? [];
    }

    public function rechazarRenovacion(int $id): array
    {
        $response = $this->withAuth()->put("/prestamos/{$id}/rechazar-renovacion");

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo rechazar la renovación.');
        }

        return $response->json('data') ?? [];
    }

    // ─────────────────────────────────────────────
    // INVENTARIO (ejemplares disponibles)
    // ─────────────────────────────────────────────

    public function getInventarioDisponible(array $params = []): array
    {
        return $this->withAuth()->get('/inventario/disponibles', $params)->json() ?? [];
    }

    /**
     * Localiza un ejemplar por su código único (ej. LIB-0001-01).
     * Retorna null si no existe.
     */
    public function buscarInventarioPorCodigo(string $codigo): ?array
    {
        $response = $this->withAuth()->get('/inventario/buscar', ['codigo' => $codigo]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function getReservas(array $params = []): array
    {
        return $this->withAuth()->get('/reservas', $params)->json() ?? [];
    }

    public function getReservasMias(array $params = []): array
    {
        return $this->withAuth()->get('/reservas/mis', $params)->json() ?? [];
    }

    public function crearReserva(int $usuarioId, int $libroId): array
    {
        return $this->withAuth()->post('/reservas', [
            'usuarioId' => $usuarioId,
            'libroId'   => $libroId,
        ])->json() ?? [];
    }

    public function autoReserva(int $libroId): array
    {
        $response = $this->withAuth()->post('/reservas/mias', ['libroId' => $libroId]);

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo reservar el libro.');
        }

        return $response->json('data') ?? [];
    }

    public function misReservas(array $params = []): array
    {
        return $this->withAuth()->get('/reservas', $params)->json() ?? [];
    }

    public function cancelarReserva(int $id): array
    {
        return $this->withAuth()->delete("/reservas/{$id}")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // MULTAS
    // ─────────────────────────────────────────────

    public function getMultas(array $params = []): array
    {
        return $this->withAuth()->get('/multas', $params)->json() ?? [];
    }

    public function getMultasMias(array $params = []): array
    {
        return $this->withAuth()->get('/multas/mis', $params)->json() ?? [];
    }

    public function pagarMulta(int $id): array
    {
        return $this->withAuth()->post("/multas/{$id}/pagar")->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // REPORTES
    // ─────────────────────────────────────────────

    public function getReportePrestamosDiarios(): array
    {
        return $this->withAuth()->get('/reportes/prestamos-diarios')->json() ?? [];
    }

    public function getReporteLibrosMasSolicitados(): array
    {
        return $this->withAuth()->get('/reportes/libros-mas-solicitados')->json() ?? [];
    }

    public function getReporteMultasCobradas(): array
    {
        return $this->withAuth()->get('/reportes/multas-cobradas')->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // CONFIGURACIÓN
    // ─────────────────────────────────────────────

    public function getConfiguracion(): array
    {
        $response = $this->withAuth()->get('/configuracion');

        if (!$response->successful()) {
            return [];
        }

        return $response->json('data') ?? [];
    }

    public function actualizarConfiguracion(int $id, string $valor): array
    {
        return $this->withAuth()->put("/configuracion/{$id}", ['valor' => $valor])->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // AUDITORÍA
    // ─────────────────────────────────────────────

    public function getAuditoria(array $params = []): array
    {
        $response = $this->withAuth()->get('/auditoria', $params);

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
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

    public function getDashboardResumen(array $params = []): array
    {
        $response = $this->withAuth()->get('/dashboard/resumen', $params);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('data') ?? [];
    }

    // ─────────────────────────────────────────────
    // CÓDIGOS QR
    // ─────────────────────────────────────────────

    public function getQrCodigos(array $params = []): array
    {
        $response = $this->withAuth()->get('/qr-codigos', $params);

        if (!$response->successful()) {
            return [];
        }

        return $response->json('data') ?? [];
    }

    public function getQrByLibro(int $libroId): array
    {
        $response = $this->withAuth()->get("/qr-codigos/libro/{$libroId}");

        if (!$response->successful()) {
            return [];
        }

        return $response->json('data') ?? [];
    }

    public function crearQr(int $libroId): array
    {
        return $this->withAuth()->post('/qr-codigos', ['libroId' => $libroId])->json() ?? [];
    }

    public function regenerarQr(int $id): array
    {
        return $this->withAuth()->put("/qr-codigos/{$id}/regenerar")->json() ?? [];
    }

    public function toggleQr(int $id, bool $activo): array
    {
        return $this->withAuth()
            ->put("/qr-codigos/{$id}/activo?activo=" . ($activo ? '1' : '0'))
            ->json() ?? [];
    }

    /**
     * Valida un código QR (escaneado/ingresado) y devuelve la información del libro.
     * @throws \Exception si el código no existe o está inactivo.
     */
    public function validarQr(string $codigo): array
    {
        $response = $this->withAuth()->post('/qr-codigos/validar', ['codigo' => $codigo]);

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'Código QR no válido.');
        }

        return $response->json('data') ?? [];
    }

    // ─────────────────────────────────────────────
    // NOTIFICACIONES
    // ─────────────────────────────────────────────

    public function getNotificaciones(array $params = []): array
    {
        $response = $this->withAuth()->get('/notificaciones', $params);
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    public function getNotificacionesTodas(array $params = []): array
    {
        $response = $this->withAuth()->get('/notificaciones/todas', $params);
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    public function contarNotificacionesNoLeidas(): int
    {
        $response = $this->withAuth()->get('/notificaciones/no-leidas');
        $data = $response->successful() ? $response->json('data') : null;
        return is_numeric($data) ? (int) $data : 0;
    }

    public function crearNotificacion(array $data): array
    {
        $response = $this->withAuth()->post('/notificaciones', $data);

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo crear la notificación.');
        }

        return $response->json('data') ?? [];
    }

    public function marcarNotificacionLeida(int $id): array
    {
        return $this->withAuth()->put("/notificaciones/{$id}/leida")->json() ?? [];
    }

    public function marcarTodasNotificacionesLeidas(): array
    {
        return $this->withAuth()->put('/notificaciones/leer-todas')->json() ?? [];
    }

    // ─────────────────────────────────────────────
    // SANCIONES
    // ─────────────────────────────────────────────

    public function getSanciones(array $params = []): array
    {
        $response = $this->withAuth()->get('/sanciones', $params);
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    public function getSancionesMias(array $params = []): array
    {
        $response = $this->withAuth()->get('/sanciones/mis', $params);
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    public function getSancionesPorUsuario(int $usuarioId, array $params = []): array
    {
        $response = $this->withAuth()->get("/sanciones/usuario/{$usuarioId}", $params);
        return $response->successful() ? ($response->json() ?? []) : [];
    }

    public function crearSancion(array $data): array
    {
        $response = $this->withAuth()->post('/sanciones', $data);

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo aplicar la sanción.');
        }

        return $response->json('data') ?? [];
    }

    public function levantarSancion(int $id): array
    {
        $response = $this->withAuth()->put("/sanciones/{$id}/levantar");

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo levantar la sanción.');
        }

        return $response->json('data') ?? [];
    }

    // ─────────────────────────────────────────────
    // PERFIL (usuario autenticado)
    // ─────────────────────────────────────────────

    public function getMiPerfil(): array
    {
        $response = $this->withAuth()->get('/perfil');

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo obtener el perfil.');
        }

        return $response->json('data') ?? [];
    }

    public function actualizarMiPerfil(array $data): array
    {
        $response = $this->withAuth()->put('/perfil', $data);

        if (!$response->successful()) {
            throw new \Exception($response->json('detail') ?? $response->json('message') ?? 'No se pudo actualizar el perfil.');
        }

        return $response->json('data') ?? [];
    }
}
