# Práctica: Flujo MVC en Spring Boot 3 — SIGCB-QR

**PFC:** SIGCB-QR (Sistema de Gestión de Catálogo Bibliotecario)
**Módulo analizado:** `sigcb-qr-api` (backend Spring Boot 3)
**Endpoint de referencia:** `GET /api/libros` (listado paginado de libros)

> Nota: este PFC no incluye un cliente Angular dentro del repositorio — el frontend
> presente (`SIGCB-QR/`) es Laravel + Alpine.js. Para esta práctica el endpoint se
> probó directamente con Postman.

## Tabla de trazado de flujo

| # | Componente | Clase Java (nombre exacto) | Método (nombre exacto) | Paquete |
|---|---|---|---|---|
| 1 | Cliente | *(N/A — sin cliente Angular en este repo; probado con Postman)* | `GET /api/libros` | — |
| 2 | Tomcat embebido | *(Automático, no editable)* | Gestionado por Spring Boot | Spring interno |
| 3 | DispatcherServlet | `DispatcherServlet` | `doDispatch()` | Spring interno |
| 4 | Filtro JWT | `JwtAuthenticationFilter` | `doFilterInternal()` | `com.sigcbqr.security` |
| 5 | HandlerMapping | `RequestMappingHandlerMapping` | `getHandler()` | Spring interno |
| 6 | Controlador | `LibroController` | `listar(Pageable pageable)` | `com.sigcbqr.controller` |
| 7 | Servicio | `LibroService` | `listar(Pageable pageable)` | `com.sigcbqr.service` |
| 8 | Repositorio | `LibroRepository` | `findByActivoTrue(Pageable pageable)` | `com.sigcbqr.repository` |
| 9 | Serialización JSON | `MappingJackson2HttpMessageConverter` | `write()` | Spring interno |

**Detalle relevante para el paso 4:** `JwtAuthenticationFilter` no lee el token desde
el header `Authorization`, sino desde una **cookie**
(`tokenProvider.extractTokenFromCookie(request)`). Al probar en Postman, el JWT debe
enviarse como cookie, no como Bearer token.

## Parte 3: Preguntas de análisis

**1. En el panel Frames del depurador, cuando la ejecución está dentro del
`JwtAuthenticationFilter`, aparecen clases de Spring y Tomcat encima de la suya.
¿Qué representa esa pila de llamadas? ¿En qué orden se ejecutaron esas clases?**

Representa el orden real de invocación desde que Tomcat acepta la conexión:
`TomcatEmbeddedWebappClassLoader` → `ApplicationFilterChain` (cadena de filtros de
Servlet) → filtros internos de Spring Security → `JwtAuthenticationFilter.doFilterInternal()`.
En `SecurityConfig` este filtro está registrado con
`.addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class)`,
por eso se ejecuta antes del filtro estándar de autenticación por usuario/contraseña.

**2. Cuando el depurador entra en el servicio, encima del método del servicio
aparecen clases como `TransactionInterceptor` y `CglibAopProxy`. ¿Qué está haciendo
Spring en ese momento? ¿Por qué es importante que esas clases se ejecuten antes del
método del servicio?**

En `LibroService` esto solo aparece en métodos con `@Transactional` (`crear()`,
`actualizar()`, `eliminar()`) — no en `listar()`, que solo tiene `@Cacheable`. Spring
envuelve el bean en un proxy CGLIB que abre la transacción JDBC antes de ejecutar el
código propio y hace commit o rollback según el resultado. Es importante porque, por
ejemplo en `crear()`, si falla la vinculación de categoría/editorial/autores a mitad
de camino, el rollback evita que quede un libro guardado sin sus relaciones completas.

**3. Al entrar en el repositorio, el depurador muestra la clase
`SimpleJpaRepository` que nunca fue escrita por el equipo. ¿Cómo existe esa clase en
tiempo de ejecución si no está en el código fuente del PFC? ¿Qué mecanismo de Spring
la genera?**

`LibroRepository` solo declara métodos (`findByActivoTrue`,
`findByTituloContainingIgnoreCase`, etc.) sin implementación. Al arrancar, Spring Data
JPA escanea las interfaces que extienden `JpaRepository` y genera un **proxy
dinámico en tiempo de ejecución** (vía `RepositoryFactoryBeanSupport`) que delega en
`SimpleJpaRepository`. Para los *query methods* derivados del nombre, Spring parsea
el nombre del método y construye la consulta JPQL automáticamente.

**4. Si se envía la solicitud GET sin el encabezado/cookie de autenticación, ¿qué
paso del flujo detiene la solicitud y qué código HTTP devuelve? ¿En qué clase Java
del proyecto ocurre esa decisión?**

Si no llega token válido, `JwtAuthenticationFilter.doFilterInternal()` simplemente no
establece el `SecurityContextHolder` y deja pasar la solicitud sin autenticar. Como
`/api/libros` cae bajo `.requestMatchers("/api/**").authenticated()` en
`SecurityConfig`, Spring Security rechaza la solicitud más adelante en la cadena y
delega en `JwtAuthenticationEntryPoint`, que devuelve **401 Unauthorized**.

**5. Observando el SQL que Hibernate genera para el endpoint GET de listado:
¿Hibernate genera un SELECT con todos los campos de la tabla o solo los que el DTO
necesita? ¿Qué implicaciones de rendimiento tiene esto cuando la tabla tiene 50
columnas?**

`listar()` usa `libroRepository.findByActivoTrue(pageable)`, que devuelve entidades
`Libro` completas (no una proyección DTO) — Hibernate genera un `SELECT` con **todas
las columnas mapeadas en la entidad**. El mapeo a `LibroResponse` ocurre después, en
memoria, dentro de `toResponse()`. Como `Libro` tiene relaciones (`categoria`,
`editorial`, `autores`), esto puede disparar selects adicionales (N+1) si son
`LAZY` y se acceden en `toResponse()`. Con una tabla de 50 columnas el costo sería
mayor ancho de banda y memoria por fila — mitigable con proyecciones JPQL
(`SELECT new ...`) si el rendimiento se vuelve un problema; en este proyecto el
impacto ya se amortigua parcialmente con `@Cacheable` en `listar()`.
