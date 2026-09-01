# Informe técnico — SIGCB-QR

| Archivo | Contenido |
|---|---|
| `informe-tecnico.pdf` | **Informe compilado** (13 páginas) |
| `informe-tecnico.tex` | Fuente LaTeX |
| `referencias.bib` | Bibliografía: 29 entradas, todas citadas |

## Compilar

Con TeX Live instalado:

```bash
latexmk -pdf informe-tecnico.tex
```

Sin instalar nada, en contenedor (misma versión con la que se generó el PDF que
se entrega):

```bash
docker run --rm -v "$PWD":/doc -w /doc texlive/texlive:latest-small \
  latexmk -pdf -interaction=nonstopmode -halt-on-error informe-tecnico.tex
```

`latexmk` encadena `pdflatex`, `bibtex` y las pasadas necesarias para resolver
índice y citas. La compilación de la versión entregada terminó con **0 citas sin
definir** y **0 referencias sin resolver**.

## Contenido

1. Introducción: contexto, objetivos.
2. Arquitectura: patrón BFF y las nueve decisiones registradas, con su coste.
3. Método empírico: diseño, reglas de reporte, entorno, reproducibilidad.
4. Bloque 1 — Auditoría de seguridad OWASP (42 comprobaciones).
5. Bloque 2 — Pruebas y cobertura (41 pruebas; JaCoCo).
6. Bloque 3 — Rendimiento (cuatro corridas de k6).
7. Bloque 4 — Calidad del frontend (Lighthouse).
8. Defectos encontrados: los tres, con su análisis de por qué las pruebas no los vieron.
9. **Amenazas a la validez**: constructo, interna, externa y de conclusión.
10. Ética y tratamiento de datos.
11. Conclusiones y trabajo futuro.

## Relación con el resto de la documentación

El informe **no duplica** la documentación del repositorio: la cita. Los datos
crudos están en `docs/mediciones/`, las decisiones en `docs/adr/`, la
trazabilidad en `docs/trazabilidad/matriz.csv` y las consideraciones éticas
completas en `ETHICS.md`.

## Nota sobre el informe anterior

`docs/INFORME_TECNICO_PFC_LATEX/INFORME_TECNICO_PFC.tex` es el documento de la
Tercera Entrega: una concatenación generada con Pandoc del SRS y los casos de uso,
sin bibliografía ni apartado de validez. Se conserva como material de requisitos.
El informe técnico de la Entrega Final es **este**.
