#!/usr/bin/env python3
"""
Generates docs/entrega/markdown-tercero.md — consolidated markdown of all
Tercera Entrega documents (SRS, casos de uso, historias, matriz, codigo,
apendices) in the same order as ENTREGA-TERCERA.pdf.
"""

from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "docs" / "entrega" / "markdown-tercero.md"

CU_MAP = {1: "autenticacion", 2: "gestion-libros", 3: "prestamo", 4: "devolucion", 5: "reportes"}
HU_MAP = {1: "autenticacion", 2: "registro", 3: "logout", 4: "crud-usuarios",
          5: "crud-libros", 6: "prestamo", 7: "devolucion", 8: "renovacion",
          9: "reportes", 10: "dashboard"}

CODE_FILES = [
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtTokenProvider.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtAuthenticationFilter.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/SecurityConfig.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtBlacklistService.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/AuthController.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/AuthService.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/PrestamoController.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/PrestamoService.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/CacheConfig.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/exception/GlobalExceptionHandler.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Prestamo.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Libro.java", "java"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Usuario.java", "java"),
    ("db/procs/sp_crear_prestamo.sql", "sql"),
    ("db/procs/sp_devolver_prestamo.sql", "sql"),
    ("db/procs/sp_reporte_prestamos_diarios.sql", "sql"),
]


def read(rel_path):
    p = ROOT / rel_path
    if p.exists():
        return p.read_text(encoding="utf-8")
    return ""


def csv_to_markdown(csv_text):
    lines = [l for l in csv_text.strip().split("\n") if l.strip()]
    if not lines:
        return ""
    out = []
    for i, line in enumerate(lines):
        cells = line.split(",")
        out.append("| " + " | ".join(cells) + " |")
        if i == 0:
            out.append("|" + "---|" * len(cells))
    return "\n".join(out)


def build_toc():
    toc = []
    toc.append("- [1. SRS - Documento de Requisitos](#1-srs--documento-de-requisitos)")
    toc.append("\n- **2. Casos de Uso**")
    for i in range(1, 6):
        toc.append(f"  - [CU-0{i}](#cu-0{i}-{CU_MAP[i]})")
    toc.append("\n- **3. Historias de Usuario**")
    for i in range(1, 11):
        toc.append(f"  - [HU-{i:02d}](#hu-{i:02d}-{HU_MAP[i]})")
    toc.append("\n- [4. Matriz de Trazabilidad](#4-matriz-de-trazabilidad)")
    toc.append("\n- **5. Codigo Fuente**")
    for rel, _ in CODE_FILES:
        name = Path(rel).name
        toc.append(f"  - [{name}](#{name.lower().replace('_', '-').replace('.', '-')})")
    toc.append("\n- **7. Apendices**")
    toc.append("  - [Bitacora de Observaciones](#bitacora-de-observaciones)")
    toc.append("  - [CHANGELOG de Requisitos](#changelog-de-requisitos)")
    toc.append("  - [Catalogo de Stored Procedures](#catalogo-de-stored-procedures)")
    return "\n".join(toc)


def main():
    parts = []

    parts.append("# ENTREGA - TERCERA ENTREGA")
    parts.append("")
    parts.append("## SIGCB-QR - Sistema Integral de Gestion Bibliotecaria")
    parts.append("")
    parts.append("> Version: v0.9.0-rc  |  Julio 2026")
    parts.append(">")
    parts.append("> Documento consolidado: SRS, Casos de Uso, Historias de Usuario, Matriz de Trazabilidad, Codigo Fuente y Apendices")
    parts.append("")
    parts.append("---")
    parts.append("")
    parts.append("## Tabla de Contenidos")
    parts.append("")
    parts.append(build_toc())
    parts.append("")
    parts.append("---")
    parts.append("")

    # 1. SRS
    parts.append("## 1. SRS - Documento de Requisitos")
    parts.append("")
    parts.append(read("docs/requisitos/SRS.md").strip())
    parts.append("")
    parts.append("---")
    parts.append("")

    # 2. Casos de Uso
    parts.append("## 2. Casos de Uso")
    parts.append("")
    for i in range(1, 6):
        parts.append(f"### CU-0{i}: {CU_MAP[i]}")
        parts.append("")
        parts.append(read(f"docs/requisitos/casos-de-uso/CU-0{i}-{CU_MAP[i]}.md").strip())
        parts.append("")
    parts.append("---")
    parts.append("")

    # 3. Historias de Usuario
    parts.append("## 3. Historias de Usuario")
    parts.append("")
    for i in range(1, 11):
        parts.append(f"### HU-{i:02d}: {HU_MAP[i]}")
        parts.append("")
        parts.append(read(f"docs/requisitos/historias/HU-{i:02d}-{HU_MAP[i]}.md").strip())
        parts.append("")
    parts.append("---")
    parts.append("")

    # 4. Matriz
    parts.append("## 4. Matriz de Trazabilidad")
    parts.append("")
    parts.append(csv_to_markdown(read("docs/trazabilidad/matriz.csv")))
    parts.append("")
    parts.append("---")
    parts.append("")

    # 5. Codigo Fuente
    parts.append("## 5. Codigo Fuente")
    parts.append("")
    for rel, lang in CODE_FILES:
        parts.append(f"### {Path(rel).name}")
        parts.append("")
        parts.append(f"`{rel}`")
        parts.append("")
        parts.append(f"```{lang}")
        code = read(rel).rstrip("\n")
        parts.append(code)
        parts.append("```")
        parts.append("")
    parts.append("---")
    parts.append("")

    # 7. Apendices
    parts.append("## 7. Apendices")
    parts.append("")
    parts.append("### Bitacora de Observaciones")
    parts.append("")
    parts.append(read("docs/observaciones/OBSERVACIONES.md").strip())
    parts.append("")
    parts.append("### CHANGELOG de Requisitos")
    parts.append("")
    parts.append(read("docs/requisitos/CHANGELOG-REQ.md").strip())
    parts.append("")
    parts.append("### Catalogo de Stored Procedures")
    parts.append("")
    parts.append(read("docs/basedatos/CATALOGO-SP.md").strip())
    parts.append("")

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text("\n".join(parts), encoding="utf-8")
    print(f"Generado: {OUTPUT}")
    print(f"Tamano: {OUTPUT.stat().st_size} bytes")
    print(f"Lineas: {len(OUTPUT.read_text('utf-8').splitlines())}")


if __name__ == "__main__":
    main()
