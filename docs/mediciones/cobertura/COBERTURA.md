# Cobertura de pruebas — SIGCB-QR

**Fecha de medición:** 2026-09-04
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
(`SigcbQrApplicationTests`) que arranca la aplicación completa y aplica las diez
migraciones de Flyway.

```bash
docker network create sigcbqr-test-net
docker run -d --name sigcbqr-test-pg --network sigcbqr-test-net \
  -e POSTGRES_DB=sigcbqr_test -e POSTGRES_USER=postgres -e POSTGRES_PASSWORD=test123 \
  postgres:16@sha256:f1c3376c26f2609ab9f29f71f824103fe2fcd8ee0346485cb6122a4f93df6f94
docker run -d --name sigcbqr-test-redis --network sigcbqr-test-net \
  redis:7@sha256:71da9275c5f3fcb97d0fa0c8c5b36cc995327265420f17a04bfd544f458059f7

docker run --rm --network sigcbqr-test-net \
  -v "$PWD/backend":/app -v sigcbqr-m2:/root/.m2 -w /app \
  -e SPRING_DATASOURCE_URL=jdbc:postgresql://sigcbqr-test-pg:5432/sigcbqr_test \
  -e SPRING_DATASOURCE_USERNAME=postgres -e SPRING_DATASOURCE_PASSWORD=test123 \
  -e REDIS_HOST=sigcbqr-test-redis \
  maven:3.9-eclipse-temurin-21 mvn -B clean verify
```

En CI equivale al trabajo `test` de `.github/workflows/ci.yml`, que levanta los
mismos servicios como *service containers*.

---

## 2. Resultado de la ejecución

```
[INFO] Tests run: 95, Failures: 0, Errors: 0, Skipped: 0
[INFO] All coverage checks have been met.
[INFO] BUILD SUCCESS
[INFO] Total time:  03:08 min
```

### Pruebas por clase

| Clase de prueba | Pruebas | Tipo |
|---|---:|---|
| `AuditoriaControllerTest` | 5 | `@WebMvcTest` |
| `AuthControllerTest` | 2 | `@WebMvcTest` |
| `AuthMeEndpointTest` | 3 | `@WebMvcTest` |
| `AuthServiceTest` | 2 | Unitaria (Mockito) |
| `CacheSerializationTest` | 2 | Unitaria |
| `CatalogoControllerTest` | 10 | `@WebMvcTest` |
| `CatalogoSecurityTest` | 8 | `@WebMvcTest` |
| `CsrfDefenseTest` | 4 | Unitaria |
| `GlobalExceptionHandlerTest` | 6 | Unitaria |
| `JwtAuthenticationFilterTest` | 3 | Unitaria (Mockito) |
| `JwtBlacklistServiceTest` | 4 | Unitaria (Mockito) |
| `JwtTokenProviderTest` | 5 | Unitaria |
| `LibroControllerTest` | 3 | `@WebMvcTest` |
| `LibroServiceTest` | 5 | Unitaria (Mockito) |
| `MultaControllerTest` | 4 | `@WebMvcTest` |
| `NotificacionServiceTest` | 4 | Unitaria (Mockito) |
| `PrestamoServiceTest` | 5 | Unitaria (Mockito) |
| `RegisterValidationTest` | 5 | `@WebMvcTest` |
| `ReporteControllerTest` | 2 | `@WebMvcTest` |
| `ReservaControllerTest` | 5 | `@WebMvcTest` |
| `SancionServiceTest` | 4 | Unitaria (Mockito) |
| `SecurityTest` | 3 | `@SpringBootTest` |
| `SigcbQrApplicationTests` | 1 | Contexto completo |
| **Total** | **95** | |

---

## 3. Cobertura medida

| Contador | Cubierto | Total | Porcentaje |
|---|---:|---:|---:|
| Instrucciones | 2 594 | 7 165 | **36,20 %** |
| Ramas | 63 | 374 | **16,84 %** |
| **Líneas** | **678** | **1 595** | **42,51 %** |
| Métodos | 119 | 334 | 35,63 % |
| Clases analizadas | – | 65 | – |

El umbral configurado en `pom.xml` es **30 % de líneas a nivel de BUNDLE**, y la
regla se cumple (42,51 %). El umbral es bajo a propósito y no debe presentarse
como un objetivo alcanzado: se fijó para que la construcción falle si la
cobertura *retrocede*, no como meta de calidad. **Está muy por debajo del 70 %
que pide la guía, en los tres estratos.**

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
| `com.sigcbqr.config` | 66/66 | 100,0 % | 0,0 % |
| `com.sigcbqr.model.dto.response` | 19/23 | 82,6 % | 0,0 % |
| `com.sigcbqr.security` | 82/123 | 66,7 % | 25,0 % |
| `com.sigcbqr.exception` | 54/83 | 65,1 % | 50,0 % |
| `com.sigcbqr.controller` | 166/330 | 50,3 % | 29,3 % |
| `com.sigcbqr` | 1/3 | 33,3 % | 0,0 % |
| `com.sigcbqr.service` | 289/899 | 32,1 % | 13,3 % |
| `com.sigcbqr.model.entity` | 1/68 | 1,5 % | 0,0 % |

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

Se corrige tres veces en el mismo documento porque la cifra publicada debe ser la
que se recalcula del crudo versionado, no la más favorable ni la más cómoda.

## 4. Interpretación

**El reparto importa más que el total.** La cobertura no es uniforme y está
concentrada donde debe estarlo:

- `security` (48,0 %) y `controller` (43,9 %) contienen las reglas cuya rotura
  tendría consecuencias: emisión y validación de tokens, revocación, límites de
  préstamo, borrado lógico.
- `model.entity` (1,5 %) son entidades JPA de datos con Lombok. La cifra es baja y
  **carece de significado**: probar un *getter* generado no aporta información.
  Contribuye a hundir el total sin que eso indique riesgo.
- `service` (32,1 % de línea y 13,3 % de rama) es hoy la zona con déficit real:
  concentra la lógica de negocio y es donde menos bifurcaciones se ejercitan.

**La cobertura de rama, 16,31 %, es el dato preocupante**, no el 41,28 % de líneas.
Significa que las pruebas recorren el camino feliz y dejan casi todas las
bifurcaciones —validaciones, comprobaciones de estado, ramas de error— sin
ejercitar. Es coherente con los tres defectos que la construcción no detectó y
que sí encontró la auditoría manual (§5).

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

1. **Pruebas de rama en `service`**: límite de préstamos alcanzado, ejemplar no
   disponible, devolución fuera de plazo con generación de multa.
2. **Pruebas de los diez controladores sin cubrir**, al menos autorización por rol
   y forma de la respuesta.
3. **Pruebas de integración con la caché activa**, que habrían detectado el
   defecto de ADR-0006 antes que un `curl`.
4. **Excluir de la medición `model.entity` y los DTO**, para que el total refleje
   el código con lógica en lugar de diluirse en código generado.
5. Subir el umbral de JaCoCo por pasos, **después** de 1 y 2, no antes: elevarlo
   ahora sólo incentivaría pruebas que ejecutan líneas sin comprobar nada.
