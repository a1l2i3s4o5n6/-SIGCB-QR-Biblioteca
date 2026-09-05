# Cobertura de pruebas — SIGCB-QR

**Fecha de medición:** 2026-09-05
**Herramienta:** JaCoCo 0.8.15 (`jacoco-maven-plugin`)
**Requisito asociado:** REQ-NF-005
**Informe HTML completo:** `backend/target/site/jacoco/index.html` (se genera con
`mvn verify`; no se versiona)
**Crudo versionado:** `docs/mediciones/cobertura/jacoco.xml` y `jacoco.csv` — copia
literal del informe que produjo `mvn verify`. Todas las cifras de este documento
son recalculables desde el CSV; el apartado 3.1 da el comando exacto.

---

## 1. Cómo reproducir

Las pruebas necesitan PostgreSQL y Redis reales: hay una prueba de contexto
(`SigcbQrApplicationTests`) que arranca la aplicación completa y aplica las
migraciones de Flyway.

La vía canónica es autocontenida y **genera sus propias credenciales efímeras de
prueba** (no fija ninguna en el repositorio):

```bash
make test        # = bash scripts/run-tests.sh
cp backend/target/site/jacoco/jacoco.{csv,xml} docs/mediciones/cobertura/
```

Para una reproducción manual con un contenedor desechable (la contraseña puede
ser cualquiera, el contenedor es de un solo uso):

```bash
docker network create sigcbqr-test-net
docker run -d --name sigcbqr-test-pg --network sigcbqr-test-net \
  -e POSTGRES_DB=sigcbqr_test -e POSTGRES_USER=postgres \
  -e POSTGRES_PASSWORD=test-only \
  postgres:16@sha256:f1c3376c26f2609ab9f29f71f824103fe2fcd8ee0346485cb6122a4f93df6f94
docker run -d --name sigcbqr-test-redis --network sigcbqr-test-net \
  redis:7@sha256:71da9275c5f3fcb97d0fa0c8c5b36cc995327265420f17a04bfd544f458059f7

docker run --rm --network sigcbqr-test-net \
  -v "$PWD/backend":/app -v sigcbqr-m2:/root/.m2 -w /app \
  -e SPRING_DATASOURCE_URL=jdbc:postgresql://sigcbqr-test-pg:5432/sigcbqr_test \
  -e SPRING_DATASOURCE_USERNAME=postgres -e SPRING_DATASOURCE_PASSWORD=test-only \
  -e TEST_JWT_SECRET="$(head -c 64 /dev/urandom | base64 | tr -d '\n')" \
  -e SEED_ADMIN_PASSWORD=test-only -e SEED_BIBLIO_PASSWORD=test-only \
  -e SEED_STUDENT_PASSWORD=test-only \
  -e REDIS_HOST=sigcbqr-test-redis \
  maven:3.9-eclipse-temurin-21 mvn -B clean verify
```

En CI equivale al trabajo `build-and-test` de `.github/workflows/ci.yml`, que
ejecuta el mismo arnés (`scripts/run-tests.sh`) con contraseñas aleatorias por
corrida.

---

## 2. Resultado de la ejecución

```
[INFO] Tests run: 152, Failures: 0, Errors: 0, Skipped: 0
[INFO] All coverage checks have been met.
[INFO] BUILD SUCCESS
[INFO] Total time:  06:10 min
```

### Pruebas por clase

