#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Calcula la puntuación System Usability Scale (SUS) a partir de un CSV de
respuestas.

Fórmula (Brooke, 1996):
  - ítems impares (1,3,5,7,9): contribución = puntuación - 1
  - ítems pares   (2,4,6,8,10): contribución = 5 - puntuación
  - SUS = suma de contribuciones (0-40) x 2,5  ->  0-100

Formato del CSV:
  participante,perfil,p1,p2,p3,p4,p5,p6,p7,p8,p9,p10
  P01,estudiante,4,2,5,1,4,2,5,1,4,2

Uso:
  python scripts/sus-score.py docs/mediciones/usabilidad/sus-respuestas.csv
  python scripts/sus-score.py --autotest      # verifica la fórmula

El script se niega a inventar: si el archivo no tiene filas válidas, lo dice y
sale con código 1 en lugar de emitir una puntuación.
"""
import csv
import io
import math
import statistics
import sys
from collections import defaultdict

ITEMS = [f"p{i}" for i in range(1, 11)]


def puntuar(respuestas):
    """respuestas: lista de 10 enteros de 1 a 5. Devuelve el SUS (0-100)."""
    if len(respuestas) != 10:
        raise ValueError(f"se esperaban 10 items, llegaron {len(respuestas)}")
    for v in respuestas:
        if not 1 <= v <= 5:
            raise ValueError(f"valor fuera del rango 1-5: {v}")
    total = 0
    for indice, valor in enumerate(respuestas):
        numero = indice + 1
        total += (valor - 1) if numero % 2 == 1 else (5 - valor)
    return total * 2.5


def adjetivo(sus):
    """Escala de adjetivos de Bangor et al. (2009)."""
    if sus >= 85.5:
        return "excelente"
    if sus >= 71.4:
        return "bueno"
    if sus >= 50.9:
        return "aceptable (limite)"
    if sys.maxsize and sus >= 35.7:
        return "pobre"
    return "inaceptable"


def ic95(valores):
    """Intervalo de confianza al 95 % de la media (t de Student aproximada)."""
    n = len(valores)
    if n < 2:
        return None
    media = statistics.mean(valores)
    error = statistics.stdev(valores) / math.sqrt(n)
    # Valores criticos de t para dos colas al 95 %, por grados de libertad.
    t_tabla = {1: 12.706, 2: 4.303, 3: 3.182, 4: 2.776, 5: 2.571, 6: 2.447,
               7: 2.365, 8: 2.306, 9: 2.262, 10: 2.228, 11: 2.201, 12: 2.179,
               13: 2.160, 14: 2.145, 15: 2.131, 20: 2.086, 25: 2.060, 30: 2.042}
    gl = n - 1
    t = t_tabla.get(gl, 1.96 if gl > 30 else 2.2)
    return (media - t * error, media + t * error)


def autotest():
    """Comprueba la formula con los casos canonicos de la literatura."""
    casos = [
        ([5, 1, 5, 1, 5, 1, 5, 1, 5, 1], 100.0, "todo maximamente favorable"),
        ([1, 5, 1, 5, 1, 5, 1, 5, 1, 5], 0.0, "todo maximamente desfavorable"),
        ([3, 3, 3, 3, 3, 3, 3, 3, 3, 3], 50.0, "todo neutro"),
    ]
    fallos = 0
    for respuestas, esperado, descripcion in casos:
        obtenido = puntuar(respuestas)
        estado = "OK" if abs(obtenido - esperado) < 1e-9 else "FALLA"
        if estado == "FALLA":
            fallos += 1
        print(f"  [{estado}] {descripcion:34} esperado={esperado:6.1f} obtenido={obtenido:6.1f}")
    for invalidas, motivo in [([5] * 9, "faltan items"), ([6] + [3] * 9, "valor fuera de rango")]:
        try:
            puntuar(invalidas)
            print(f"  [FALLA] deberia rechazar: {motivo}")
            fallos += 1
        except ValueError:
            print(f"  [OK] rechaza correctamente: {motivo}")
    if fallos:
        sys.exit(f"\nAUTOTEST FALLIDO: {fallos} caso(s)")
    print("\nAUTOTEST OK: la formula SUS se comporta como la literatura describe.")


def main():
    if "--autotest" in sys.argv:
        print("=== Verificacion de la formula SUS ===")
        autotest()
        return

    if len(sys.argv) < 2:
        sys.exit(f"Uso: python {sys.argv[0]} <archivo.csv> | --autotest")

    ruta = sys.argv[1]
    try:
        filas = list(csv.DictReader(io.open(ruta, encoding="utf-8")))
    except FileNotFoundError:
        sys.exit(f"ERROR: no se encuentra {ruta}")

    puntuaciones = []
    por_perfil = defaultdict(list)
    descartadas = 0

    for fila in filas:
        etiqueta = (fila.get("participante") or "?").strip()
        if etiqueta.startswith("#") or not etiqueta:
            continue
        try:
            respuestas = [int(str(fila[i]).strip()) for i in ITEMS]
            sus = puntuar(respuestas)
        except (KeyError, ValueError, TypeError) as e:
            print(f"  [DESCARTADA] {etiqueta}: {e}")
            descartadas += 1
            continue
        puntuaciones.append(sus)
        por_perfil[(fila.get("perfil") or "sin perfil").strip()].append(sus)
        print(f"  {etiqueta:6} {(fila.get('perfil') or ''):14} SUS = {sus:5.1f}  ({adjetivo(sus)})")

    if not puntuaciones:
        print()
        print("No hay ninguna respuesta valida en el archivo.")
        print("No se emite puntuacion: sin participantes no hay resultado que informar.")
        sys.exit(1)

    n = len(puntuaciones)
    media = statistics.mean(puntuaciones)
    print()
    print("=== Resumen ===")
    print(f"  Participantes validos : {n}" + (f" (descartadas: {descartadas})" if descartadas else ""))
    print(f"  Media                 : {media:.1f}  ({adjetivo(media)})")
    print(f"  Mediana               : {statistics.median(puntuaciones):.1f}")
    if n >= 2:
        print(f"  Desviacion tipica     : {statistics.stdev(puntuaciones):.1f}")
        lo, hi = ic95(puntuaciones)
        print(f"  IC 95 % de la media   : [{lo:.1f}, {hi:.1f}]")
    print(f"  Minimo / Maximo       : {min(puntuaciones):.1f} / {max(puntuaciones):.1f}")
    print(f"  Referencia industria  : 68,0 (promedio segun Sauro y Lewis)")

    if len(por_perfil) > 1:
        print()
        print("=== Por perfil ===")
        for perfil, valores in sorted(por_perfil.items()):
            print(f"  {perfil:14} n={len(valores):2}  media={statistics.mean(valores):5.1f}")

    if n < 8:
        print()
        print(f"  AVISO: con n={n} la estimacion de la media es muy imprecisa.")
        print("  Sirve para detectar problemas graves de usabilidad, no para comparar versiones.")


if __name__ == "__main__":
    main()
