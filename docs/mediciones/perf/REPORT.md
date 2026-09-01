# Reporte de mediciones de rendimiento — SIGCB-QR

**Fecha de medición:** 2026-09-01
**Versión medida:** `v1.0.0-rc`
**Herramienta:** k6 (imagen `grafana/k6:latest`)
**Endpoint medido:** `GET /api/libros?page=0&size=10`
**Requisitos asociados:** REQ-NF-001, REQ-R-002, REQ-R-004
**Salidas crudas:** [`k6-02-cache-caliente.txt`](k6-02-cache-caliente.txt),
[`k6-03-cache-caliente.txt`](k6-03-cache-caliente.txt),
[`k6-04-cache-fria-jvm-caliente.txt`](k6-04-cache-fria-jvm-caliente.txt)

---

## 1. Resumen ejecutivo

| Métrica | Endpoint | Objetivo | Resultado | Estado |
|---|---|---|---|---|
| p95 | `GET /api/libros` | ≤ 200 ms | **26–83 ms** según corrida | Cumple |
| Tasa de error | `GET /api/libros` | < 1 % | **0,00 %** (1 474 peticiones) | Cumple |
| Hit ratio | Redis (caché `libros`) | ≥ 80 % | **99,7 %** | Cumple |

Las tres corridas superan el umbral de REQ-NF-001 con holgura. El resultado más
interesante, sin embargo, no es que se cumpla el umbral, sino **qué explica la
variación entre corridas**: ver §5.

---

## 2. Cómo reproducir

```bash
make up
docker exec sigcbqr-redis redis-cli FLUSHALL        # sólo para la corrida en frío
docker exec sigcbqr-redis redis-cli CONFIG RESETSTAT

docker run --rm --network sigcb-qr-biblioteca_default \
  -v "$PWD/scripts":/scripts \
  -e BASE_URL=http://sigcbqr-api:8080 \
  grafana/k6:latest run /scripts/k6-load-test.js
```

El contenedor de k6 se une a la red de compose y llama a la API por su nombre de
servicio, de modo que la medición no atraviesa la traducción de puertos del host.

### Perfil de carga

| Fase | Duración | Usuarios virtuales |
|---|---|---|
| Calentamiento | 10 s | 0 → 5 |
| Carga sostenida | 20 s | 5 → 20 |
| Descenso | 10 s | 20 → 0 |

Cada iteración: una petición al catálogo y una pausa de 1 s. Alrededor de 370
iteraciones por corrida.

### Entorno

- Windows 11 Pro (26200), Docker 29.5.2 sobre WSL2
- API: Java 21 (Temurin), Spring Boot 3.5.16, contenedor sin límite de CPU
- PostgreSQL 16.15 y Redis 7, ambos anclados por digest (ADR-0007)
- Datos: semilla `V3__datos_semilla.sql` — **20 libros, 69 ejemplares**
- Umbrales declarados en el propio script: `catalogo_duracion p(95)<200`,
  `catalogo_errores rate<0.01`

---

## 3. Un defecto del instrumento, corregido antes de medir

La versión anterior de `scripts/k6-load-test.js` **iniciaba sesión en cada
iteración de cada usuario virtual**. Con el límite de tasa de 5 intentos por IP y
minuto (`RateLimitService`), a partir del quinto login todas las peticiones
recibían 429.

El resultado habría sido inválido en dos direcciones a la vez:

- la tasa de error se dispara, y
- **el p95 sale artificialmente bajo**, porque un 429 se responde sin tocar la
  base de datos ni la caché.

Es decir, la prueba habría medido la velocidad del limitador de tasa y la habría
presentado como velocidad del catálogo — un número excelente que no significa
nada. El script se corrigió para autenticar una sola vez en `setup()`, fuera de
la ventana de medición, y reutilizar el token en todos los usuarios virtuales.

Se documenta porque explica por qué las corridas anteriores a esta entrega
figuraban como «pendientes»: el instrumento no estaba en condiciones de producir
un dato defendible.

---

## 4. Corridas

Orden cronológico real de ejecución. Se numeran como se ejecutaron, no como
convendría al relato.

### Corrida 1 — JVM fría + caché fría (exploratoria)

Primera ejecución tras reconstruir el contenedor de la API.

| Métrica | Valor |
|---|---:|
| p95 | 83,43 ms |
| media | 57,19 ms |
| mediana | 17,28 ms |
| **máximo** | **5,36 s** |
| Iteraciones | 360 |
| Errores | 0 |

Corrida exploratoria: sirvió para comprobar que el arnés funcionaba. **No se
conservó su salida cruda**, así que sus cifras se reportan como orientativas y no
se usan para concluir. Se incluye porque su máximo de 5,36 s es el dato que
motiva el análisis de §5.

### Corrida 2 — caché caliente

| Métrica | Valor |
|---|---:|
| p95 | **73,02 ms** |
| p90 | 52,22 ms |
| media | 28,21 ms |
| mediana | 20,48 ms |
| máximo | 196,67 ms |
| Iteraciones | 370 (9,00 it/s) |
| Comprobaciones | 740/740 correctas |
| Errores | 0,00 % |

### Corrida 3 — caché caliente (réplica)

| Métrica | Valor |
|---|---:|
| p95 | **64,89 ms** |
| p90 | 49,98 ms |
| media | 25,61 ms |
| mediana | 17,79 ms |
| máximo | 212,33 ms |
| Iteraciones | 369 (9,10 it/s) |
| Comprobaciones | 738/738 correctas |
| Errores | 0,00 % |

