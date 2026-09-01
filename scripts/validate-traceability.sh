#!/bin/bash
# validate-traceability.sh — Valida la matriz de trazabilidad contra el repositorio.
#
# Comprueba, para cada fila:
#   1. Que la prueba citada EXISTA realmente en backend/src/test (clase y método).
#   2. Que la evidencia empírica citada EXISTA como archivo o directorio.
#   3. Que un requisito Must tenga historia de usuario o caso de uso.
#   4. Que un requisito Must tenga módulo o endpoint.
#   5. Que 'verificado' implique una prueba real o evidencia empírica real.
#
# El punto 1 es el motivo de existir de este script. En la Tercera Entrega, la
# matriz declaraba como "verificadas" cuatro clases de prueba que no existían en
# el repositorio (UsuarioControllerTest, LibroPerfTest y dos métodos con nombres
# que no coincidían con los reales). Una matriz que se comprueba a sí misma no
# puede volver a afirmar eso.
#
# Uso: bash scripts/validate-traceability.sh

set -uo pipefail

MATRIX="docs/trazabilidad/matriz.csv"
TEST_DIR="backend/src/test/java"
ERRORS=0
WARNINGS=0
FILAS=0

if [ ! -f "$MATRIX" ]; then
    echo "ERROR: no se encuentra $MATRIX"
    exit 1
fi
if [ ! -d "$TEST_DIR" ]; then
    echo "ERROR: no se encuentra $TEST_DIR"
    exit 1
fi

echo "=== Validando matriz de trazabilidad ==="

# Inventario real de pruebas del repositorio: "Clase.metodo" por línea.
INVENTARIO=$(mktemp)
trap 'rm -f "$INVENTARIO"' EXIT
while IFS= read -r archivo; do
    clase=$(basename "$archivo" .java)
    grep -oE '(void|public void) +[a-zA-Z0-9_]+ *\(' "$archivo" \
        | grep -oE '[a-zA-Z0-9_]+ *\($' \
        | tr -d ' (' \
        | grep -v '^setUp$' \
        | sed "s/^/${clase}./"
done < <(find "$TEST_DIR" -name '*Test.java' -o -name '*Tests.java') | sort -u > "$INVENTARIO"

echo "Pruebas encontradas en el repositorio: $(wc -l < "$INVENTARIO")"
echo

# La cabecera se salta; las filas no contienen comas dentro de los campos.
while IFS=',' read -r id tipo prioridad hu cu modulo endpoint prueba acceso evidencia estado; do
    [ -z "${id// }" ] && continue
    FILAS=$((FILAS + 1))

    id=$(echo "$id" | xargs)
    prioridad=$(echo "$prioridad" | xargs)
    estado=$(echo "$estado" | tr -d '\r' | xargs)
    prueba=$(echo "$prueba" | xargs)
    evidencia=$(echo "$evidencia" | xargs)
    hu=$(echo "$hu" | xargs); cu=$(echo "$cu" | xargs)
    modulo=$(echo "$modulo" | xargs); endpoint=$(echo "$endpoint" | xargs)

    # 1. La prueba citada debe existir.
    if [ -n "$prueba" ]; then
        if ! grep -qxF "$prueba" "$INVENTARIO"; then
            echo "ERROR: $id cita la prueba '$prueba', que NO existe en $TEST_DIR"
            ERRORS=$((ERRORS + 1))
        fi
    fi

    # 2. La evidencia citada debe existir.
    if [ -n "$evidencia" ] && [ ! -e "$evidencia" ]; then
        echo "ERROR: $id cita la evidencia '$evidencia', que no existe"
        ERRORS=$((ERRORS + 1))
    fi

    if [ "$prioridad" = "Must" ]; then
        # 3. Historia o caso de uso.
        if [ -z "$hu" ] && [ -z "$cu" ]; then
            echo "WARNING: $id (Must) sin historia de usuario ni caso de uso"
            WARNINGS=$((WARNINGS + 1))
        fi
        # 4. Módulo o endpoint.
        if [ -z "$modulo" ] && [ -z "$endpoint" ]; then
            echo "ERROR: $id (Must) sin módulo ni endpoint"
            ERRORS=$((ERRORS + 1))
        fi
    fi

    # 5. 'verificado' exige respaldo real.
    if [ "$estado" = "verificado" ] && [ -z "$prueba" ] && [ -z "$evidencia" ]; then
        echo "ERROR: $id declara 'verificado' sin prueba automatizada ni evidencia empírica"
        ERRORS=$((ERRORS + 1))
    fi

    # Estado reconocido.
    case "$estado" in
        verificado|implementado|pendiente) ;;
        *) echo "ERROR: $id tiene el estado no reconocido '$estado'"
           ERRORS=$((ERRORS + 1)) ;;
    esac
done < <(tail -n +2 "$MATRIX")

echo
echo "Filas analizadas : $FILAS"
echo "Errores          : $ERRORS"
echo "Advertencias     : $WARNINGS"

if [ "$ERRORS" -gt 0 ]; then
    echo "VALIDACIÓN FALLÓ: $ERRORS errores encontrados"
    exit 1
fi

echo "VALIDACIÓN EXITOSA: toda prueba y toda evidencia citadas existen en el repositorio"
