#!/bin/bash
# validate-traceability.sh — Valida que todo requisito Must tenga trazabilidad completa
# Uso: ./scripts/validate-traceability.sh
# Este script se ejecuta en CI para rechazar commits con requisitos Must huérfanos

set -euo pipefail

MATRIX="docs/trazabilidad/matriz.csv"
ERRORS=0

if [ ! -f "$MATRIX" ]; then
    echo "ERROR: No se encuentra $MATRIX"
    exit 1
fi

echo "=== Validando matriz de trazabilidad ==="

# Saltar cabecera, leer cada línea
tail -n +2 "$MATRIX" | while IFS=',' read -r id tipo prioridad hu cu modulo endpoint prueba acceso evidencia estado; do
    # Limpiar espacios
    id=$(echo "$id" | xargs)
    prioridad=$(echo "$prioridad" | xargs)
    estado=$(echo "$estado" | xargs)

    if [ "$prioridad" = "Must" ]; then
        # Verificar que tenga historia o caso de uso
        hu=$(echo "$hu" | xargs)
        cu=$(echo "$cu" | xargs)
        if [ -z "$hu" ] && [ -z "$cu" ]; then
            echo "ERROR: $id (Must) sin historia de usuario ni caso de uso"
            ERRORS=$((ERRORS + 1))
        fi

        # Verificar que tenga endpoint o módulo
        modulo=$(echo "$modulo" | xargs)
        endpoint=$(echo "$endpoint" | xargs)
        if [ -z "$modulo" ] && [ -z "$endpoint" ]; then
            echo "ERROR: $id (Must) sin módulo ni endpoint"
            ERRORS=$((ERRORS + 1))
        fi

        # Verificar que tenga prueba
        prueba=$(echo "$prueba" | xargs)
        if [ -z "$prueba" ]; then
            echo "WARNING: $id (Must) sin prueba automatizada"
        fi

        if [ "$estado" != "verificado" ] && [ "$estado" != "implementado" ]; then
            echo "WARNING: $id (Must) estado '$estado'"
        fi
    fi
done

if [ "$ERRORS" -gt 0 ]; then
    echo "VALIDACIÓN FALLÓ: $ERRORS errores encontrados"
    exit 1
fi

echo "VALIDACIÓN EXITOSA: Trazabilidad completa"
