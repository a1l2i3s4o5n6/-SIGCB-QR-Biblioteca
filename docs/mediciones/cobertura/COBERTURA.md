# Cobertura de pruebas — SIGCB-QR

**Fecha de medición:** 2026-09-01
**Herramienta:** JaCoCo 0.8.12 (`jacoco-maven-plugin`)
**Requisito asociado:** REQ-NF-005
**Informe HTML completo:** `backend/target/site/jacoco/index.html` (se genera con
`mvn verify`; no se versiona)

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
[INFO] Tests run: 41, Failures: 0, Errors: 0, Skipped: 0
[INFO] All coverage checks have been met.
[INFO] BUILD SUCCESS
[INFO] Total time:  03:20 min
```

### Pruebas por clase

| Clase de prueba | Pruebas | Tipo |
|---|---:|---|
| `CacheSerializationTest` | 2 | Unitaria |
| `AuthControllerTest` | 2 | `@WebMvcTest` |
| `LibroControllerTest` | 3 | `@WebMvcTest` |
| `ReporteControllerTest` | 2 | `@WebMvcTest` |
| `GlobalExceptionHandlerTest` | 6 | Unitaria |
| `JwtAuthenticationFilterTest` | 3 | Unitaria (Mockito) |
| `JwtBlacklistServiceTest` | 4 | Unitaria (Mockito) |
| `JwtTokenProviderTest` | 5 | Unitaria |
| `SecurityTest` | 3 | `@SpringBootTest` |
| `AuthServiceTest` | 2 | Unitaria (Mockito) |
| `LibroServiceTest` | 5 | Unitaria (Mockito) |
| `PrestamoServiceTest` | 3 | Unitaria (Mockito) |
| `SigcbQrApplicationTests` | 1 | Contexto completo |
| **Total** | **41** | |

---

## 3. Cobertura medida

| Contador | Cubierto | Total | Porcentaje |
|---|---:|---:|---:|
| Instrucciones | 1 530 | 4 728 | **32,36 %** |
| Ramas | 34 | 254 | **13,39 %** |
| **Líneas** | **429** | **1 099** | **39,04 %** |
| Complejidad ciclomática | 92 | 391 | 23,53 % |
| Métodos | 83 | 264 | 31,44 % |
| Clases analizadas | — | 58 | — |

El umbral configurado en `pom.xml` es **30 % de líneas a nivel de BUNDLE**, y la
regla se cumple (39,04 %). El umbral es bajo a propósito y no debe presentarse
como un objetivo alcanzado: se fijó para que la construcción falle si la
cobertura *retrocede*, no como meta de calidad.

### Cobertura por paquete

| Paquete | Líneas | Cobertura de línea | Cobertura de rama |
|---|---:|---:|---:|
| `com.sigcbqr.config` | 58/58 | 100,0 % | 0,0 % |
| `com.sigcbqr.model.dto.response` | 14/23 | 60,9 % | 0,0 % |
| `com.sigcbqr.exception` | 38/77 | 49,4 % | 0,0 % |
| `com.sigcbqr.security` | 56/123 | 45,5 % | 25,0 % |
| `com.sigcbqr.service` | 187/454 | 41,2 % | 18,3 % |
| `com.sigcbqr` | 1/3 | 33,3 % | 0,0 % |
| `com.sigcbqr.controller` | 74/293 | 25,3 % | 10,3 % |
| `com.sigcbqr.model.entity` | 1/68 | 1,5 % | 0,0 % |

---

## 4. Interpretación

**El reparto importa más que el total.** La cobertura no es uniforme y está
concentrada donde debe estarlo:

- `security` (45,5 %) y `service` (41,2 %) contienen las reglas cuya rotura
  tendría consecuencias: emisión y validación de tokens, revocación, límites de
  préstamo, borrado lógico.
- `model.entity` (1,5 %) son entidades JPA de datos con Lombok. La cifra es baja y
  **carece de significado**: probar un *getter* generado no aporta información.
  Contribuye a hundir el total sin que eso indique riesgo.
- `controller` (25,3 %) es la zona con déficit real: solo tres de los trece
  controladores tienen pruebas.

**La cobertura de rama, 13,39 %, es el dato preocupante**, no el 39 % de líneas.
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
