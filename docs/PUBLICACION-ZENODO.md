# Archivo permanente y DOI en Zenodo

**Estado:** procedimiento preparado; **el depósito aún no se ha publicado y por
tanto no hay DOI asignado.**

Este documento describe cómo obtener el archivo permanente y el DOI que la guía de
la entrega exige. Se declara el estado con precisión porque `CITATION.cff` no
puede llevar un DOI inventado: un identificador que no resuelve convierte la cita
en falsa.

---

## Qué queda listo en el repositorio

| Artefacto | Función |
|---|---|
| `.zenodo.json` | Metadatos del depósito: título, descripción, autoría, licencia, versión y palabras clave. Zenodo los lee automáticamente al archivar. |
| `CITATION.cff` | Metadatos de cita legibles por GitHub y por gestores bibliográficos. Contiene el hueco exacto donde va el DOI y las instrucciones para rellenarlo. |
| `LICENSE` | MIT. Zenodo exige una licencia declarada para el acceso abierto. |
| `CHANGELOG.md` | Historial de versiones, para que cada versión archivada sea identificable. |

## Procedimiento

### 1. Requisitos previos

- Repositorio **público** en GitHub.
- Cuenta de Zenodo enlazada con GitHub (se puede iniciar sesión con la cuenta de
  GitHub).

### 2. Activar el repositorio en Zenodo

1. Entrar en <https://zenodo.org/account/settings/github/>.
2. Localizar el repositorio en la lista y poner su interruptor en **ON**.

Este paso instala un *webhook*: a partir de aquí, **cada publicación (*release*)
de GitHub genera automáticamente una versión archivada en Zenodo**. Las etiquetas
anteriores a la activación no se archivan retroactivamente.

### 3. Publicar la versión

```bash
git tag -a v1.0.0 -m "Entrega Final: evidencia empírica, ADR, ética y licencia"
git push origin v1.0.0
```

Después, en GitHub: **Releases → Draft a new release**, elegir la etiqueta
`v1.0.0`, poner como notas la sección `[1.0.0-rc]` de `CHANGELOG.md` y publicar.

En minutos, Zenodo archiva un `.zip` del repositorio en ese commit exacto y asigna
los DOI.

### 4. Anotar los dos DOI

Zenodo emite **dos**, y conviene no confundirlos:

| DOI | Apunta a | Dónde usarlo |
|---|---|---|
| **De concepto** | Siempre a la versión más reciente | `CITATION.cff`, README, informe técnico |
| **De versión** | A `v1.0.0` y sólo a ella | Cuando se cite exactamente lo entregado |

Para un trabajo evaluado, el DOI **de versión** es el que garantiza que quien
evalúa ve lo mismo que se entregó.

### 5. Completar `CITATION.cff`

Añadir, según indica el comentario del propio archivo:

```yaml
identifiers:
  - type: doi
    value: 10.5281/zenodo.XXXXXXX
    description: "DOI de concepto (todas las versiones)"
  - type: doi
    value: 10.5281/zenodo.YYYYYYY
    description: "DOI de la versión v1.0.0"
```

Y la insignia en el README:

```markdown
[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.XXXXXXX.svg)](https://doi.org/10.5281/zenodo.XXXXXXX)
```

### 6. Comprobar

- `https://doi.org/10.5281/zenodo.XXXXXXX` resuelve al depósito.
- La licencia figura como MIT y el acceso como abierto.
- El `.zip` archivado contiene `docs/`, `scripts/` y las salidas crudas de
  `docs/mediciones/`, que son lo que hace reproducible la evidencia.

## Qué queda fuera del archivo

Por diseño, el `.zip` de Zenodo **no** incluye:

- Las imágenes de contenedor. Su reproducibilidad la garantizan los digest SHA256
  de `docker-compose.yml` (ADR-0007), no el archivo.
- Las dependencias de Maven, Composer y npm. Están fijadas por `pom.xml`,
  `composer.lock` y `package-lock.json`.
- Los informes HTML de cobertura, que se regeneran con `mvn verify`.

Sí se incluyen los informes JSON y HTML de Lighthouse y las salidas de k6 y de la
auditoría OWASP, porque son **datos de medición**: no se pueden regenerar
idénticos y son la evidencia que respalda las cifras del informe.
