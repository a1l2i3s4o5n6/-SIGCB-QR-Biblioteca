# ADR-0010: Deshabilitar la protección CSRF de Spring y apoyar la defensa en `SameSite=Strict`

- **Estado:** Aceptado
- **Fecha:** 2026-09-03
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-NF-002, REQ-NF-007
- **Relacionado con:** ADR-0003, ADR-0009

## Contexto

`SecurityConfig` deshabilita la protección CSRF de Spring Security:

```java
.csrf(csrf -> csrf.disable())
```

Esta línea llevaba en el código desde el principio **sin ninguna justificación
escrita**. Se registra ahora porque el análisis estático con SpotBugs y
find-sec-bugs la señaló como `SPRING_CSRF_PROTECTION_DISABLED`, y al examinarla
resultó ser una decisión defendible que nadie había tomado explícitamente.

Deshabilitar CSRF es la práctica habitual y correcta en una API sin estado, pero
el razonamiento que la sostiene **depende de dónde viaje la credencial**:

- Si el cliente envía el token en la cabecera `Authorization`, no hay riesgo de
  CSRF. Un sitio de terceros puede provocar que el navegador haga una petición a
  nuestro dominio, pero **no puede hacer que añada esa cabecera**. La petición
  llega sin credencial y se rechaza con `401`.
- Si el cliente autentica **por cookie**, el razonamiento se cae. El navegador
  adjunta la cookie de forma automática en toda petición al dominio, **incluidas
  las originadas por otro sitio**. Ese es exactamente el mecanismo que CSRF
  explota.

Y este sistema **autentica por cookie**: el ADR-0003 decidió guardar el JWT en
una cookie `HttpOnly` en lugar de en `localStorage`, para evitar que un XSS
pudiera leerlo. Esa decisión, correcta frente a XSS, es precisamente la que hace
relevante el riesgo de CSRF.

Escenario concreto del riesgo. Un bibliotecario autenticado visita una página
maliciosa mientras su sesión sigue viva. La página contiene:

```html
<form action="http://servidor-biblioteca/api/categorias/3" method="POST">
  <input type="hidden" name="_method" value="DELETE">
</form>
<script>document.forms[0].submit()</script>
```

Sin ninguna defensa, el navegador enviaría la cookie de sesión y el servidor
ejecutaría el borrado, creyendo que lo pidió el bibliotecario.

## Opciones consideradas

1. **Habilitar la protección CSRF de Spring con repositorio en cookie**
   (`CookieCsrfTokenRepository`). Es la defensa canónica. Obliga a que cada
   petición que modifica estado lleve un token que el atacante no puede leer por
   la política del mismo origen. Coste: el BFF Laravel tendría que leer la cookie
   `XSRF-TOKEN` y reenviarla como cabecera en cada llamada de `ApiClient`, y
   habría que gestionar su renovación. Añade una pieza más a la frontera entre
   las dos pilas, que el ADR-0002 se esfuerza en mantener delgada.
2. **Migrar de cookie a cabecera `Authorization`.** Elimina el riesgo de CSRF de
   raíz, porque la credencial deja de viajar sola. Coste: renuncia a `HttpOnly` y
   devuelve el token a un lugar accesible desde JavaScript, reabriendo el riesgo
   de XSS que el ADR-0003 cerró. Se cambia un riesgo por otro, y el que se
   reabre es peor: un XSS exitoso entrega el token, mientras que un CSRF exitoso
   solo permite acciones concretas.
3. **Deshabilitar CSRF y apoyar la defensa en `SameSite=Strict`.** El navegador
   no adjunta la cookie en peticiones originadas por otro sitio, de modo que el
   escenario anterior falla: la petición llega sin cookie y el servidor responde
   `401`. Coste: la defensa pasa a depender del navegador, no del servidor.
4. **Ambas cosas: CSRF habilitado *y* `SameSite=Strict`.** Defensa en
   profundidad. Coste: el de la opción 1.

## Decisión

Se adopta la **opción 3**: la protección CSRF de Spring queda deshabilitada y la
defensa recae sobre el atributo `SameSite=Strict` de la cookie de sesión, que se
fija en `JwtTokenProvider.createAccessTokenCookie` y en `createLogoutCookie`.