| Clase de prueba | Pruebas | Tipo |
|---|---:|---|
| `AuditoriaControllerTest` | 5 | `@WebMvcTest` |
| `AuthControllerTest` | 2 | `@WebMvcTest` |
| `AuthMeEndpointTest` | 3 | `@WebMvcTest` |
| `AuthServiceTest` | 6 | Unitaria (Mockito) |
| `CacheSerializationTest` | 2 | Unitaria |
| `CatalogoControllerTest` | 10 | `@WebMvcTest` |
| `CatalogoSecurityTest` | 8 | `@WebMvcTest` |
| `CsrfDefenseTest` | 4 | Unitaria |
| `DashboardServiceTest` | 7 | Unitaria (Mockito) |
| `GlobalExceptionHandlerTest` | 6 | Unitaria |
| `JwtAuthenticationFilterTest` | 3 | Unitaria (Mockito) |
| `JwtBlacklistServiceTest` | 4 | Unitaria (Mockito) |
| `JwtTokenProviderTest` | 5 | Unitaria |
| `LibroControllerTest` | 3 | `@WebMvcTest` |
| `LibroServiceTest` | 5 | Unitaria (Mockito) |
| `MultaControllerTest` | 4 | `@WebMvcTest` |
| `NotificacionProgramadaServiceTest` | 4 | Unitaria (Mockito) |
| `NotificacionServiceTest` | 6 | Unitaria (Mockito) |
| `PerfilControllerTest` | 3 | `@WebMvcTest` |
| `PrestamoServiceTest` | 15 | Unitaria (Mockito) |
| `QrCodigoServiceTest` | 13 | Unitaria (Mockito) |
| `RegisterValidationTest` | 5 | `@WebMvcTest` |
| `ReporteControllerTest` | 2 | `@WebMvcTest` |
| `ReservaControllerTest` | 5 | `@WebMvcTest` |
| `SancionServiceTest` | 4 | Unitaria (Mockito) |
| `SecurityTest` | 3 | `@SpringBootTest` |
| `SigcbQrApplicationTests` | 1 | Contexto completo |
| `UsuarioServiceTest` | 14 | Unitaria (Mockito) |
| **Total** | **152** | |

---

## 3. Cobertura medida

| Contador | Cubierto | Total | Porcentaje |
|---|---:|---:|---:|
| Instrucciones | 5 111 | 8 165 | **62,60 %** |
| Ramas | 206 | 460 | **44,78 %** |
| **Líneas** | **1 214** | **1 795** | **67,63 %** |
| Métodos | 181 | 364 | 49,73 % |
| Clases analizadas | – | 66 | – |

El umbral configurado en `pom.xml` es **60 % de líneas a nivel de BUNDLE** (subido
desde el 30 % inicial durante la Fase 3), y la regla se cumple (67,63 %). La guía
pide 70 % de líneas y 70 % de ramas en los tres estratos: la cobertura de línea
está a poco más de dos puntos, y la de rama (44,78 %) sigue siendo el punto débil,
aunque «service» ya la ejercita al 58,6 %.

### 3.1 Cómo recalcular estas cifras

```bash
python - <<'EOF'
import csv, collections
rows = list(csv.DictReader(open('docs/mediciones/cobertura/jacoco.csv')))
t = collections.Counter()
for r in rows:
    for k in r:
        if k.endswith(('_MISSED', '_COVERED')):
            t[k] += int(r[k])
for c in ['INSTRUCTION', 'BRANCH', 'LINE', 'COMPLEXITY', 'METHOD']:
    m, cv = t[c + '_MISSED'], t[c + '_COVERED']
    print(f'{c:12s} {cv:5d}/{m+cv:5d} = {100*cv/(m+cv):5.2f} %')
EOF
```

### Cobertura por paquete

| Paquete | Líneas | Cobertura de línea | Cobertura de rama |
|---|---:|---:|---:|
| `com.sigcbqr.config` | 66/66 | 100,0 % | 100,0 % |
| `com.sigcbqr.model.dto.response` | 19/23 | 82,6 % | 100,0 % |
| `com.sigcbqr.security` | 83/123 | 67,5 % | 27,8 % |
| `com.sigcbqr.exception` | 56/83 | 67,5 % | 50,0 % |
| `com.sigcbqr.controller` | 187/394 | 47,5 % | 26,9 % |
| `com.sigcbqr` | 1/3 | 33,3 % | 100,0 % |
| `com.sigcbqr.service` | 801/1032 | 77,6 % | 58,6 % |
| `com.sigcbqr.model.entity` | 1/71 | 1,4 % | 0,0 % |

