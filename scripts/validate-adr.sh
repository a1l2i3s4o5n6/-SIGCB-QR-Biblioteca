#!/bin/bash
# validate-adr.sh — Comprueba que el índice de ADR y los archivos del directorio
# no se hayan desincronizado.
#
# Falla si:
#   - existe un archivo NNNN-*.md que no aparece en el índice
#   - el índice enlaza un archivo que no existe
#   - un ADR no declara un estado reconocido
#
# Uso: bash scripts/validate-adr.sh

set -uo pipefail

ADR_DIR="docs/adr"
INDEX="$ADR_DIR/README.md"
ERRORS=0

if [ ! -f "$INDEX" ]; then
    echo "ERROR: no se encuentra $INDEX"
    exit 1
fi

echo "=== Validando registros de decisiones de arquitectura ==="

# 1. Todo archivo NNNN-*.md debe estar enlazado en el índice
for file in "$ADR_DIR"/[0-9][0-9][0-9][0-9]-*.md; do
    [ -e "$file" ] || continue
    base=$(basename "$file")
    if ! grep -qF "($base)" "$INDEX"; then
        echo "ERROR: $base existe pero no aparece en el índice"
        ERRORS=$((ERRORS + 1))
    fi
done

# 2. Todo enlace del índice debe apuntar a un archivo existente
while read -r base; do
    if [ ! -f "$ADR_DIR/$base" ]; then
        echo "ERROR: el índice enlaza $base, que no existe"
        ERRORS=$((ERRORS + 1))
    fi
done < <(grep -oE '\([0-9]{4}-[a-z0-9-]+\.md\)' "$INDEX" | tr -d '()' | sort -u)

# 3. Todo ADR debe declarar un estado reconocido
for file in "$ADR_DIR"/[0-9][0-9][0-9][0-9]-*.md; do
    [ -e "$file" ] || continue
    if ! grep -qE '^\- \*\*Estado:\*\* (Propuesto|Aceptado|Reemplazado|Rechazado)' "$file"; then
        echo "ERROR: $(basename "$file") no declara un estado reconocido"
        ERRORS=$((ERRORS + 1))
    fi
done

count=$(ls "$ADR_DIR"/[0-9][0-9][0-9][0-9]-*.md 2>/dev/null | wc -l)

if [ "$ERRORS" -gt 0 ]; then
    echo "VALIDACIÓN FALLÓ: $ERRORS errores encontrados"
    exit 1
fi

echo "VALIDACIÓN EXITOSA: $count ADR sincronizados con el índice"
