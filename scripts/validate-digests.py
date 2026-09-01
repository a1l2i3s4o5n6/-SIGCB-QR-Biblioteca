#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Valida que todas las imágenes base del proyecto estén ancladas por digest
SHA256 con exactamente 64 caracteres hexadecimales.

Verifica:
  - docker-compose.yml (servicios con imagen)
  - .github/workflows/ci.yml (servicios de CI)

Uso:
  python scripts/validate-digests.py
"""
import re
import sys
from pathlib import Path

SHA256_RE = re.compile(r"@(sha256:[0-9a-f]{64})")


def find_digests(path):
    """Devuelve una lista de (func_valida, descripcion)."""
    digests = []
    if not Path(path).exists():
        return digests
    text = Path(path).read_text(encoding="utf-8", errors="replace")
    # Busca líneas que definan una imagen:  image: <algo>@sha256:<64hex>
    for m in re.finditer(r"^\s*image:\s*(.+?)\s*$", text, re.MULTILINE):
        ref = m.group(1).strip()
        dm = SHA256_RE.search(ref)
        if not dm:
            digests.append((False, f"{path}: sin digest -> {ref}"))
            continue
        digest = dm.group(1)
        ok = len(digest) == len("sha256:") + 64
        digests.append((ok, f"{path}: {ref} ({len(digest)} chars -> {'OK' if ok else 'INVALIDO'})"))
    return digests


def main():
    targets = ["docker-compose.yml", ".github/workflows/ci.yml"]
    all_ok = True
    for target in targets:
        items = find_digests(target)
        if not items:
            print(f"[WARN] {target}: no se encontró ninguna imagen por digest")
            continue
        for ok, desc in items:
            if not ok:
                all_ok = False
            print(f"  [{'OK' if ok else 'FAIL'}] {desc}")
    if all_ok:
        print("\nTodos los digests SHA256 son válidos (64 caracteres hexadecimales).")
        sys.exit(0)
    print("\nERROR: existen digests inválidos o imágenes sin anclar.")
    sys.exit(1)


if __name__ == "__main__":
    main()