### 3.2 Historial de esta cifra, y por qué se corrigió dos veces

La primera redacción declaró 36,32 % de instrucciones, 17,75 % de ramas y 43,53 %
de líneas sobre 5 352 instrucciones y 62 clases. Esas cifras correspondían a una
ejecución **anterior** al rediseño del tablero de control (commit `8503a5f`), que
añadió código de producción sin añadir pruebas en la misma proporción. El informe
de JaCoCo que ahora se versiona analiza 65 clases y 7 076 instrucciones, y da
32,17 / 16,31 / 38,85. **La cobertura no subió: bajó, porque creció el
denominador.**

La medición definitiva se obtuvo el 3 de septiembre ejecutando la suite entera
con `make test`, ya autocontenido en contenedor. Son **87 pruebas** (no 55: se
añadieron pruebas de controlador para catálogo, multas y reservas, y las cuatro
de `CsrfDefenseTest` que vigilan la defensa del ADR-0010) y dan
35,01 / 16,31 / 41,28. La cobertura de línea sube casi dos puntos respecto de la
medición intermedia, gracias a esas pruebas nuevas; la de rama **no se mueve en
absoluto**, y ese es el dato que importa.

La medición que se publica con la versión 1.0.0 se tomó el 4 de septiembre, ya
con `RegisterValidationTest` y `AuthMeEndpointTest` incorporadas: son **95
pruebas** y dan 36,20 / 16,84 / 42,51. Frente a la medición anterior, la
cobertura de línea sube 1,23 puntos y la de rama medio punto. Lo que más se
mueve es `com.sigcbqr.security`, de 48,0 % a 66,7 % de línea, porque las dos
pruebas nuevas ejercitan el endpoint `/auth/me` y la validación del registro,
que hasta ahora no tocaba ninguna prueba. La cobertura de rama sigue siendo el
punto débil: 16,84 % está muy lejos del 70 % que pide la guía.

Con la **Fase 3 (cobertura)** se publica una cuarta medición, tomada el 5 de
septiembre: **152 pruebas** —las 114 previas más 7 de `DashboardServiceTest`, 4 de
`NotificacionProgramadaServiceTest`, 13 de `QrCodigoServiceTest` y 14 de
`UsuarioServiceTest`— y dan **62,60 / 44,78 / 67,63**. El salto viene de cubrir
los servicios con mayor déficit: `service` pasa de 32,1 % a 77,6 % de línea y de
13,3 % a 58,6 % de rama; `DashboardService` de 4,3 % a 84,8 %,
`NotificacionProgramadaService` de 0 % a 100 %, `QrCodigoService` de 1,7 % a
98,3 % y `UsuarioService` de 9,7 % a 100 %. El umbral de JaCoCo se sube de 30 % a
60 % en el mismo commit que los tests. Quedan como zonas con déficit
`AuditoriaService` (19 %) y `ReporteService` (16,7 %), que esta fase no tocó, y
la rama de `controller` y `security` (≈27 %).

Se corrige tres veces en el mismo documento porque la cifra publicada debe ser la
que se recalcula del crudo versionado, no la más favorable ni la más cómoda.

## 4. Interpretación

**El reparto importa más que el total.** La cobertura no es uniforme y está
concentrada donde debe estarlo:

- `service` (77,6 % de línea y 58,6 % de rama) es ahora la zona con mayor
  cobertura, y es donde vive la lógica de negocio: después de la Fase 3 los
  cuatro servicios con peor punto de partida quedan por encima del 84 %.
- `security` (67,5 % de línea y 27,8 % de rama) y `controller` (47,5 % de línea
  y 26,9 % de rama) contienen las reglas cuya rotura tendría consecuencias:
  emisión y validación de tokens, revocación, autorización por rol, límites de
  préstamo, borrado lógico. Sus pruebas `@WebMvcTest` ejercitan el camino feliz
  pero pocas bifurcaciones.
