# ADR-0002: Separar la API en Spring Boot del frontend en Laravel (patrón BFF)

- **Estado:** Aceptado
- **Fecha:** 2026-08-28 (decisión aplicada en el código desde 2026-06)
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** todos los REQ-F; REQ-NF-002, REQ-NF-006

## Contexto

El repositorio contiene dos aplicaciones de servidor: una API REST en Java 21 con
Spring Boot 3.5 (`backend/`) y una aplicación web en PHP 8.3 con Laravel 13
(`frontend/`). Vista desde fuera, esa combinación parece una incoherencia —así se
señaló en la evaluación de la Tercera Entrega— y por tanto necesita justificarse
o corregirse.

Las restricciones reales del proyecto fueron:

1. **Restricción académica.** La asignatura exige demostrar una API REST con
   seguridad basada en tokens, capas separadas, procedimientos almacenados y
   pruebas automatizadas en el ecosistema Java/Spring. El backend no es una
   elección libre del equipo.
2. **Restricción de competencias.** El equipo tenía experiencia previa en Blade y
   PHP y ninguna en frameworks de frontend con compilación (React, Vue, Angular)
   ni en renderizado del lado del cliente.
3. **Restricción de calendario.** Cuatro entregas en un semestre, con la interfaz
   completa —nueve módulos de administración— exigida desde la segunda.
4. **Restricción de seguridad.** El requisito REQ-NF-002 obliga a que el token de
   sesión no quede expuesto a JavaScript en el navegador.

## Opciones consideradas

1. **Una sola aplicación Spring Boot con plantillas Thymeleaf.** Una sola pila y
   un solo despliegue. Descartada porque anula la competencia del equipo en Blade,
   obliga a aprender Thymeleaf durante el semestre y elimina la frontera HTTP que
   la asignatura pide demostrar: sin cliente externo, la API no se ejercita como
   API.
2. **Una sola aplicación Laravel con toda la lógica en PHP.** La más rápida de
   construir y la más coherente en apariencia. Descartada porque incumple la
   restricción 1: no habría backend Spring Boot que evaluar.
3. **SPA (React o Vue) contra la API de Spring Boot.** Es la arquitectura de
   referencia hoy. Descartada por la restricción 2 —ningún integrante había
   escrito una SPA— y por la 4: una SPA obliga a guardar el token en el navegador,
   donde queda al alcance de cualquier XSS, salvo que se monte un proxy de sesión,
   que es exactamente la opción 4 con más piezas.
4. **Laravel como *Backend for Frontend* (BFF) sobre la API de Spring Boot.**
   Laravel no contiene lógica de negocio: renderiza Blade en el servidor y toda
   petición de datos va a la API por HTTP.

## Decisión

Se adopta la opción 4: **Laravel actúa exclusivamente como BFF de presentación;
Spring Boot es el único dueño de los datos y de las reglas de negocio.**

La frontera se sostiene con tres reglas que se pueden auditar:

1. **Laravel no habla con PostgreSQL.** No hay modelos Eloquent de dominio ni
   migraciones de negocio en `frontend/`. Las únicas tablas que Laravel conoce son
   las suyas de infraestructura (sesiones, caché, colas).
2. **Todo acceso a datos pasa por `App\Services\ApiClient`.** Un único punto de
   salida HTTP; ningún controlador de Laravel arma peticiones por su cuenta.
3. **Ninguna regla de negocio se duplica.** Los límites de préstamo, el cálculo de
   multas y las reglas de autorización viven en el backend. Laravel puede ocultar
   un botón por comodidad, pero la decisión que cuenta es la del servidor: un
   estudiante que llame directamente al endpoint recibe 403 (verificado en
   `docs/mediciones/seguridad/`, bloque API5).

Como efecto secundario, el BFF resuelve la restricción 4: el JWT vive en la sesión
del servidor de Laravel y en una cookie `HttpOnly`. El navegador nunca lo ve. Ver
ADR-0003.

## Consecuencias

### Positivas

- La API queda ejercitada por un cliente real, no por una plantilla del mismo
  proceso: los fallos de contrato aparecen en desarrollo, no en la demostración.
- El token no es accesible desde JavaScript; el impacto de un XSS en la interfaz
  se reduce a la sesión de PHP.
- El equipo entrega nueve módulos de interfaz en el plazo, usando la tecnología
  que ya domina.
- La interfaz puede sustituirse (por una SPA, por una app móvil) sin tocar el
  dominio.

### Negativas

- **Dos entornos de ejecución que mantener**: JVM y PHP-FPM, dos imágenes, dos
  conjuntos de dependencias, dos ciclos de actualización.
- **Un salto de red adicional** en cada petición de usuario (navegador → Laravel →
  API). Añade latencia y crea un modo de fallo que no existiría en una aplicación
  única.
- **Riesgo permanente de fuga de lógica hacia el BFF.** Es la deuda que hay que
  vigilar: cada vez que algo resulta incómodo de pedir a la API, existe la
  tentación de calcularlo en Blade. Por eso las tres reglas de arriba son
  auditables y no meramente aspiracionales.
- **La curva de aprendizaje se paga igual**, solo que en operaciones (Docker,
  redes, variables de entorno) en lugar de en frontend.

## Verificación

- `frontend/app/Models/` contiene únicamente `User.php` (sesión); no hay modelos
  de dominio.
- `frontend/database/migrations/` contiene solo las tres migraciones de
  infraestructura de Laravel.
- Todos los controladores de `frontend/app/Http/Controllers/` reciben `ApiClient`
  por inyección y no usan `DB::` ni Eloquent de dominio.
- La API rechaza con 403 las peticiones que la interfaz ya oculta: bloques API1 y
  API5 de `docs/mediciones/seguridad/OWASP-AUDIT.md`.
