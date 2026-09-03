#!/bin/bash
# owasp-audit.sh — Auditoría de seguridad reproducible contra el sistema en marcha.
#
# Cubre controles de OWASP API Security Top 10 (2023) y OWASP Top 10 (2021)
# aplicables a esta aplicación. Cada comprobación es una petición curl real
# contra la API y el frontend; el script no simula nada.
#
# Requisitos:
#   - el sistema levantado (make up) y las migraciones aplicadas
#   - curl y python en el PATH
#
# Uso:
#   bash scripts/owasp-audit.sh                    # salida legible
#   bash scripts/owasp-audit.sh > evidencia.txt    # evidencia cruda
#
# Código de salida: 0 si todas las comprobaciones pasan, 1 si alguna falla.
#
# NOTA sobre el límite de tasa: el bloque API4 agota deliberadamente el límite
# de 5 intentos por IP y minuto del endpoint de login. Por eso se ejecuta al
# final y por eso el script inicia sesión una sola vez por rol al principio.

set -uo pipefail

API="${API_URL:-http://localhost:8080}"
WEB="${WEB_URL:-http://localhost:8000}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

PASS=0; FAIL=0; INFO=0

hr() { printf '%s\n' "------------------------------------------------------------"; }
bloque() { echo; hr; echo "$1"; hr; }

# check <descripción> <esperado> <obtenido>
check() {
    local desc="$1" esperado="$2" obtenido="$3"
    if [ "$esperado" = "$obtenido" ]; then
        printf '  [PASA] %-56s esperado=%-24s obtenido=%s\n' "$desc" "$esperado" "$obtenido"
        PASS=$((PASS + 1))
    else
        printf '  [FALLA] %-55s esperado=%-24s obtenido=%s\n' "$desc" "$esperado" "$obtenido"
        FAIL=$((FAIL + 1))
    fi
}

# nota <descripción> <observación>   — se registra, no puntúa
nota() {
    printf '  [NOTA] %-56s %s\n' "$1" "$2"
    INFO=$((INFO + 1))
}

# code <args de curl...> -> imprime el código HTTP
code() { curl -s -o /dev/null -w '%{http_code}' "$@"; }

echo "============================================================"
echo " Auditoría OWASP — SIGCB-QR"
echo " Fecha:    $(date -u '+%Y-%m-%dT%H:%M:%SZ') (UTC)"
echo " API:      $API"
echo " Frontend: $WEB"
echo " curl:     $(curl --version | head -1)"
echo "============================================================"

# ── Disponibilidad previa ───────────────────────────────────────
salud=$(code "$API/actuator/health")
if [ "$salud" != "200" ]; then
    echo "ERROR: la API no responde en $API (actuator/health -> $salud)."
    echo "Levante el sistema con 'make up' antes de auditar."
    exit 1
fi

# ── Sesiones de trabajo (un login por rol) ──────────────────────
# login <email> <password> <archivo-cookies>
# Guarda además las cabeceras en <archivo-cookies>.head y el cuerpo en
# <archivo-cookies>.body, para que los bloques siguientes puedan inspeccionarlos
# sin gastar otra unidad del límite de tasa que el bloque API4 debe medir.
login() {
    curl -s -D "$3.head" -o "$3.body" -c "$3" -X POST "$API/api/auth/login" \
         -H 'Content-Type: application/json' \
         -d "{\"email\":\"$1\",\"password\":\"$2\"}"
}
# Cada archivo es una SESIÓN distinta, con su propio token. El bloque API2
# revoca un token, y la revocación es por `jti` (ADR-0009): copiar el archivo de
# cookies en lugar de iniciar sesión de nuevo invalidaría también la sesión
# original, y los bloques posteriores medirían 401 donde deben medir 403.
ADMIN="$TMP/admin.txt"; EST="$TMP/estudiante.txt"; LOGOUT="$TMP/logout.txt"

