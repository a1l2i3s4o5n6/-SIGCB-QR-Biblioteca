# ADR-0007: Anclar las imágenes de contenedor por digest SHA256

- **Estado:** Aceptado
- **Fecha:** 2026-08-30
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-NF-004; observación OBS-07

## Contexto

`docker-compose.yml` y el flujo de CI referenciaban las imágenes por etiqueta
(`postgres:16`, `redis:7`, `dpage/pgadmin4:latest`). Una etiqueta es un puntero
móvil: `postgres:16` señala hoy a una imagen y mañana a otra. Dos personas que
ejecutan `make up` con una semana de diferencia pueden estar midiendo sistemas
distintos, lo que invalida cualquier comparación de rendimiento. La observación
OBS-07 lo señaló.

Un antecedente propio refuerza el punto: al fijar los digest por primera vez se
escribieron **valores truncados**, de 62 y 63 caracteres hexadecimales en lugar de
64. Un digest inválido no degrada en silencio: `docker compose pull` falla y el
sistema no arranca. El error pasó desapercibido hasta que un evaluador contó los
caracteres. La lección no es «poner digest», sino «comprobar automáticamente que
el digest es válido».

## Opciones consideradas

1. **Seguir con etiquetas móviles.** Cero mantenimiento, reproducibilidad nula.
2. **Etiquetas de versión completa** (`postgres:16.15-bookworm`). Mejora mucho,
   pero una etiqueta de versión sigue pudiendo reconstruirse con otro parche del
   sistema base.
3. **Digest SHA256** (`imagen:etiqueta@sha256:<64 hex>`). Identifica un manifiesto
   inmutable: siempre los mismos bytes.

## Decisión

Se adopta la opción 3 para **todas** las imágenes de terceros, tanto en
`docker-compose.yml` como en los servicios de `.github/workflows/ci.yml`. Se
conserva la etiqueta legible junto al digest (`postgres:16@sha256:…`) porque el
digest no dice qué versión es.

Digest vigentes, verificados contra el registro el 2026-09-01:

| Servicio | Referencia |
|---|---|
| postgres (compose) | `postgres:16@sha256:f1c3376c…df6f94` |
| redis (compose) | `redis:7@sha256:71da9275…8059f7` |
| pgadmin (compose) | `dpage/pgadmin4:latest@sha256:2f4ce946…2c2f4d` |
| postgres (CI) | `postgres:16-alpine@sha256:cf78e766…c20685` |
| redis (CI) | `redis:7-alpine@sha256:ff02b58f…28eadf` |

Y, sobre todo, **la comprobación es automática**: `scripts/validate-digests.py`
recorre ambos archivos, exige que toda entrada `image:` lleve digest y que el
digest tenga exactamente 64 caracteres hexadecimales. Se ejecuta con `make verify`
y en el trabajo `validate-traceability` de CI. Un digest truncado ya no puede
llegar a una entrega.

## Consecuencias

### Positivas

- `make up` levanta hoy el mismo sistema que levantará dentro de seis meses:
  las mediciones de `docs/mediciones/` son comparables entre corridas.
- Un digest inválido rompe CI en segundos en lugar de romper la demostración.
- Queda constancia exacta de qué se midió, que es lo que un informe con evidencia
  empírica necesita declarar.

### Negativas

- **Las actualizaciones de seguridad dejan de llegar solas.** Anclar es congelar:
  si aparece un CVE en `postgres:16`, el proyecto seguirá arrancando la imagen
  vulnerable hasta que alguien cambie el digest a mano. Es el coste real de esta
  decisión y exige revisar los digest de forma periódica.
- Los digest son ilegibles y hacen ruido en los *diffs*.
- Cambiar de versión pasa a ser un paso deliberado: consultar el registro, copiar
  el digest, verificar. Ese es justamente el objetivo, pero cuesta tiempo.

## Verificación

```
$ make verify
  [OK] docker-compose.yml: postgres:16@sha256:f1c3…f94 (71 chars -> OK)
  ...
  Todos los digests SHA256 son válidos (64 caracteres hexadecimales).
```

Comprobación de que cada digest existe realmente en el registro (no solo de que
tiene la longitud correcta):

```
$ docker manifest inspect postgres:16@sha256:f1c3376c…df6f94 > /dev/null && echo OK
```

Las cinco referencias se contrastaron contra `registry-1.docker.io` el 2026-09-01
y coinciden con el `Docker-Content-Digest` que devuelve el registro para su
etiqueta.