### Corrida 4 — caché fría, JVM caliente

Ejecutada tras `FLUSHALL` y `CONFIG RESETSTAT`, con la JVM ya rodada por las
corridas anteriores.

| Métrica | Valor |
|---|---:|
| p95 | **26,41 ms** |
| p90 | 20,90 ms |
| media | 12,66 ms |
| mediana | 9,40 ms |
| máximo | 228,27 ms |
| Iteraciones | 375 (9,06 it/s) |
| Comprobaciones | 750/750 correctas |
| Errores | 0,00 % |

---

## 5. Análisis: lo que domina la latencia **no** es la caché

Puestas en orden cronológico, las cifras dicen algo que contradice la hipótesis de
partida:

| Corrida | Estado de la caché | Estado de la JVM | p95 |
|---|---|---|---:|
| 1 | fría | **fría** | 83,43 ms |
| 2 | caliente | templada | 73,02 ms |
| 3 | caliente | caliente | 64,89 ms |
| 4 | **fría** | **caliente** | **26,41 ms** |

La corrida 4 tiene la caché **vacía** y es, con diferencia, la más rápida: su p95
es menos de la mitad que el de las corridas con caché llena. La conclusión es
directa:

> **A esta escala, el p95 lo determina el calentamiento de la JVM, no el estado de
> la caché de Redis.**

Tiene una explicación sencilla y comprobable: la semilla tiene **20 libros**. Una
página de diez filas se resuelve en PostgreSQL con un índice y un `LIMIT`, y ese
trabajo es comparable —o menor— al de serializar el resultado y hacer un viaje de
ida y vuelta a Redis. Lo que sí cuesta al principio es que el JIT compile las
rutas de Hibernate, Jackson y Spring Security: de ahí el máximo de 5,36 s en la
primera petición de la corrida 1 y su desaparición progresiva.

**Consecuencia para el informe:** no puede afirmarse que la caché de Redis mejore
el rendimiento de este sistema. Los datos disponibles no lo sostienen; a este
volumen, más bien sugieren lo contrario. La caché sigue estando justificada por
ADR-0006 como mecanismo que escalará con el acervo real (decenas de miles de
títulos), pero **eso es una previsión, no una medición**. Demostrarlo exige un
conjunto de datos de otro orden de magnitud, y esa medición no se ha hecho.

Es exactamente el tipo de afirmación que en entregas anteriores se dio por buena
sin comprobar.

---

## 6. Hit ratio de Redis

Medido con `redis-cli INFO stats` inmediatamente después de la corrida 4, con las
estadísticas reiniciadas justo antes:

```
keyspace_hits:376
keyspace_misses:1
```

**Hit ratio = 376 / 377 = 99,73 %**, muy por encima del objetivo del 80 %.

El único fallo es la primera petición, que puebla la entrada; las 375 restantes la
aciertan. La clave observada es:

```
$ docker exec sigcbqr-redis redis-cli KEYS 'libros*'
libros::0-10-titulo: ASC
```

La clave incluye el criterio de orden, según la corrección de ADR-0006. Con la
clave anterior —sólo número y tamaño de página— dos peticiones con distinto orden
habrían compartido entrada.

Un hit ratio del 99,7 % es previsible y poco informativo en esta prueba: todos los
usuarios virtuales piden **la misma página con el mismo orden**. Con un patrón de
acceso realista, repartido entre páginas y filtros, sería sensiblemente menor.

---

## 7. Amenazas a la validez de estas mediciones

- **El volumen de datos no es representativo.** 20 libros y 69 ejemplares. Una
  biblioteca universitaria maneja varios órdenes de magnitud más, y las
  conclusiones sobre índices, paginación y utilidad de la caché no se transfieren.
- **Una sola máquina.** Cliente, API, base de datos y caché comparten CPU y
  memoria. En un despliegue real la latencia de red entre componentes sería un
  factor que aquí no existe.
- **20 usuarios virtuales concurrentes** es carga baja. No se localizó el punto de
  saturación ni se probó la degradación.
- **Sin repeticiones suficientes para inferencia.** Tres corridas comparables no
  permiten un intervalo de confianza defendible. Las cifras son descriptivas.
- **Un solo endpoint.** No se midieron los de préstamo ni los reportes, que
  ejecutan procedimientos almacenados y tienen un perfil distinto. Por eso
  REQ-R-001 y REQ-R-003 siguen como `pendiente` en la matriz de trazabilidad.
- **`grafana/k6:latest` no está anclado por digest**, a diferencia de las imágenes
  de `docker-compose.yml`. Es una inconsistencia con ADR-0007 en el arnés de
  medición.

## 8. Estado de los requisitos de rendimiento

| Requisito | Endpoint | Estado | Motivo |
|---|---|---|---|
| REQ-NF-001 | `GET /api/libros` | Medido, cumple | p95 26–83 ms frente a 200 ms |
| REQ-R-002 | `GET /api/libros` | Medido, cumple | Misma medición |
| REQ-R-004 | Caché de libros | Medido, matizado | Hit ratio 99,7 %, pero sin mejora de latencia demostrable a esta escala |
| REQ-R-001 | `POST /api/auth/login` | **Pendiente** | No medido: el límite de tasa impide una carga sostenida sin desactivarlo |
| REQ-R-003 | `POST /api/prestamos` | **Pendiente** | No medido: escribe y alteraría el estado de la base entre corridas |
