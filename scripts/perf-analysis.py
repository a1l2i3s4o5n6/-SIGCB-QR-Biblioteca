#!/usr/bin/env python3
"""
perf-analysis.py — Análisis estadístico de mediciones de rendimiento
Semilla fija: 42
Uso: python scripts/perf-analysis.py docs/mediciones/perf/k6-run*.json
"""

import json
import sys
import statistics
import random
from pathlib import Path

random.seed(42)

def load_metrics(files):
    times = []
    for f in files:
        data = json.loads(Path(f).read_text())
        # Extraer tiempos de respuesta de k6
        for metric, values in data.get("metrics", {}).items():
            if "http_req_duration" in metric:
                times.extend(values.get("values", []))
    return sorted(times)

def report(times):
    if not times:
        print("NO DATA — No se encontraron métricas")
        return

    n = len(times)
    mean = statistics.mean(times)
    stdev = statistics.stdev(times) if n > 1 else 0.0
    ci_95 = 1.96 * stdev / (n ** 0.5)

    print("=== Reporte de Rendimiento ===")
    print(f"Semilla: 42")
    print(f"Muestras: {n}")
    print(f"Media: {mean:.2f} ms")
    print(f"Desviación típica: {stdev:.2f} ms")
    print(f"IC 95%%: [{mean - ci_95:.2f}, {mean + ci_95:.2f}] ms")
    print(f"p50: {times[n // 2]:.2f} ms")
    print(f"p90: {times[int(n * 0.90)]:.2f} ms")
    print(f"p95: {times[int(n * 0.95)]:.2f} ms")
    print(f"p99: {times[int(n * 0.99)]:.2f} ms")

if __name__ == "__main__":
    files = sys.argv[1:] if len(sys.argv) > 1 else []
    if not files:
        print("Uso: python scripts/perf-analysis.py <archivos-k6>")
        sys.exit(1)
    times = load_metrics(files)
    report(times)
