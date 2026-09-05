# Informe — Rediseño del centro de control (dashboard)

> Entrega del plan de 17 puntos aprobado para el rediseño del centro de control de
> SIGCB-QR. Documento de entrega: describe lo implementado, cómo verificarlo y el
> estado de la evidencia al 2026-09-01.
>
> Nota: este informe se estructura por componentes entregados. Si la lista de
> control del docente numera el plan de otra forma, el mapeo es directo porque
> cada sección nombra el archivo y el comportamiento que la verifica.

## 1. Objetivo y alcance

Reemplazar el tablero genérico —que en entregas anteriores mostró cifras fijas
inventadas (OBS-10)— por un centro de control real: KPIs agregados sobre la base
de datos, gráficos de actividad, panel de sanciones y multas, módulo QR, alertas
priorizadas, feed de actividad desde auditoría, estado del sistema, seguimiento
de préstamos/reservas/usuarios, accesos por rol y un dashboard personal para el
rol ESTUDIANTE.

Principio rector: **nada simulado y nada fijo en plantillas**. Toda cifra sale de
la API y todo dato de la base de datos real (20 libros, 61 ejemplares
disponibles, 7 préstamos activos, 8 vencidos, 2 multas por $35.00, 1 sanción
activa, 6 QR activos / 2 inactivos, 5 reservas en el período — snapshot de la
e2e del 2026-09-01).

## 2. Arquitectura

Se conserva el patrón *Backend for Frontend* (ADR-0002): el frontend Laravel
nunca toca la base de datos; consume REST por `ApiClient`. El nuevo centro de
control se resuelve con un único endpoint agregado:

- `GET /api/dashboard/resumen?desde=YYYY-MM-DD&hasta=YYYY-MM-DD`

que selecciona el alcance por el `rol` del JWT: `ADMIN` y `BIBLIOTECARIO` reciben
el resumen global de la biblioteca; `ESTUDIANTE` recibe únicamente sus propios
datos (préstamos, reservas, sanciones y multas personales). Un estudiante que
llame directamente a la API solo puede obtener su propio alcance; los paneles
staff son exclusivos del contrato de datos para staff.

Contexto de seguridad: la página del frontend se sirve con sesión servidor y
cookie HttpOnly (ADR-0003); el JWT nunca se expone al navegador.

## 3. Endpoints nuevos y conservados

| Endpoint | Rol | Propósito |
|---|---|---|
| `GET /api/dashboard/resumen` | ADMIN, BIBLIOTECARIO, ESTUDIANTE | Resumen del centro de control por rol, con rango de fechas opcional (por defecto últimos 30 días) |
| `GET /api/prestamos/mis?desde=&hasta=` | ESTUDIANTE | Préstamos personales paginados |
| `GET /api/dashboard/stats` | Autenticado | Conservado (compatibilidad con HU-10); implementación restaurada equivalente a `sp_dashboard_estadisticas` |

Controlador: `DashboardController.java`; servicio: `DashboardService.java`.

## 4. KPIs (staff)

Ocho tarjetas renderizadas; todas con datos agregados por repositorios con
consultas derivadas o JPQL:

- Libros registrados + nuevos en el período
- Ejemplares disponibles / prestados / dañados (por estado en inventario)
- Préstamos activos, con subíndice de vencidos y de vencimientos en 24 h
- Usuarios registrados, nuevos en el período y activos
- Códigos QR activos (con inactivos y creados en el período)
- Sanciones activas (obligatoria, con prioridad: roja si hay vencidas)
- Multas pendientes y total acumulado
- Reservas por estado (pendientes, confirmadas, completadas, canceladas)

La respuesta incluye además `prestamosVencidos` (estado VENCIDO + ACTIVO con
fecha de vencimiento pasada), `prestamosProximos24h`, `prestamosProximos7dias`,
`prestamosReservados`, `prestamosDevueltos`, `sancionesVencidas`,
`sancionesProximas`, `sancionesResueltas`, métricas de QR por evento de auditoría
y `usuariosPorRol` con porcentaje. Ver `DashboardKpisResponse`,
`DashboardRolCantidadResponse`.

## 5. Encabezado y filtro de fechas

`_encabezado.blade.php` contiene el selector de rango (Alpine): presets
Ultimos 7 / 30 / 90 días y personalizado. Los campos `desde`/`hasta` viven en el
DOM con `x-show`, de modo que el GET al servidor siempre los incluye. El presets
por defecto es 30 días cuando no hay rango. El backend intercambia `desde` y
`hasta` si vienen invertidos y acota todas las series del período a ese rango.

