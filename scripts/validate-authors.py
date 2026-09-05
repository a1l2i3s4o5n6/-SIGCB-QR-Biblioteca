#!/usr/bin/env python3
"""Valida la coherencia de la autoria del repositorio.

Comprobaciones (sin argumentos; exit != 0 si algo falla):

  1. Existe el `.mailmap` que unifica las identidades de git.
  2. Cada correo de autor de git (con mailmap aplicado) aparece UNA sola vez
     y esta declarado en CONTRIBUTORS.md (`**Email (git):**`). Un correo duplicado
     entre nombres distintos indica una identidad sin mapear.
  El conjunto de correos declarados en CONTRIBUTORS.md coincide con el conjunto
  de correos presentes en los commits (sin fantasmas ni ausencias).
  4. Los 3 archivos de autoria (CONTRIBUTORS.md, CITATION.cff y .zenodo.json)
     declaran exactamente el mismo conjunto de integrantes.
  5. AVISO (no falla): si quedan ORCID pendientes (`0000-0000-0000-0000`) en
     CONTRIBUTORS.md o CITATION.cff; esos IDs los debe registrar cada integrante
     antes de la publicacion en Zenodo.

Uso:
  python scripts/validate-authors.py
"""

import json
import re
import subprocess
import sys
import unicodedata
from pathlib import Path

RAIZ = Path(__file__).resolve().parent.parent

PLACEHOLDER_ORCID = "0000-0000-0000-0000"

ERRORES = []
AVISOS = []


def error(msg):
    ERRORES.append(msg)
    print(f"[ERROR] {msg}")


def aviso(msg):
    AVISOS.append(msg)
    print(f"[AVISO] {msg}")


def normaliza(nombre):
    """Minusculas, sin acentos ni puntuacion, para comparar superficies."""
    sin_acentos = "".join(
        c for c in unicodedata.normalize("NFD", nombre) if unicodedata.category(c) != "Mn"
    )
    return re.sub(r"[^a-z0-9]", "", sin_acentos.lower())


def nombres_y_correos_contributors(texto):
    cabeceras = re.findall(r"^###\s+(.+)$", texto, flags=re.MULTILINE)
    correos = [c for c in re.findall(r"\*\*Email \(git\):\*\*\s*(\S+)", texto) if "@" in c]
    return [normaliza(n) for n in cabeceras], set(correos)


def nombres_citation(texto):
    return [normaliza(m) for m in re.findall(r"^  - name:\s*[\"']?([^\"'\n]+)[\"']?$", texto, flags=re.MULTILINE)]


def nombres_zenodo(ruta):
    with open(ruta, encoding="utf-8") as f:
        datos = json.load(f)
    return [normaliza(c["name"]) for c in datos["creators"]]


def autores_git():
    salida = subprocess.run(
        ["git", "log", "--no-merges", "--format=%aN <%aE>"],
        cwd=str(RAIZ), capture_output=True, text=True, check=True,
    ).stdout
    lineas = [l for l in salida.splitlines() if l.strip()]
    pares = []
    for linea in lineas:
        nombre, correo = linea.rsplit("<", 1)
        pares.append((nombre.strip(), correo.rstrip(">")))
    return pares


def main():
    print("=== Validando coherencia de autoria ===")

    mailmap = RAIZ / ".mailmap"
    if not mailmap.exists():
        error("falta el .mailmap; las identidades de git (JuniorSoft363/BryBryst) quedarían fragmentadas")
    else:
        print(f"  [OK] .mailmap presente ({mailmap.name})")

    texto_contrib = (RAIZ / "CONTRIBUTORS.md").read_text(encoding="utf-8")
    texto_citation = (RAIZ / "CITATION.cff").read_text(encoding="utf-8")
    zenodo = RAIZ / ".zenodo.json"

    nombres_cont, correos_cont = nombres_y_correos_contributors(texto_contrib)
    autores = autores_git()
    if not autores:
        error("git no devolvió ningún autor; ¿se está ejecutando dentro del repositorio?")

    correo_a_nombres = {}
    for nombre, correo in autores:
        correo_a_nombres.setdefault(correo, set()).add(nombre)

    correos_git = list(correo_a_nombres)
    correos_duplicados = {
        correo: sorted(nombres)
        for correo, nombres in correo_a_nombres.items()
        if len(nombres) > 1
    }
    if correos_duplicados:
        error(f"correo con más de una identidad sin mapear: {correos_duplicados}")
    else:
        print("  [OK] cada correo de autor aparece con una sola identidad (mailmap aplicado)")

    if ERRORES:
        pass
    elif correos_cont != set(correos_git):
        error(
            "los correos declarados en CONTRIBUTORS.md y los presentes en git no coinciden. "
            f"declarados={sorted(correos_cont)} git={sorted(set(correos_git))}. "
            "Añade el correo nuevo a CONTRIBUTORS.md y al .mailmap."
        )
    else:
        print("  [OK] correos declarados = correos de git y viceversa")

    citacion = {normaliza(m) for m in nombres_citation(texto_citation)}
    if not zenodo.exists():
        error("falta .zenodo.json")
        zenodo_nombres = set()
    else:
        zenodo_nombres = set(nombres_zenodo(zenodo))

    conj_cont = set(nombres_cont)
    if len(conj_cont) != len(nombres_cont):
        error("hay integrantes duplicados en las cabeceras de CONTRIBUTORS.md")
    if conj_cont == citacion == zenodo_nombres and len(conj_cont) == 3:
        print("  [OK] CONTRIBUTORS / CITATION.cff / .zenodo.json declaran los mismos 3 integrantes")
    else:
        error(
            "las superficies de autoría no coinciden. "
            f"CONTRIBUTORS={sorted(conj_cont)} CITATION={sorted(citacion)} zenodo={sorted(zenodo_nombres)}"
        )

    pendientes = (texto_contrib + texto_citation).count(PLACEHOLDER_ORCID)
    if pendientes:
        aviso(
            f"quedan {pendientes} ORCID placeholder ({PLACEHOLDER_ORCID}) en "
            "CONTRIBUTORS.md/CITATION.cff: registrarlos antes de publicar en Zenodo."
        )
    else:
        print("  [OK] sin ORCID placeholder")

    print()
    if ERRORES:
        print(f"RESULTADO: {len(ERRORES)} error(es), {len(AVISOS)} aviso(s).")
        sys.exit(1)
    print(f"RESULTADO: VALIDACIÓN EXITOSA con {len(AVISOS)} aviso(s).")
    sys.exit(0)


if __name__ == "__main__":
    main()