# Las credenciales de la sonda NO se versionan. Se toman del entorno y, si no
# estan definidas, el script se detiene: es preferible no auditar a auditar con
# una contrasena escrita en el repositorio. Para el entorno de demostracion
# local, exporta las dos variables antes de invocar el script.
: "${SIGCB_ADMIN_PASSWORD:?Define SIGCB_ADMIN_PASSWORD antes de ejecutar la auditoria}"
: "${SIGCB_ESTUDIANTE_PASSWORD:?Define SIGCB_ESTUDIANTE_PASSWORD antes de ejecutar la auditoria}"

login admin@biblioteca.com          "$SIGCB_ADMIN_PASSWORD"       "$ADMIN"
login carlos.garcia@estudiante.com  "$SIGCB_ESTUDIANTE_PASSWORD"  "$EST"
login carlos.garcia@estudiante.com  "$SIGCB_ESTUDIANTE_PASSWORD"  "$LOGOUT"
LOGINS_CONSUMIDOS=3

# ════════════════════════════════════════════════════════════════
bloque "API1:2023 — Autorización a nivel de objeto (BOLA)"

check "GET /api/auditoria como estudiante" 403 "$(code -b "$EST" "$API/api/auditoria")"
check "GET /api/usuarios como estudiante"  403 "$(code -b "$EST" "$API/api/usuarios")"
check "GET /api/usuarios/1 como estudiante" 403 "$(code -b "$EST" "$API/api/usuarios/1")"
check "GET /api/usuarios como admin"       200 "$(code -b "$ADMIN" "$API/api/usuarios")"

# ════════════════════════════════════════════════════════════════
bloque "API2:2023 — Autenticación rota"

check "GET /api/libros sin token"                401 "$(code "$API/api/libros")"
check "GET /api/libros con token con formato inválido" 401 "$(code -H 'Cookie: access_token=abc.def.ghi' "$API/api/libros")"
check "GET /api/libros con firma manipulada"     401 "$(code -H 'Cookie: access_token=eyJhbGciOiJIUzM4NCJ9.eyJzdWIiOiIxIiwicm9sIjoiQURNSU4ifQ.firma_falsa' "$API/api/libros")"
check "GET /api/libros con Bearer vacío"         401 "$(code -H 'Authorization: Bearer ' "$API/api/libros")"

echo
echo "  Atributos de la cookie de sesión emitida por POST /api/auth/login:"
cookie_hdr=$(tr -d '\r' < "$ADMIN.head" | grep -i '^set-cookie: access_token')
echo "    $cookie_hdr"
case "$cookie_hdr" in *HttpOnly*)  check "cookie access_token con HttpOnly"        si si ;;
                     *)            check "cookie access_token con HttpOnly"        si no ;; esac
case "$cookie_hdr" in *SameSite=Strict*) check "cookie access_token con SameSite=Strict" si si ;;
                     *)                  check "cookie access_token con SameSite=Strict" si no ;; esac
case "$cookie_hdr" in *Secure*) nota "cookie access_token con Secure" "presente" ;;
                     *)         nota "cookie access_token con Secure" "ausente (esperado en HTTP local; app.jwt.secure-cookie=true en el perfil prod)" ;; esac

echo
echo "  Revocación efectiva del token en el cierre de sesión (ADR-0009):"
check "antes del logout, la cookie sirve"  200 "$(code -b "$LOGOUT" "$API/api/libros")"
check "POST /api/auth/logout"              200 "$(code -b "$LOGOUT" -X POST "$API/api/auth/logout")"
check "tras el logout, la misma cookie ya no sirve" 401 "$(code -b "$LOGOUT" "$API/api/libros")"

# ════════════════════════════════════════════════════════════════
bloque "API3:2023 — Exposición excesiva de propiedades"

cuerpo_login=$(cat "$ADMIN.body")
case "$cuerpo_login" in *'"password"'*) check "la respuesta de login no expone password" no si ;;
                        *)              check "la respuesta de login no expone password" no no ;; esac

cuerpo_usuarios=$(curl -s -b "$ADMIN" "$API/api/usuarios?page=0&size=5")
case "$cuerpo_usuarios" in *'$2a$'*|*'$2b$'*) check "el listado de usuarios no expone hashes bcrypt" no si ;;
                           *)                 check "el listado de usuarios no expone hashes bcrypt" no no ;; esac