El motivo de no elegir la opción 4, que es la más segura, es honesto y conviene
dejarlo escrito: **el coste de coordinar el token CSRF a través del BFF no se
juzgó proporcionado para un proyecto de asignatura con acervo sintético**. No es
que la opción 4 sea peor; es que se decidió no pagarla. En un sistema con datos
reales la elección debería revisarse.

`SameSite=Strict` se elige sobre `Lax` porque `Lax` sigue permitiendo que la
cookie viaje en navegaciones de nivel superior por `GET`, y este sistema tiene
endpoints `GET` que devuelven datos sensibles (`/api/usuarios`,
`/api/auditoria`). `Strict` cierra también esa vía, a cambio de que un enlace
externo al sistema aterrice siempre en la pantalla de acceso.

## Consecuencias

### Positivas

- El vector CSRF clásico queda bloqueado sin añadir maquinaria a la frontera
  entre Laravel y Spring Boot.
- Se conserva `HttpOnly`, y con él la defensa frente a XSS del ADR-0003.
- `SameSite=Strict` protege **todos** los endpoints por igual, sin depender de
  que alguien recuerde anotarlos. Es una propiedad de la cookie, no de cada ruta.

### Negativas

- **La defensa es una sola línea, y está fuera de nuestro control.** Depende de
  que el navegador implemente `SameSite` correctamente. Un agente antiguo que lo
  ignore deja el sistema expuesto, y el servidor no tiene forma de detectarlo ni
  de negarse a servir a ese cliente.
- **No hay defensa en profundidad.** Si el atributo se pierde en una refactorización,
  no queda nada detrás. Por eso esta decisión viene acompañada de una prueba de
  regresión, sin la cual sería imprudente.
- **Un enlace externo nunca llega autenticado.** Con `Strict`, seguir un enlace
  desde el correo institucional al catálogo aterriza en la pantalla de acceso
  aunque la sesión esté viva. Es una molestia real para el usuario y se acepta
  a cambio de la protección.
- La decisión **no es correcta por defecto en cualquier despliegue**: si en el
  futuro se expusiera la API a clientes de otro origen, habría que revisarla.

## Verificación

La decisión sería inútil sin algo que la vigile. Se verifica en dos niveles:

1. **Prueba automatizada** — `CsrfDefenseTest`, en
   `backend/src/test/java/com/sigcbqr/security/`. Cuatro casos que fallan si
   alguien retira el atributo:
   - la cookie de acceso lleva `SameSite=Strict`;
   - la cookie de acceso lleva `HttpOnly`;
   - la cookie de cierre de sesión lleva también `SameSite=Strict`, porque una
     cookie de borrado sin el atributo permitiría a un tercero forzar el cierre
     de sesión de un usuario;
   - la cookie de cierre de sesión tiene `maxAge = 0`.

2. **Auditoría sobre el sistema en marcha** — `scripts/owasp-audit.sh` comprueba
   el atributo en una respuesta HTTP real, no en el código:

```
  Atributos de la cookie de sesión emitida por POST /api/auth/login:
    set-cookie: access_token=...; Path=/; Max-Age=3600; HttpOnly; SameSite=Strict
  [PASA] cookie access_token con HttpOnly                    esperado=si  obtenido=si
  [PASA] cookie access_token con SameSite=Strict             esperado=si  obtenido=si
```

La prueba cubre el caso de que alguien edite el código; la auditoría cubre el
caso de que la configuración de despliegue altere la cabecera. Ninguna de las
dos sustituye a la otra.

## Notas

Este ADR es consecuencia directa de haber ejecutado análisis estático. El
hallazgo no lo encontró la auditoría dinámica de 51 comprobaciones, porque esa
auditoría **verifica que `SameSite=Strict` esté presente** y por tanto ve el
sistema en su estado correcto; no se pregunta qué pasaría si no lo estuviera.
Es el mismo patrón que dejó pasar los ocho endpoints de escritura del catálogo
sin autorización: un instrumento solo encuentra lo que está diseñado para
buscar.
