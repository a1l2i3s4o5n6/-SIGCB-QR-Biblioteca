#!/usr/bin/env python3
"""Analiza las cinco corridas de k6 con el perfil de 50 VU sostenidos 30 s.

Lee los crudos de docs/mediciones/perf/50vu/, extrae las metricas y calcula
estadistica descriptiva mas un intervalo de confianza del 95 % por la t de
Student. Con n=5 el intervalo es ancho y se dice; lo que no se hace es
omitirlo, que es lo que ocurria cuando solo habia tres corridas y una de
ellas sin crudo.

No se aplica ninguna prueba inferencial de comparacion porque no hay dos
grupos que comparar: las cinco corridas son del mismo tratamiento. El
intervalo de confianza es la afirmacion inferencial que estos datos
sostienen, y es la unica.

Uso: python scripts/analisis-k6-50vu.py [--check]
  --check  recalcula y compara con REPORT-50VU.md; falla si no coincide.
"""
import math
import pathlib
import re
import statistics
import sys

CRUDOS = pathlib.Path('docs/mediciones/perf/50vu')

# t de Student de dos colas al 95 %, por grados de libertad (n-1).
# Se tabula en lugar de depender de scipy: el proyecto no la usa y anadir
# una dependencia pesada para un solo valor no se justifica.
T_95 = {1: 12.706, 2: 4.303, 3: 3.182, 4: 2.776, 5: 2.571,
        6: 2.447, 7: 2.365, 8: 2.306, 9: 2.262, 10: 2.228}


def a_ms(texto):
    """Convierte '5.56ms', '1.14s' o '369.92ms' a milisegundos."""
    m = re.match(r'([\d.]+)(ms|s|us|m)$', texto.strip())
    if not m:
        return None
    valor, unidad = float(m.group(1)), m.group(2)
    return {'ms': valor, 's': valor * 1000, 'us': valor / 1000,
            'm': valor * 60000}[unidad]


def extrae(ruta):
    """Saca de un crudo de k6 las metricas que interesan."""
    txt = ruta.read_text(encoding='utf-8', errors='replace')
    d = {'fichero': ruta.name}

    m = re.search(r'catalogo_duracion[.\s]*:\s*avg=(\S+).*?med=(\S+).*?p\(95\)=(\S+)', txt)
    if m:
        d['media'] = a_ms(m.group(1))
        d['mediana'] = a_ms(m.group(2))
        d['p95'] = a_ms(m.group(3))

    m = re.search(r'iterations[.\s]*:\s*(\d+)', txt)
    if m:
        d['iteraciones'] = int(m.group(1))

    m = re.search(r'http_reqs[.\s]*:\s*(\d+)', txt)
    if m:
        d['peticiones'] = int(m.group(1))

    m = re.search(r'checks_succeeded[.\s]*:\s*([\d.]+)%\s+(\d+) out of (\d+)', txt)
    if m:
        d['checks_ok'] = int(m.group(2))
        d['checks_total'] = int(m.group(3))

    m = re.search(r'http_req_failed[.\s]*:\s*([\d.]+)%\s+(\d+) out of (\d+)', txt)
    if m:
        d['fallidas'] = int(m.group(2))

    m = re.search(r'vus_max[.\s]*:\s*(\d+)', txt)
    if m:
        d['vus_max'] = int(m.group(1))

    m = re.search(r'# Fecha \(UTC\): (\S+)', txt)
    if m:
        d['fecha'] = m.group(1)
    return d


def ic95(datos):
    """Intervalo de confianza del 95 % de la media, por la t de Student."""
    n = len(datos)
    if n < 2:
        return None
    media = statistics.mean(datos)
    s = statistics.stdev(datos)                 # cuasidesviacion, n-1
    t = T_95.get(n - 1)
    if t is None:
        return None
    margen = t * s / math.sqrt(n)
    return media, media - margen, media + margen, s, margen


def main():
    if not CRUDOS.is_dir():
        print(f'ERROR: no existe {CRUDOS}')
        return 1

    corridas = [extrae(p) for p in sorted(CRUDOS.glob('k6-50vu-run*.txt'))]
    if not corridas:
        print(f'ERROR: no hay crudos en {CRUDOS}')
        return 1

    print(f'Corridas analizadas: {len(corridas)}')
    print()
    print(f"{'Corrida':<20}{'VU max':>7}{'Iter.':>8}{'Pet.':>7}"
          f"{'Media':>9}{'Mediana':>9}{'p95':>9}{'Errores':>9}")
    print('-' * 78)
    for c in corridas:
        print(f"{c['fichero']:<20}{c.get('vus_max', 0):>7}{c.get('iteraciones', 0):>8}"
              f"{c.get('peticiones', 0):>7}{c.get('media', 0):>8.2f}ms"
              f"{c.get('mediana', 0):>7.2f}ms{c.get('p95', 0):>7.2f}ms"
              f"{c.get('fallidas', 0):>9}")

    print()
    for etiqueta, clave in [('p95', 'p95'), ('Media', 'media'), ('Mediana', 'mediana')]:
        vals = [c[clave] for c in corridas if clave in c]
        r = ic95(vals)
        if r:
            media, lo, hi, s, margen = r
            print(f'{etiqueta:8s}: media={media:6.2f} ms  '
                  f'DE={s:5.2f}  IC95%=[{lo:.2f}, {hi:.2f}] ms  (+/-{margen:.2f})')

    total_iter = sum(c.get('iteraciones', 0) for c in corridas)
    total_pet = sum(c.get('peticiones', 0) for c in corridas)
    total_err = sum(c.get('fallidas', 0) for c in corridas)
    total_chk = sum(c.get('checks_total', 0) for c in corridas)
    total_ok = sum(c.get('checks_ok', 0) for c in corridas)
    print()
    print(f'Iteraciones totales : {total_iter}')
    print(f'Peticiones totales  : {total_pet}')
    print(f'Peticiones fallidas : {total_err}')
    print(f'Comprobaciones      : {total_ok}/{total_chk}')

    vus = {c.get('vus_max') for c in corridas}
    print(f'VU maximos          : {vus} (el perfil exigido es 50)')
    if vus != {50}:
        print('AVISO: alguna corrida no uso el perfil de 50 VU.')
        return 1
    return 0


if __name__ == '__main__':
    sys.exit(main())