- `model.entity` (1,4 %) son entidades JPA de datos con Lombok. La cifra es baja y
  **carece de significado**: probar un *getter* generado no aporta información.
  Contribuye a hundir el total sin que eso indique riesgo.
- `AuditoriaService` (19 %) y `ReporteService` (16,7 %) son los dos servicios
  con lógica sin probar; no se tocaron en esta fase.

**La cobertura de rama, 44,78 %, sigue siendo el dato más débil.** Con las
pruebas de servicio subió de 16,84 % y la rama de `service` ya llega al 58,6 %,
pero la de `controller` y `security` (≈27 %) indica que las pruebas recorren
sobre todo el camino feliz y dejan bifurcaciones —validaciones, comprobaciones
de estado, ramas de error— sin ejercitar.

## 5. Lo que la cobertura no vio

Durante esta entrega se encontraron tres defectos reales en un sistema cuya
suite estaba, en apariencia, en verde. Ninguno lo detectó la cobertura:

1. **El acierto de caché devolvía 500** (ADR-0006). El código de `listar()`
   contaba como cubierto: las pruebas lo llamaban con Mockito, sin pasar por el
   proxy de `@Cacheable`. La línea estaba cubierta; el comportamiento no.
2. **Toda ruta inexistente devolvía 500 en lugar de 404**, filtrando el mensaje
   interno del framework. `GlobalExceptionHandler` figuraba con cobertura; el
   manejador que faltaba no podía aparecer como línea no cubierta, porque no
   existía.
3. **La CSP del frontend bloqueaba sus propias hojas de estilo y el script de
   códigos QR.** Está fuera del alcance de JaCoCo por completo.

La conclusión, y es la que se lleva al informe: **la cobertura mide qué líneas
ejecutan las pruebas, no si el sistema hace lo que debe.** Los tres defectos
aparecieron al ejercitar el sistema en marcha —auditoría OWASP y Lighthouse—, no
al medir cobertura.

### Estado previo de la suite

Conviene registrarlo porque contradice lo declarado en la Tercera Entrega: al
ejecutar la suite completa al inicio de esta entrega, **no estaba en verde**.

```
[ERROR] Tests run: 36, Failures: 0, Errors: 4, Skipped: 0
[INFO] BUILD FAILURE
```

Los cuatro errores eran:

- `AuthControllerTest` (2): el contexto de `@WebMvcTest` no arrancaba desde que
  `AuthController` pasó a depender de `RateLimitService` y no se añadió el doble
  correspondiente (`NoSuchBeanDefinitionException`).
- `JwtAuthenticationFilterTest` (2): `UnnecessaryStubbing`. Las pruebas simulaban
  `extractTokenFromCookie`, pero el filtro llama a `extractTokenFromRequest`
  desde que se admitió también la cabecera `Authorization`. Las pruebas no
  ejercitaban el camino real.

Ambos fallos vienen de cambios en el código de producción cuyas pruebas no se
volvieron a ejecutar. Están corregidos, y por eso la cifra que se declara ahora
—41 pruebas, 0 fallos— se acompaña de la orden que la produce.

## 6. Cómo mejorar la cifra de forma útil

En orden de valor, no de facilidad:

1. **Cubrir los servicios que quedaron sin probar**: `AuditoriaService` (19 %) y
   `ReporteService` (16,7 %), ambos con lógica de negocio real.
2. **Rama en `controller` y `security`** (≈27 %): autorización por rol y forma
   de la respuesta, que es lo que los `@WebMvcTest` actuales no bifurcan.
3. **Pruebas de integración con la caché activa**, que habrían detectado el
   defecto de ADR-0006 antes que un `curl`.
4. **Excluir de la medición `model.entity` y los DTO**, para que el total refleje
   el código con lógica en lugar de diluirse en código generado.
5. Un escalón con 1 y 2 cerrados, subir el umbral de JaCoCo a **70 %** para
   acompañar el objetivo de la guía: el 60 % ya está fijado desde la Fase 3 y se
   cumple (67,63 %).
