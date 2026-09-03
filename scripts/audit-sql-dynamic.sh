#!/bin/bash
# audit-sql-dynamic.sh — Audita el acceso a datos en busca de SQL construido
# dinámicamente con entrada de usuario.
#
# Motivo: la guía exige demostrar que no se concatena entrada de usuario en
# sentencias SQL. Afirmarlo en prosa no es evidencia; este script lo comprueba
# sobre el árbol y falla si encuentra un patrón peligroso, de modo que la
# afirmación queda respaldada por una comprobación que cualquiera puede repetir.
#
# Comprobaciones:
#   C1. Concatenación con '+' dentro de @Query / createQuery / createNativeQuery.
#   C2. Uso de createNativeQuery / createQuery con String interpolado.
#   C3. Interpolación de cadenas de Java (String.format, .concat, plantillas)
#       en la vecindad de una palabra clave SQL.
#   C4. JdbcTemplate/Statement en lugar de PreparedStatement.
#   C5. Concatenación SQL en el frontend PHP (query builder crudo, DB::raw).
#   C6. Inventario: parámetros con nombre (:param) y posicionales (?n) en @Query,
#       que es la forma segura y la que debe predominar.
#
# Salida: informe legible + código de salida 0 (limpio) o 1 (hallazgos).
# Uso: bash scripts/audit-sql-dynamic.sh [--json]

set -uo pipefail

BACKEND_SRC="backend/src/main/java"
FRONTEND_SRC="frontend/app"
HALLAZGOS=0
JSON_OUT=0
[ "${1:-}" = "--json" ] && JSON_OUT=1

if [ ! -d "$BACKEND_SRC" ]; then
    echo "ERROR: no se encuentra $BACKEND_SRC (ejecuta desde la raíz del repositorio)"
    exit 1
fi

declare -a DETALLES

reportar() {
    # $1 = id de comprobación, $2 = descripción, $3 = fichero:línea
    HALLAZGOS=$((HALLAZGOS + 1))
    DETALLES+=("$1|$2|$3")
    echo "  HALLAZGO [$1] $2"
    echo "            $3"
}

echo "============================================================"
echo " Auditoría de SQL dinámico — SIGCB-QR"
echo " Fecha: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
echo " Árbol: $(git rev-parse --short HEAD 2>/dev/null || echo 'sin git')"
echo "============================================================"
echo ""

# ---------------------------------------------------------------------------
# C0 — autotest del instrumento
#
# Antes de afirmar "cero hallazgos" hay que demostrar que el detector detecta
# algo. scripts/autotest-sql/ contiene un fichero con tres inyecciones reales y
# otro con la forma segura. Si el detector no encuentra exactamente 3 y 0, el
# instrumento está roto y el resultado de la auditoría no vale nada.
# ---------------------------------------------------------------------------
echo "C0. Autotest del instrumento"
AT_DIR="scripts/autotest-sql"
if [ -d "$AT_DIR" ]; then
    TMP_AT=$(mktemp -d)
    cp "$AT_DIR/CasoInseguro.java.txt" "$TMP_AT/CasoInseguro.java"
    N_MALO=$(python scripts/_sql_dynamic_check.py "$TMP_AT" | wc -l)
    rm -f "$TMP_AT/CasoInseguro.java"
    cp "$AT_DIR/CasoSeguro.java.txt" "$TMP_AT/CasoSeguro.java"
    N_BUENO=$(python scripts/_sql_dynamic_check.py "$TMP_AT" | wc -l)
    rm -rf "$TMP_AT"
    if [ "$N_MALO" -eq 3 ] && [ "$N_BUENO" -eq 0 ]; then
        echo "  OK — detecta 3/3 inyecciones y 0 falsos positivos."
    else
        echo "  ERROR: el autotest falla (esperado 3 y 0; obtenido $N_MALO y $N_BUENO)."
        echo "  La auditoría se aborta: un detector sin validar no prueba nada."
        exit 2
    fi
else
    echo "  ERROR: falta $AT_DIR; no se puede validar el instrumento."
    exit 2
fi
echo ""

# ---------------------------------------------------------------------------
# C1 y C2 — construcción dinámica de consultas
#
# Se delega en _sql_dynamic_check.py, que extrae el argumento completo de cada
# @Query/createQuery/createNativeQuery, borra los literales de cadena y mira lo
# que sobra. Un grep de '" +' marcaría como peligrosas las nueve consultas JPQL
# que este proyecto parte en varias líneas, y las nueve son constantes.
# ---------------------------------------------------------------------------
echo "C1/C2. Construcción dinámica de consultas JPA/JPQL/nativas"
ANTES=$HALLAZGOS
while IFS= read -r hit; do
    [ -z "$hit" ] && continue
    reportar "C1" "Consulta construida con valor no literal" "$hit"
done < <(python scripts/_sql_dynamic_check.py "$BACKEND_SRC" 2>/dev/null)
[ $HALLAZGOS -eq $ANTES ] && echo "  OK — todas las consultas son literales constantes."
echo ""