# ════════════════════════════════════════════════════════════════
bloque "API5:2023 — Autorización a nivel de función (BFLA)"

# Cuerpo válido a propósito: con un cuerpo inválido la validación responde 400
# antes de que actúe @PreAuthorize y la comprobación no probaría nada.
usuario_valido='{"nombre":"Sonda BFLA","email":"sonda.bfla@example.invalid","password":"Sonda12345","telefono":"0999999999","rolId":1}'
check "POST /api/usuarios como estudiante (cuerpo válido)" 403 \
      "$(code -b "$EST" -X POST "$API/api/usuarios" -H 'Content-Type: application/json' -d "$usuario_valido")"
check "GET /api/reportes/prestamos-diarios como estudiante" 403 "$(code -b "$EST" "$API/api/reportes/prestamos-diarios")"
check "GET /api/configuracion como estudiante"              403 "$(code -b "$EST" "$API/api/configuracion")"

# El control es efectivo solo si además no se creó nada.
creado=$(curl -s -b "$ADMIN" "$API/api/usuarios?page=0&size=100" | grep -c 'sonda.bfla@example.invalid' || true)
check "el intento BFLA no creó ningún usuario" 0 "$creado"

# ════════════════════════════════════════════════════════════════
bloque "API8:2023 / A05:2021 — Configuración incorrecta"

echo "  Cabeceras de seguridad de la API (GET /api/libros, sin autenticar):"
cab_api=$(curl -s -D - -o /dev/null "$API/api/libros" | tr -d '\r')
echo "$cab_api" | grep -iE '^(x-content-type-options|x-frame-options|cache-control|content-type):' | sed 's/^/    /'
case "$cab_api" in *"X-Content-Type-Options: nosniff"*) check "API: X-Content-Type-Options nosniff" si si ;;
                   *)                                   check "API: X-Content-Type-Options nosniff" si no ;; esac
case "$cab_api" in *"X-Frame-Options: DENY"*) check "API: X-Frame-Options DENY" si si ;;
                   *)                          check "API: X-Frame-Options DENY" si no ;; esac
case "$cab_api" in *"application/problem+json"*) check "API: errores en formato RFC 7807" si si ;;
                   *)                             check "API: errores en formato RFC 7807" si no ;; esac

echo
echo "  Cabeceras de seguridad del frontend (GET /login):"
cab_web=$(curl -s -D - -o /dev/null "$WEB/login" | tr -d '\r')
echo "$cab_web" | grep -iE '^(content-security-policy|x-content-type-options|referrer-policy|x-frame-options):' | cut -c1-150 | sed 's/^/    /'
case "$cab_web" in *"Content-Security-Policy:"*) check "Frontend: Content-Security-Policy" si si ;;
                   *)                             check "Frontend: Content-Security-Policy" si no ;; esac
case "$cab_web" in *"Referrer-Policy:"*) check "Frontend: Referrer-Policy" si si ;;
                   *)                     check "Frontend: Referrer-Policy" si no ;; esac
case "$cab_web" in *"X-Content-Type-Options: nosniff"*) check "Frontend: X-Content-Type-Options nosniff" si si ;;
                   *)                                    check "Frontend: X-Content-Type-Options nosniff" si no ;; esac

echo
echo "  CORS (ADR-0002): solo se admiten los orígenes declarados."
check "preflight desde http://evil.example"     403 \
      "$(code -X OPTIONS "$API/api/libros" -H 'Origin: http://evil.example' -H 'Access-Control-Request-Method: GET')"
check "preflight desde el origen del frontend"  200 \
      "$(code -X OPTIONS "$API/api/libros" -H "Origin: $WEB" -H 'Access-Control-Request-Method: GET')"

