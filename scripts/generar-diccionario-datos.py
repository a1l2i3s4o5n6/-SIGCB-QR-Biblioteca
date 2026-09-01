#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Genera docs/basedatos/DICCIONARIO-DATOS.md a partir del esquema REAL de la base
de datos en marcha, no de una transcripción a mano.

Consulta information_schema y pg_catalog a través de `docker exec` sobre el
contenedor de PostgreSQL, de modo que el diccionario nunca puede divergir del
esquema que Flyway aplicó (ADR-0008).

Uso:
    python scripts/generar-diccionario-datos.py            # escribe el .md
    python scripts/generar-diccionario-datos.py --check    # solo verifica que esté al día
"""
import re
import subprocess
import sys
import textwrap
from collections import OrderedDict, defaultdict
from datetime import datetime, timezone
from pathlib import Path

CONTENEDOR = "sigcbqr-postgres"
BASE = "sigcbqr"
USUARIO = "postgres"
SALIDA = Path("docs/basedatos/DICCIONARIO-DATOS.md")

# Tablas de infraestructura que no forman parte del modelo de dominio.
EXCLUIDAS = {"flyway_schema_history"}

# Glosario de negocio: qué significa cada tabla en el dominio bibliotecario.
# Es lo único que no puede deducirse del esquema y por eso se declara aquí.
PROPOSITO = {
    "usuarios": "Personas que usan el sistema: estudiantes, bibliotecarios y administradores.",
    "roles": "Perfiles de autorización. Determinan a qué endpoints puede llamar un usuario.",
    "facultades": "Unidades académicas de la universidad.",
    "carreras": "Programas académicos, cada uno adscrito a una facultad.",
    "libros": "Obra bibliográfica como título (la obra, no el ejemplar físico).",
    "autores": "Autores de las obras. Relación N:M con libros.",
    "libro_autores": "Tabla puente entre libros y autores.",
    "editoriales": "Sellos editoriales de las obras.",
    "categorias": "Clasificación temática del acervo.",
    "inventario": "Ejemplar físico concreto de un libro. Es lo que se presta.",
    "prestamos": "Entrega de un ejemplar a un usuario, con fecha de vencimiento y devolución.",
    "reservas": "Solicitud anticipada de un libro cuando no hay ejemplar disponible.",
    "multas": "Sanción económica derivada de una devolución tardía.",
    "sanciones": "Restricción no económica aplicada a un usuario (suspensión temporal).",
    "qr_codigos": "Código QR asociado a un libro para su identificación rápida.",
    "notificaciones": "Avisos dirigidos a un usuario (vencimientos, reservas disponibles).",
    "configuracion": "Parámetros del sistema editables sin desplegar (p. ej. monto de multa diaria).",
    "auditoria": "Registro de acciones sobre el sistema: quién, qué, cuándo y desde dónde.",
    "jwt_blacklist": "Identificadores (jti) de tokens revocados en el cierre de sesión (ADR-0009).",
}

# Columnas cuyo tratamiento tiene implicaciones de privacidad (ver ETHICS.md).
SENSIBLES = {
    ("usuarios", "password"): "Hash bcrypt con salt único. Nunca se devuelve por la API.",
    ("usuarios", "email"): "Dato personal identificativo. Es además la credencial de acceso.",
    ("usuarios", "telefono"): "Dato personal de contacto.",
    ("auditoria", "ip"): "Dirección IP del cliente. Dato personal; sin cifrado en reposo.",
    ("auditoria", "equipo"): "Agente de usuario del cliente. Contribuye a la huella del navegador.",
    ("prestamos", "usuario_id"): "Vincula a una persona con lo que lee. Alta sensibilidad.",
    ("sanciones", "motivo"): "Texto libre sobre la conducta de una persona.",
}


def psql(consulta):
    """Ejecuta una consulta y devuelve las filas ya troceadas por '|'."""
    salida = subprocess.run(
        ["docker", "exec", CONTENEDOR, "psql", "-U", USUARIO, "-d", BASE,
         "-At", "-F", "|", "-c", textwrap.dedent(consulta)],
        capture_output=True, text=True, encoding="utf-8",
    )
    if salida.returncode != 0:
        sys.exit(f"ERROR al consultar la base:\n{salida.stderr}")
    return [linea.split("|") for linea in salida.stdout.strip().splitlines() if linea]


def tipo_legible(tipo, longitud, precision, escala):
    if longitud:
        return f"{tipo}({longitud})"
    if tipo == "numeric" and precision:
        return f"numeric({precision},{escala})"
    return {
        "timestamp without time zone": "timestamp",
        "character varying": "varchar",
        "double precision": "float8",
    }.get(tipo, tipo)


def recoger():
    columnas = defaultdict(list)
    for (tabla, _pos, col, tipo, longitud, precision, escala, nulo, defecto) in psql("""
        SELECT c.table_name, c.ordinal_position, c.column_name, c.data_type,
               coalesce(c.character_maximum_length::text,''),
               coalesce(c.numeric_precision::text,''),
               coalesce(c.numeric_scale::text,''),
               c.is_nullable, coalesce(c.column_default,'')
        FROM information_schema.columns c
        JOIN information_schema.tables t
          ON t.table_name = c.table_name AND t.table_schema = c.table_schema
        WHERE c.table_schema='public' AND t.table_type='BASE TABLE'
        ORDER BY c.table_name, c.ordinal_position;
    """):
        if tabla in EXCLUIDAS:
            continue
        columnas[tabla].append({
            "nombre": col,
            "tipo": tipo_legible(tipo, longitud, precision, escala),
            "nulo": nulo == "YES",
            "defecto": defecto,
        })

    restricciones = defaultdict(list)
    for fila in psql("""
        SELECT rel.relname, con.conname, con.contype,
               pg_get_constraintdef(con.oid)
        FROM pg_constraint con
        JOIN pg_class rel ON rel.oid = con.conrelid
        JOIN pg_namespace ns ON ns.oid = rel.relnamespace
        WHERE ns.nspname = 'public'
        ORDER BY rel.relname, con.contype DESC, con.conname;
    """):
        tabla, nombre, tipo, definicion = fila[0], fila[1], fila[2], "|".join(fila[3:])
        if tabla in EXCLUIDAS:
            continue
        restricciones[tabla].append({"nombre": nombre, "tipo": tipo, "def": definicion})

    indices = defaultdict(list)
    for fila in psql("""
        SELECT tablename, indexname, indexdef
        FROM pg_indexes
        WHERE schemaname='public'
        ORDER BY tablename, indexname;
    """):
        tabla, nombre, definicion = fila[0], fila[1], "|".join(fila[2:])
        if tabla in EXCLUIDAS:
            continue
        indices[tabla].append({"nombre": nombre, "def": definicion})

    filas = {}
    for tabla in columnas:
        filas[tabla] = psql(f'SELECT count(*) FROM "{tabla}";')[0][0]

    return OrderedDict(sorted(columnas.items())), restricciones, indices, filas


def clave_primaria(restricciones_tabla):
    for r in restricciones_tabla:
        if r["tipo"] == "p":
            return r["def"]
    return ""


def render(columnas, restricciones, indices, filas):
    ahora = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")
    version = psql("SELECT version();")[0][0].split(",")[0]
    out = []
    A = out.append

    A("# Diccionario de datos — SIGCB-QR\n")
    A("> **Generado automáticamente** por `scripts/generar-diccionario-datos.py` a")
    A("> partir del esquema real de la base en marcha. No editar a mano: los cambios")
    A("> se pierden en la siguiente generación. Para modificar el esquema, añadir una")
    A("> migración de Flyway (ADR-0008) y volver a ejecutar el generador.\n")
    A(f"- **Generado el:** {ahora}")
    A(f"- **Motor:** {version}")
    A(f"- **Base:** `{BASE}`, esquema `public`")
    A(f"- **Tablas de dominio:** {len(columnas)} (se excluye `flyway_schema_history`)")
    A("- **Origen del esquema:** migraciones `V1`–`V10` en "
      "`backend/src/main/resources/db/migration/`\n")
    A("Los recuentos de filas corresponden al conjunto de datos **sintético** de la")
    A("semilla (`V3__datos_semilla.sql`). No hay datos de personas reales; ver")
    A("`ETHICS.md`.\n")

    A("## Índice de tablas\n")
    A("| Tabla | Propósito | Columnas | Filas |")
    A("|---|---|---:|---:|")
    for tabla, cols in columnas.items():
        A(f"| [`{tabla}`](#{tabla}) | {PROPOSITO.get(tabla, '—')} | {len(cols)} | {filas[tabla]} |")
    A("")

    A("## Convenciones\n")
    A("- Toda tabla de dominio usa una clave primaria sustituta `id BIGSERIAL`.")
    A("- Las marcas temporales `created_at` / `updated_at` son `timestamp` sin zona,")
    A("  con `CURRENT_TIMESTAMP` por defecto; la aplicación trabaja en la zona")
    A("  `America/Guayaquil`.")
    A("- Las bajas son **lógicas**: la columna `activo` se pone en `false` y la fila")
    A("  se conserva, para no romper el historial de préstamos.")
    A("- Los nombres de tabla van en plural y en minúsculas; los de columna, en")
    A("  `snake_case`.\n")

    A("## Datos personales\n")
    A("Columnas con implicaciones de privacidad, según `ETHICS.md`:\n")
    A("| Tabla | Columna | Consideración |")
    A("|---|---|---|")
    for (tabla, col), nota in sorted(SENSIBLES.items()):
        A(f"| `{tabla}` | `{col}` | {nota} |")
    A("")

    A("---\n")
    for tabla, cols in columnas.items():
        A(f"## `{tabla}`\n")
        A(f"{PROPOSITO.get(tabla, '_Sin descripción de negocio registrada._')}\n")
        pk = clave_primaria(restricciones.get(tabla, []))
        if pk:
            A(f"**Clave primaria:** `{pk}`\n")

        A("| # | Columna | Tipo | Nulo | Por defecto | Notas |")
        A("|---:|---|---|:---:|---|---|")
        for i, c in enumerate(cols, 1):
            defecto = f"`{c['defecto']}`" if c["defecto"] else "—"
            if "nextval" in c["defecto"]:
                defecto = "autoincremental"
            nota = SENSIBLES.get((tabla, c["nombre"]), "")
            A(f"| {i} | `{c['nombre']}` | `{c['tipo']}` | {'sí' if c['nulo'] else 'no'} | {defecto} | {nota} |")
        A("")

        fks = [r for r in restricciones.get(tabla, []) if r["tipo"] == "f"]
        if fks:
            A("**Claves foráneas**\n")
            for r in fks:
                A(f"- `{r['nombre']}`: {r['def']}")
            A("")

        otras = [r for r in restricciones.get(tabla, []) if r["tipo"] in ("u", "c")]
        if otras:
            A("**Restricciones de unicidad y comprobación**\n")
            for r in otras:
                A(f"- `{r['nombre']}`: {r['def']}")
            A("")

        idx = [r for r in indices.get(tabla, []) if not r["nombre"].endswith("_pkey")]
        if idx:
            A("**Índices**\n")
            for r in idx:
                A(f"- `{r['nombre']}`: `{r['def'].split(' USING ')[-1]}`")
            A("")
        A("---\n")

    A("## Objetos programables\n")
    A("Los procedimientos y funciones almacenados están catalogados aparte, en")
    A("`docs/basedatos/CATALOGO-SP.md`. El criterio para decidir qué va por ORM y qué")
    A("va por procedimiento se documenta en ADR-0005.\n")
    return "\n".join(out) + "\n"


def main():
    columnas, restricciones, indices, filas = recoger()
    contenido = render(columnas, restricciones, indices, filas)

    if "--check" in sys.argv:
        if not SALIDA.exists():
            sys.exit(f"ERROR: falta {SALIDA}. Ejecute el generador.")
        actual = SALIDA.read_text(encoding="utf-8")

        # --check verifica el ESQUEMA, no los datos. Dos cosas cambian en cada
        # ejecución sin que el esquema se haya tocado: la marca de tiempo y el
        # recuento de filas, que sube en cuanto alguien usa el sistema (la
        # auditoría OWASP, por ejemplo, escribe en `auditoria` y en
        # `jwt_blacklist`). Compararlas haría fallar la comprobación por motivos
        # que no interesan y acabaría enseñando a ignorarla.
        def normalizar(texto):
            lineas = []
            for linea in texto.splitlines():
                if linea.startswith("- **Generado el:**"):
                    continue
                # Última celda de las filas del índice: el recuento de filas.
                lineas.append(re.sub(r"\|\s*\d+\s*\|$", "| N |", linea))
            return lineas

        if normalizar(actual) != normalizar(contenido):
            sys.exit(f"ERROR: {SALIDA} no refleja el esquema actual. "
                     f"Ejecute: python {sys.argv[0]}")
        print(f"OK: {SALIDA} está al día respecto al esquema.")
        return

    SALIDA.parent.mkdir(parents=True, exist_ok=True)
    SALIDA.write_text(contenido, encoding="utf-8")
    print(f"Escrito {SALIDA} ({len(columnas)} tablas, "
          f"{sum(len(c) for c in columnas.values())} columnas).")


if __name__ == "__main__":
    main()