# ---------------------------------------------------------------------------
# C3 — interpolación de cadenas junto a palabras clave SQL
# ---------------------------------------------------------------------------
echo "C3. Interpolación de cadenas junto a palabras clave SQL"
ANTES=$HALLAZGOS
while IFS= read -r hit; do
    [ -z "$hit" ] && continue
    reportar "C3" "String.format/concat junto a SQL" "$hit"
done < <(grep -rniE '(String\.format|\.concat\()[^;]*(SELECT|INSERT|UPDATE|DELETE|WHERE|FROM)\b' \
            --include="*.java" "$BACKEND_SRC" 2>/dev/null)
[ $HALLAZGOS -eq $ANTES ] && echo "  OK — sin interpolación junto a SQL."
echo ""

# ---------------------------------------------------------------------------
# C4 — Statement en lugar de PreparedStatement
# ---------------------------------------------------------------------------
echo "C4. Statement / JdbcTemplate con SQL construido"
ANTES=$HALLAZGOS
while IFS= read -r hit; do
    [ -z "$hit" ] && continue
    reportar "C4" "Statement (no parametrizado) o JdbcTemplate con '+'" "$hit"
done < <(grep -rnE '(createStatement\s*\(|jdbcTemplate\.[a-zA-Z]+\([^)]*"\s*\+)' \
            --include="*.java" "$BACKEND_SRC" 2>/dev/null)
[ $HALLAZGOS -eq $ANTES ] && echo "  OK — no se usa Statement ni JdbcTemplate concatenado."
echo ""

# ---------------------------------------------------------------------------
# C5 — frontend PHP
# ---------------------------------------------------------------------------
echo "C5. SQL crudo en el frontend Laravel"
ANTES=$HALLAZGOS
if [ -d "$FRONTEND_SRC" ]; then
    while IFS= read -r hit; do
        [ -z "$hit" ] && continue
        reportar "C5" "DB::raw / DB::select con interpolación" "$hit"
    done < <(grep -rnE 'DB::(raw|select|statement|unprepared)\s*\(' \
                --include="*.php" "$FRONTEND_SRC" 2>/dev/null)
    [ $HALLAZGOS -eq $ANTES ] && echo "  OK — el frontend no ejecuta SQL (habla con la API)."
else
    echo "  N/A — no existe $FRONTEND_SRC"
fi
echo ""

# ---------------------------------------------------------------------------
# C6 — inventario de la forma segura
# ---------------------------------------------------------------------------
echo "C6. Inventario del acceso a datos (forma segura)"
N_QUERY=$(grep -rc '@Query' --include="*.java" "$BACKEND_SRC" 2>/dev/null | awk -F: '{s+=$2} END {print s+0}')
N_NAMED=$(grep -rhoE ':[a-zA-Z][a-zA-Z0-9_]*' --include="*.java" "$BACKEND_SRC" 2>/dev/null | wc -l)
N_PROC=$(grep -rc '@Procedure' --include="*.java" "$BACKEND_SRC" 2>/dev/null | awk -F: '{s+=$2} END {print s+0}')
N_PARAM=$(grep -rc '@Param' --include="*.java" "$BACKEND_SRC" 2>/dev/null | awk -F: '{s+=$2} END {print s+0}')
echo "  @Query declaradas ................ $N_QUERY"
echo "  @Param (vinculación por nombre) .. $N_PARAM"
echo "  @Procedure (procedimientos) ...... $N_PROC"
echo "  Marcadores ':nombre' en fuentes .. $N_NAMED"
echo ""

# ---------------------------------------------------------------------------
# Veredicto
# ---------------------------------------------------------------------------
echo "============================================================"
if [ $HALLAZGOS -eq 0 ]; then
    echo " RESULTADO: LIMPIO — 0 hallazgos de SQL dinámico."
    echo " Todo el acceso a datos usa vinculación de parámetros"
    echo " (@Param / :nombre) o procedimientos almacenados (@Procedure)."
else
    echo " RESULTADO: $HALLAZGOS HALLAZGO(S) — revisar antes de publicar."
fi
echo "============================================================"

if [ $JSON_OUT -eq 1 ]; then
    {
        echo "{"
        echo "  \"fecha\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\","
        echo "  \"commit\": \"$(git rev-parse HEAD 2>/dev/null || echo null)\","
        echo "  \"hallazgos\": $HALLAZGOS,"
        echo "  \"query_declaradas\": $N_QUERY,"
        echo "  \"param_vinculados\": $N_PARAM,"
        echo "  \"procedimientos\": $N_PROC,"
        echo "  \"detalle\": ["
        for i in "${!DETALLES[@]}"; do
            IFS='|' read -r id desc loc <<< "${DETALLES[$i]}"
            sep=","; [ "$i" -eq $((${#DETALLES[@]} - 1)) ] && sep=""
            echo "    {\"check\": \"$id\", \"descripcion\": \"$desc\", \"ubicacion\": \"${loc//\"/\\\"}\"}$sep"
        done
        echo "  ]"
        echo "}"
    } > docs/mediciones/seguridad/audit-sql-dynamic.json
    echo ""
    echo "JSON escrito en docs/mediciones/seguridad/audit-sql-dynamic.json"
fi

exit $([ $HALLAZGOS -eq 0 ] && echo 0 || echo 1)
