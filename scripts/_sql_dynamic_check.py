#!/usr/bin/env python3
"""Comprueba si alguna consulta SQL/JPQL se construye con algo que no sea un
literal de cadena.

Por qué existe este fichero y no un grep: una consulta JPQL larga se parte en
varias líneas con '+', y eso es concatenación *de literales en tiempo de
compilación*, que es segura. Un grep de '" +' marca las nueve consultas del
proyecto como peligrosas y las nueve son inocuas. La distinción que importa no
es "hay un +", sino "¿lo que se suma es un literal o una variable?".

El procedimiento es: localizar la llamada, extraer su argumento completo
—aunque abarque varias líneas—, borrar todos los literales de cadena y mirar
lo que queda. Si solo quedan '+' y espacios, la consulta es constante. Si queda
un identificador, se está interpolando algo y hay que mirarlo.

Uso: python scripts/_sql_dynamic_check.py <directorio>
Salida: una línea por hallazgo, 'fichero:linea:fragmento'. Código 1 si hay.
"""
import re
import sys
import pathlib

LLAMADAS = re.compile(r'(@Query|createNativeQuery|createQuery|createStatement)\s*\(')
LITERAL = re.compile(r'"(?:[^"\\]|\\.)*"')
IDENTIFICADOR = re.compile(r'[A-Za-z_][A-Za-z0-9_.]*')
# Anotaciones de atributo que pueden seguir a la consulta y no forman parte de ella
RUIDO = re.compile(r'\b(nativeQuery|value|countQuery|name)\b|true|false')


def argumento_completo(texto, inicio):
    """Devuelve el texto entre el paréntesis que abre en 'inicio' y su cierre."""
    profundidad = 0
    dentro_cadena = False
    escape = False
    for i in range(inicio, len(texto)):
        c = texto[i]
        if dentro_cadena:
            if escape:
                escape = False
            elif c == '\\':
                escape = True
            elif c == '"':
                dentro_cadena = False
            continue
        if c == '"':
            dentro_cadena = True
        elif c == '(':
            profundidad += 1
        elif c == ')':
            profundidad -= 1
            if profundidad == 0:
                return texto[inicio + 1:i]
    return texto[inicio + 1:]


def revisar(ruta):
    hallazgos = []
    texto = ruta.read_text(encoding='utf-8', errors='replace')
    for m in LLAMADAS.finditer(texto):
        apertura = texto.index('(', m.start())
        arg = argumento_completo(texto, apertura)
        # createStatement() no lleva argumento: es peligroso por sí mismo
        if m.group(1) == 'createStatement':
            linea = texto[:m.start()].count('\n') + 1
            hallazgos.append((linea, 'createStatement() sin parametrizar'))
            continue
        resto = LITERAL.sub('', arg)
        resto = RUIDO.sub('', resto)
        # Lo que sobra debería ser solo '+', comas y espacios
        sobrante = IDENTIFICADOR.findall(resto)
        if sobrante:
            linea = texto[:m.start()].count('\n') + 1
            fragmento = ' '.join(arg.split())[:80]
            hallazgos.append((linea, f'interpola {sobrante[:3]} -> {fragmento}'))
    return hallazgos


def main():
    raiz = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else 'backend/src/main/java')
    total = 0
    for ruta in sorted(raiz.rglob('*.java')):
        for linea, motivo in revisar(ruta):
            print(f'{ruta.as_posix()}:{linea}: {motivo}')
            total += 1
    return 1 if total else 0


if __name__ == '__main__':
    sys.exit(main())
