# Informe técnico — SIGCB-QR

| Archivo | Contenido |
|---|---|
| `informe-tecnico.pdf` | **Informe compilado** |
| `informe-tecnico.tex` | Fuente LaTeX |
| `referencias.bib` | Bibliografía |

## Compilar

Con TeX Live instalado:

```bash
latexmk -pdf informe-tecnico.tex
```

Sin instalar nada, en contenedor (misma versión con la que se generó el PDF que
se entrega):

```bash
# Se monta docs/ entero, no solo docs/informe: el informe incluye los
# diagramas C4 de ../diagrams/ y la portada lee el commit y el digest
# generados en ../caratula/.
docker run --rm -v "$(cd ../.. && pwd)/docs":/docs -w /docs/informe texlive/texlive:latest-small \
  latexmk -pdf -interaction=nonstopmode -halt-on-error informe-tecnico.tex
```

`latexmk` encadena `pdflatex`, `bibtex` y las pasadas necesarias para resolver
índice y citas. La compilación terminó con **0 citas sin definir** y **0
referencias sin resolver**.

También se puede compilar con `pdflatex` + `bibtex` + dos pasadas (ver el
comentario cabecera del `.tex` y el README raíz).

## Contenido

1. Resumen / Abstract (español + inglés) y palabras clave.
2. Introducción: contexto, problema y objetivos.
3. Marco Teórico: arquitectura web, REST, JWT, códigos QR y caché Redis.
4. Trabajos Relacionados: estrategia PRISMA, tabla comparativa (8 filas) y brecha.
5. Metodología: preguntas de investigación (RQ), DSR de Peffers, GQM, muestreo.
6. Arquitectura: patrón BFF y las nueve decisiones registradas, con su coste.
7. Bloque 1 — Auditoría de seguridad OWASP (42 comprobaciones).
8. Bloque 2 — Pruebas y cobertura (41 pruebas; JaCoCo).
9. Bloque 3 — Rendimiento (cuatro corridas de k6).
10. Bloque 4 — Calidad del frontend (Lighthouse).
11. Defectos encontrados: los tres, con su análisis de por qué las pruebas no los vieron.
12. **Amenazas a la validez**: constructo, interna, externa y de conclusión.
13. Ética y tratamiento de datos.
14. Conclusiones y trabajo futuro.

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
