#!/usr/bin/env python3
"""Calcula el digest SHA-256 de la entrega y genera los fragmentos que la
caratula y el informe incluyen.

Que se resume exactamente: la lista ordenada de todos los ficheros que Git
sigue, cada uno con su hash de contenido. No se resume el .git, ni los ficheros
ignorados, ni las marcas de tiempo, de modo que dos clones del mismo commit dan
el mismo digest en cualquier maquina y sistema operativo.

El digest se calcula sobre el MANIFIESTO en texto, no sobre un tar: un tar
incorpora permisos y orden de directorio, que varian entre Windows y Linux, y
el digest dejaria de ser reproducible entre los dos entornos del equipo.

Genera:
  docs/caratula/commit.tex   commit corto
  docs/caratula/digest.tex   digest corto (16 hex) para que quepa en una linea
  docs/caratula/MANIFIESTO.txt  manifiesto completo, verificable
  docs/caratula/digest.txt   digest completo (64 hex)

Uso: python scripts/entrega-digest.py [--check]
  --check  recalcula y falla si no coincide con lo ya generado (para CI)
"""
import hashlib
import pathlib
import subprocess
import sys

SALIDA = pathlib.Path('docs/caratula')


def git(*args):
    return subprocess.run(['git', *args], capture_output=True, text=True,
                          check=True).stdout.strip()


def sin_versionar():
    """Ficheros presentes y no ignorados que Git todavia no sigue.

    Importa porque el manifiesto se construye con 'git ls-files': un fichero sin
    anadir no entraria en el digest, y el digest afirmaria cubrir una entrega
    que en realidad no cubre. Es preferible avisar a firmar de menos en
    silencio.
    """
    salida = git('ls-files', '--others', '--exclude-standard')
    return [l for l in salida.splitlines() if l.strip()]


# PDF que este repositorio genera a partir de sus propias fuentes .tex.
#
# Quedan FUERA del manifiesto, y el motivo no es de comodidad sino de
# consistencia: la portada del informe y la caratula imprimen el digest de la
# entrega. Si el digest cubriera esos PDF, calcularlo cambiaria los PDF, lo que
# cambiaria el digest, lo que obligaria a recompilar: no existiria ningun valor
# estable. El manifiesto firma las FUENTES; los PDF se regeneran de ellas y su
# procedimiento esta documentado en el README.
GENERADOS = {
    'docs/informe/informe-tecnico.pdf',
    'docs/caratula/caratula.pdf',
}


def manifiesto():
    """Lineas 'sha256  ruta' de las fuentes seguidas, en orden estable."""
    ficheros = sorted(git('ls-files').splitlines())
    lineas = []
    for rel in ficheros:
        if rel in GENERADOS:
            continue
        ruta = pathlib.Path(rel)
        if not ruta.is_file():
            continue                      # borrado en el arbol de trabajo
        h = hashlib.sha256(ruta.read_bytes()).hexdigest()
        lineas.append(f'{h}  {rel}')
    return lineas


def main():
    comprobar = '--check' in sys.argv

    pendientes = sin_versionar()
    if pendientes:
        print(f'AVISO: {len(pendientes)} fichero(s) sin versionar quedarian FUERA '
              f'del digest de la entrega:')
        for f in pendientes[:20]:
            print(f'  - {f}')
        if len(pendientes) > 20:
            print(f'  ... y {len(pendientes) - 20} mas')
        print('Anadelos con "git add" antes de firmar la entrega.')
        print()

    lineas = manifiesto()
    texto = '\n'.join(lineas) + '\n'
    digest = hashlib.sha256(texto.encode('utf-8')).hexdigest()
    commit = git('rev-parse', '--short', 'HEAD')

    if comprobar:
        previo = (SALIDA / 'digest.txt')
        if not previo.exists():
            print('ERROR: falta docs/caratula/digest.txt; ejecuta el script sin --check.')
            return 1
        if previo.read_text(encoding='utf-8').strip() != digest:
            print('ERROR: el digest de la entrega no coincide con el versionado.')
            print(f'  versionado: {previo.read_text(encoding="utf-8").strip()}')
            print(f'  recalculado: {digest}')
            return 1
        print(f'OK — digest de la entrega verificado: {digest}')
        return 0

    SALIDA.mkdir(parents=True, exist_ok=True)
    (SALIDA / 'MANIFIESTO.txt').write_text(texto, encoding='utf-8', newline='\n')
    (SALIDA / 'digest.txt').write_text(digest + '\n', encoding='utf-8', newline='\n')
    (SALIDA / 'digest.tex').write_text(digest[:16] + r'\ldots', encoding='utf-8', newline='\n')
    (SALIDA / 'commit.tex').write_text(commit, encoding='utf-8', newline='\n')

    print(f'Ficheros en el manifiesto: {len(lineas)}')
    print(f'Commit ..................: {commit}')
    print(f'Digest SHA-256 ..........: {digest}')
    return 0


if __name__ == '__main__':
    sys.exit(main())
