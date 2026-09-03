# Análisis estático de seguridad — SpotBugs + find-sec-bugs

**Fecha:** 2026-09-03
**Herramienta:** SpotBugs 4.8.6.6 con el complemento find-sec-bugs 1.13.0
**Ámbito:** `backend/src/main/java` (las clases de prueba quedan fuera)
**Configuración:** `effort=Max`, `threshold=Low` — el ajuste más exhaustivo
**Crudo:** `spotbugsXml.xml`, en este mismo directorio

Reproducción:

```bash
make test          # lo ejecuta en la fase verify
# o aislado:
cd backend && ./mvnw spotbugs:check
```

---

## 1. Resumen

**191 hallazgos.** Su reparto por categoría es lo primero que hay que entender,
porque el número desnudo induce a error:

| Categoría | Hallazgos | Qué son |
|---|---:|---|
| `MALICIOUS_CODE` | 108 | `EI_EXPOSE_REP`: getters y setters de entidades JPA que devuelven o almacenan objetos mutables |
| `SECURITY` | 72 | 71 son `SPRING_ENDPOINT`; **1 es un hallazgo real** |
| `I18N` | 5 | `toUpperCase()`/`toLowerCase()` sin `Locale` |
| `STYLE` | 3 | Campo no leído, flujo de control inútil, captura de `Exception` |
| `BAD_PRACTICE` | 2 | Excepción en constructor; excepción posiblemente ignorada |
| `PERFORMANCE` | 1 | Almacenamiento muerto en variable local |

**No se declara «191 problemas de seguridad».** De los 72 de categoría
`SECURITY`, **71 son `SPRING_ENDPOINT`**, que no es un defecto: find-sec-bugs
marca cada endpoint de Spring como punto que un auditor debe revisar. Con 71
endpoints en el sistema, aparecen 71 avisos. Contarlos como vulnerabilidades
sería inflar la cifra.

## 2. El único hallazgo de seguridad real

### `SPRING_CSRF_PROTECTION_DISABLED` — protección CSRF deshabilitada

**Dónde:** [`SecurityConfig.java:36`](../../../../backend/src/main/java/com/sigcbqr/config/SecurityConfig.java#L36)

```java
.csrf(csrf -> csrf.disable())
```

**Por qué importa.** Este sistema **sí guarda el token de sesión en una
cookie** (`access_token`, con `HttpOnly`). El navegador la envía
automáticamente en toda petición al dominio, incluidas las que origine un sitio
de terceros. Ese es justamente el escenario que CSRF explota. Deshabilitar la
protección CSRF es seguro en una API que autentica por cabecera `Authorization`,
porque esa cabecera no viaja sola; **no lo es, sin más, en una que autentica por
cookie**.

**Mitigación presente.** La cookie se emite con `SameSite=Strict`
([`JwtTokenProvider.java:125`](../../../../backend/src/main/java/com/sigcbqr/security/JwtTokenProvider.java#L125)),
verificado sobre una respuesta real en la auditoría OWASP. `SameSite=Strict`
impide que el navegador adjunte la cookie en peticiones originadas por otro
sitio, lo que bloquea el vector CSRF clásico.

**Valoración.** El riesgo está mitigado, pero descansa **en una sola línea de
defensa** y en el comportamiento del navegador. Es una decisión defendible y no
un descuido, pero:

1. **No estaba documentada** en ningún sitio hasta este análisis. Una decisión de
   seguridad que nadie escribió no es una decisión: es una casualidad afortunada.
2. Un navegador antiguo que ignore `SameSite` deja el sistema expuesto, y el
   servidor no tiene forma de detectarlo. Esto no tiene arreglo desde el código:
   es el precio de apoyarse en una defensa que ejecuta el cliente.
3. **No había ninguna prueba** que fallara si alguien quitaba el
   `SameSite=Strict`. Toda la seguridad dependía de que nadie tocara esa línea.

**Acción tomada.** Las dos cosas que faltaban están hechas:

1. **[ADR-0010](../../../adr/0010-csrf-deshabilitado-y-samesite-strict.md)**
   registra la decisión con sus cuatro alternativas, la razón de no elegir la
   más segura —el coste de coordinar el token CSRF a través del BFF no se juzgó
   proporcionado— y sus consecuencias negativas.
2. **`CsrfDefenseTest`** vigila el atributo en las dos cookies, la de acceso y
   la de cierre de sesión. Se comprobó que la prueba **falla de verdad**: al
   retirar temporalmente `setAttribute("SameSite", "Strict")` del código, dos de
   los cuatro casos fallaron. Una prueba que no se ha visto fallar no prueba
   nada.

## 3. Los 108 hallazgos `EI_EXPOSE_REP`

Son entidades JPA con Lombok: `Usuario.getRol()` devuelve la referencia a `Rol`
en lugar de una copia, y análogos. Es el patrón normal de un modelo de dominio
con JPA, donde las asociaciones **deben** ser referencias vivas para que el ORM
gestione la persistencia. Copiarlas defensivamente rompería el mapeo.

Se dejan sin corregir a propósito. Lo que corresponde es filtrarlos en la
configuración de SpotBugs para que no oculten hallazgos reales, no cambiar el
modelo de datos.

## 4. Los hallazgos menores que sí conviene arreglar

| Hallazgo | Dónde | Coste |
|---|---|---|
| `DLS_DEAD_LOCAL_STORE`: variable `token` asignada y no usada | `AuthService.java:98` | Trivial |
| `URF_UNREAD_FIELD`: `reservaRepository` inyectado y nunca leído | `ReporteService.java:29` | Trivial |
| `UCF_USELESS_CONTROL_FLOW` | `PrestamoService.java:155` | Trivial |
| `DM_CONVERT_CASE` ×5: `toUpperCase()` sin `Locale` | Varios servicios | Trivial |
| `DE_MIGHT_IGNORE` / `REC_CATCH_EXCEPTION`: se captura `Exception` y puede tragarse | `AuditoriaService.java:46` | Merece revisión: es el servicio de auditoría, y una excepción tragada ahí significa un evento que no se registra |

El último no es cosmético. Si `AuditoriaService.registrar()` se traga una
excepción, se pierde silenciosamente un registro de auditoría, que es
precisamente lo que ese servicio existe para no permitir.

## 5. Estado del criterio

La guía exige análisis estático con SpotBugs y find-sec-bugs. **Está ejecutado y
su informe versionado**, que es lo que faltaba por completo en la entrega
anterior.

Lo que **no** está hecho: el triaje no se ha traducido en correcciones. La
configuración lleva `failOnError=false` a propósito, para obtener el informe sin
romper la construcción en la primera pasada. Ponerlo en `true`, tras filtrar los
`EI_EXPOSE_REP`, es el paso siguiente y queda pendiente.