## 6. Gráficos

`_graficos.blade.php` con Chart.js 4.4.1 por CDN (en `<head>` de
`layouts/app.blade.php`), antes del script de inicialización, que se protege con
un guard `typeof Chart`. Dos visuales:

- Serie temporal: `actividadPorDia` (préstamos, devoluciones, reservas, QR) para
  cada día del rango, rellenando huecos con 0.
- Dónut: `prestamosPorCategoria` (préstamos del período por categoría de libro,
  con porcentaje).

Si el período no tiene préstamos, la serie llega vacía y la vista lo indica en
lugar de inventar datos.

## 7. Panel de sanciones y multas

`_sanciones_multas.blade.php` resume: sanciones activas, vencidas y próximas a
vencer (fechaFin dentro de 3 días), multas pendientes, generadas y pagadas en el
período, con acceso directo a los módulos. Las categorías son derivadas en
`DashboardService` a partir de `Sancion.activa` y `Sancion.fechaFin`.

## 8. Módulo QR

`_qr.blade.php`: QR activos / inactivos y desglose por evento del período
(creados, regenerados, activados, desactivados), obtenido de la auditoría, no de
estimaciones.

## 9. Alertas priorizadas

`alertas` llega ordenada por severidad (CRITICA → BAJA):

- 8 préstamos vencidos → PELIGRO/CRITICA
- Sanciones activas sin vencidas → ADVERTENCIA/ALTA
- Multas pendientes ($35.00) → INFORMACION/MEDIA
- Ejemplar dañado, reservas pendientes → INFORMACION/MEDIA
- QR inactivos → INFORMACION/BAJA

Cada alerta lleva enlace al módulo correspondiente.

## 10. Actividad reciente (feed de auditoría)

`_actividad.blade.php` consume `actividadReciente`: últimos 12 eventos de
`auditoria` (fuente de eventos del sistema), con mapa de iconos por entidad y
acción. Se excluyen del feed los eventos de LOGIN y VALIDAR para no ensuciar la
vista.

## 11. Estado del sistema

`_estado.blade.php` muestra: base de datos operativa (petición real de conteo),
API operativa, módulo QR operativo y **respaldo declarado como "No disponible"**
cuando no existe información de respaldo en la aplicación. No se afirma nada que
no se pueda comprobar.

## 12. Seguimiento (préstamos / reservas / usuarios)

`_seguimiento.blade.php` presenta los indicadores de operación: préstamos activos,
vencimientos en 24 h y 7 días, devoluciones registradas en el período, reservas
por estado y distribución de usuarios por rol con porcentaje y total.

## 13. Accesos por rol

La frontera es de contrato y de vista:

- Backend: `resumen()` ramifica por `principal.rol()`; el alcance estudiante
  está scoped a `usuarioId` (incluso `prestamosDevueltosPeriodo`, corregido para
  no filtrar el total global a un estudiante).
- Frontend: `DashboardController::index` pasa el `rol` de sesión a la vista; la
  rama estudiante no renderiza paneles staff (verificado en la e2e).
- La distribución por rol usa `usuariosPorRol()` sobre usuarios activos.

## 14. Dashboard personal del estudiante

`_personal.blade.php`: bienvenida, indicadores personales (préstamos activos,
vencidos, próximos 24 h, reservas, sanciones activas, multas pendientes), títulos
disponibles en catálogo, sus alertas personales, su actividad (préstamos y
reservas propias) y estado del sistema. Sin cifras globales de staff.

## 15. Datos reales, sin simulación

Toda cifra procede de la base de datos real vía agregaciones. Durante la e2e
(2026-09-01) la respuesta admin reportó: 20 libros registrados, 20 con stock,
61 ejemplares disponibles, 7 prestados, 1 dañado, 7 préstamos activos, 8
vencidos, 7 usuarios (5 estudiantes), 2 multas por $35.00, 1 sanción activa,
6 QR activos / 2 inactivos, reservas 2/1/1/1 (pendientes/confirmadas/
completadas/canceladas). Ninguna constante en plantillas.

## 16. Notificaciones programadas (scheduler)

Nuevo `SchedulingConfig` (`@EnableScheduling`) y `NotificacionProgramadaService`
con `@Scheduled(cron = "0 0 */6 * * *")`. En cada corrida:

- Reclasifica préstamos ACTIVO → VENCIDO y notifica al usuario (idempotente,
  sin spam).