echo
echo "  Superficie expuesta por actuator (solo health e info deben estar activos):"
check "GET /actuator/health"   200 "$(code "$API/actuator/health")"
check "GET /actuator/env"      404 "$(code "$API/actuator/env")"
check "GET /actuator/beans"    404 "$(code "$API/actuator/beans")"
check "GET /actuator/heapdump" 404 "$(code "$API/actuator/heapdump")"

echo
echo "  Semántica de los errores: una ruta inexistente debe ser 404, no 500."
echo "  Un 500 aquí impide distinguir un fallo real de una URL mal escrita y,"
echo "  con el manejador genérico anterior, filtraba el mensaje del framework."
check "GET /api/ruta-que-no-existe"     404 "$(code -b "$ADMIN" "$API/api/ruta-que-no-existe")"
check "GET /ruta/fuera/del/api"         404 "$(code "$API/ruta/fuera/del/api")"
check "DELETE /api/auth/login (método no permitido)" 405 "$(code -X DELETE "$API/api/auth/login")"

detalle_404=$(curl -s -b "$ADMIN" "$API/api/ruta-que-no-existe")
echo "    cuerpo: $detalle_404"
case "$detalle_404" in *"static resource"*) check "el 404 no filtra el mensaje interno de Spring" no si ;;
                       *)                    check "el 404 no filtra el mensaje interno de Spring" no no ;; esac

# ════════════════════════════════════════════════════════════════
bloque "A03:2021 — Inyección"

# Las cargas deben tratarse como texto de búsqueda, no como SQL.
# Un 500 aquí indicaría que la carga llegó al motor y lo rompió.
for carga in "' OR 1=1--" \
             "'; DROP TABLE libros;--" \
             "1' UNION SELECT NULL,NULL,NULL--" \
             "admin'--" \
             "%' OR '1'='1"; do
    check "buscar q=[$carga] no provoca 500" 200 \
          "$(code -b "$ADMIN" --get --data-urlencode "q=$carga" "$API/api/libros/buscar")"
done

# La tabla debe seguir existiendo después de las cargas destructivas.
check "el catálogo sigue operativo tras las cargas" 200 "$(code -b "$ADMIN" "$API/api/libros")"

# ════════════════════════════════════════════════════════════════
bloque "API4:2023 — Consumo de recursos sin restricción"

echo "  Límite de tamaño de página (evita que un cliente pida el catálogo entero):"
tam=$(curl -s -b "$ADMIN" "$API/api/libros?page=0&size=5000" \
      | python -c "import sys,json;d=json.load(sys.stdin);print(d.get('size'))" 2>/dev/null || echo "?")
nota "GET /api/libros?size=5000 devuelve size" "$tam"

echo
echo "  Límite de tasa en el login (5 intentos por IP y minuto)."
echo "  Esta auditoría ya consumió $LOGINS_CONSUMIDOS inicios de sesión válidos, así que"
echo "  el corte debe aparecer en los primeros intentos de esta ráfaga."
vio429=no; primer429=""
for i in $(seq 1 8); do
    c=$(code -X POST "$API/api/auth/login" -H 'Content-Type: application/json' \
        -d '{"email":"fuerza.bruta@example.invalid","password":"incorrecta"}')
    printf '    intento %d -> %s\n' "$i" "$c"
    if [ "$c" = "429" ] && [ "$vio429" = "no" ]; then vio429=si; primer429=$i; fi
done
check "el login limita la tasa (aparece 429)" si "$vio429"
[ -n "$primer429" ] && nota "primer 429 en el intento" "$primer429 de la ráfaga (tras $LOGINS_CONSUMIDOS logins previos)"

# ════════════════════════════════════════════════════════════════
bloque "Resumen"

TOTAL=$((PASS + FAIL))
echo "  Comprobaciones ejecutadas : $TOTAL"
echo "  Superadas                 : $PASS"
echo "  Fallidas                  : $FAIL"
echo "  Observaciones sin puntuar : $INFO"
echo

if [ "$FAIL" -gt 0 ]; then
    echo "RESULTADO: $FAIL comprobación(es) fallida(s)."
    exit 1
fi
echo "RESULTADO: todas las comprobaciones superadas."
