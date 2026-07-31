# Reporte de Mediciones de Rendimiento — SIGCB-QR

**Versión:** v0.9.0-rc
**Fecha:** 2026-07-30
**Estado:** PRELIMINAR (mediciones pendientes de ejecución)

---

## Resumen ejecutivo

| Métrica | Endpoint | Objetivo | Resultado | Estado |
|---------|----------|----------|-----------|--------|
| p95 | GET /api/libros | ≤ 200 ms | — | ⏳ Pendiente |
| Hit ratio | Redis (libros) | ≥ 80 % | — | ⏳ Pendiente |
| Tiempo inicio | docker-compose up | ≤ 2 min | — | ⏳ Pendiente |

---

## Metodología

- **Herramienta:** k6 v0.54+
- **Semilla:** 42
- **Corridas:** 3 (cache frío + cache caliente)
- **Intervalo de confianza:** 95 %
- **Estimadores:** media, desviación típica, p50, p90, p95, p99

## Corridas

### Corrida 1 — Cache frío
*Pendiente de ejecución*

### Corrida 2 — Cache caliente
*Pendiente de ejecución*

### Corrida 3 — Cache caliente (réplica)
*Pendiente de ejecución*

---

## Análisis de hit ratio de Redis

| Métrica | Valor |
|---------|-------|
| keyspace_hits | — |
| keyspace_misses | — |
| Hit ratio | — |
| TTL configurado | 300s |

---

## Apéndice: Datos crudos

Los archivos JSON de cada corrida se almacenan en:
- `docs/mediciones/perf/k6-run1.json`
- `docs/mediciones/perf/k6-run2.json`
- `docs/mediciones/perf/k6-run3.json`