- Avisa vencimientos próximos (≤ 24 h) al usuario.
- Avisa sanciones vencidas al staff (ADMIN + BIBLIOTECARIO) y próximas a vencer
  (≤ 3 días) al usuario.
- Deduplica avisos con ventana de 6 horas
  (`countByUsuarioIdAndTituloContainingAndCreatedAtAfter`).

La primera corrida no genera ruido: el único préstamo vencido ya estaba como
tal y la sanción vigente caduca más allá del umbral.

## 17. Calidad y verificación

- **Suite backend**: `mvn verify` en contenedor → 95 pruebas, 0 fallos, 0
  errores; checks JaCoCo cumplidos. Procedimiento en `COBERTURA.md`.
- **Correcciones posteriores a la suite**: `librosDisponibles` en el contrato
  staff y `prestamosDevueltosPeriodo` scoped al estudiante (método
  `countByUsuarioIdAndFechaDevolucionBetween`). Re-verificación: 55/55 verde.
- **E2E real**: adm (staff) y estudiante nuevo (`e2e.test@estudiante.com`) por
  API (`/api/dashboard/resumen`, `/api/prestamos/mis`) y por frontend
  (`/dashboard`), con cookies de sesión. Se verificó la rama por rol, el filtro
  de fechas por defecto (30 días) y la ausencia de paneles staff en la vista
  estudiante.
- **Estrategia de caché**: se decidió **no cachear** el resumen con `@Cacheable`.
  Un TTL global dejaría datos obsoletos frente al requisito de reflejar acciones
  recientes de inmediato, y evictar en todas las mutaciones sería invasivo y
  frágil. Las agregaciones usan queries indexadas y son suficientes para la
  carga del centro de control.
- **Sin migraciones de esquema**: el rediseño no cambia la base de datos; las
  vistas y el scheduler reutilizan tablas existentes (versión de esquema 11,
  migraciones Flyway hasta V10 + datos de desarrollo).
- **Pendientes conocidos**: respaldo del sistema sin implementar (se muestra
  "No disponible" honestamente); métricas de rendimiento del nuevo endpoint no
  medidas con k6 todavía.

## Archivos entregados o modificados

Backend:

- `service/DashboardService.java` (resumen por rol, `getStats()` restaurado,
  `usuariosPorRol()`, `alertas*`, `seriesPorDia`, `prestamosPorCategoria`)
- `model/dto/response/dashboard/` (8 DTOs; nuevos `DashboardRolCantidadResponse`,
  `DashboardResumenResponse`, `DashboardEstadoSistemaResponse`, etc.)
- `controller/DashboardController.java` (`/api/dashboard/resumen`)
- `controller/PrestamoController.java` (`/api/prestamos/mis`)
- `repository/PrestamoRepository.java`, `LibroRepository.java`,
  `UsuarioRepository.java` (`findByActivoTrue`), `AuditoriaRepository.java`
  (`countQrEventos`, `findActividadReciente`)
- `config/SchedulingConfig.java`, `service/NotificacionProgramadaService.java`

Frontend:

- `app/Services/ApiClient.php` (`getDashboardResumen`)
- `app/Http/Controllers/DashboardController.php` (rango + rol de sesión)
- `resources/views/dashboard.blade.php` y `resources/views/dashboard/_*.blade.php`
  (11 partials: encabezado, kpis, graficos, sanciones_multas, qr, alertas,
  actividad, estado, accesos, seguimiento, personal)
- `resources/views/layouts/app.blade.php` (CDN Chart.js)
- `resources/css/app.css` (estilo de `.stat-card`)

## Cómo reproducirlo

```bash
# Suite de prueba (contenedor)
docker run --rm --network sigcbqr-test-net \
  -v "C:/Proyecto_web_biblioteca/-SIGCB-QR-Biblioteca/backend:/app" \
  -v sigcbqr-m2:/root/.m2 -w /app \
  -e SPRING_DATASOURCE_URL=jdbc:postgresql://sigcbqr-test-pg:5432/sigcbqr_test \
  -e SPRING_DATASOURCE_USERNAME=postgres -e SPRING_DATASOURCE_PASSWORD=test123 \
  -e REDIS_HOST=sigcbqr-test-redis maven:3.9-eclipse-temurin-21 \
  mvn -B clean verify

# Despliegue
docker compose build api frontend && docker compose up -d api frontend

# Verificación manual (staff)
curl -s -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" \
  -d '{"email":"admin@biblioteca.com","password":"admin123"}'
curl -s http://localhost:8080/api/dashboard/resumen -H "Authorization: Bearer <token>"
```