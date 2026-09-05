# Rendimiento — perfil de 50 VU sostenidos 30 s

**Fecha de medición:** 2026-09-03
**Requisito asociado:** REQ-R-002 (p95 del catálogo < 200 ms)
**Herramienta:** k6 (imagen `grafana/k6`), ejecutada dentro de la red de la
composición para no medir el salto de red del anfitrión.
**Endpoint medido:** `GET /api/libros`, autenticado.
**Crudos:** `k6-50vu-run1.txt` … `k6-50vu-run5.txt`, en este mismo directorio.

---

## 1. Por qué existe esta carpeta y no se corrigió la anterior

La medición anterior (`docs/mediciones/perf/`) usaba un perfil de **5 a 20
usuarios virtuales en 40 segundos**, y la guía exige **50 sostenidos durante 30
segundos**. Además solo se conservaron tres crudos de las cuatro corridas: de la
primera no se guardó la salida.

No se sobrescribió aquella medición: describe un experimento distinto y sus
cifras son correctas para el perfil que usó. Se conserva, y esta carpeta añade
la medición con el perfil exigido. Sustituir una medición por otra y presentar
solo la favorable sería justamente lo que este proyecto declara no hacer.

## 2. Protocolo

- Rampa de subida de 15 s hasta 50 VU.
- **Tramo sostenido de 30 s a 50 VU**, que es el que se informa.
- Descenso de 10 s.
- Cinco corridas independientes, con 20 s de reposo entre ellas.
- La sesión se obtiene una sola vez por corrida, en `setup()`, y **queda fuera**
  de la métrica `catalogo_duracion`: si se incluyera, la latencia del login
  contaminaría la del catálogo.

Reproducción:

```bash
docker run --rm --network sigcb-qr-biblioteca_default \
  -v "$PWD/scripts":/s \
  -e BASE_URL=http://api:8080 \
  -e SIGCB_EMAIL=admin@biblioteca.com -e SIGCB_PASSWORD=<contraseña> \
  grafana/k6 run /s/k6-load-test.js
```

## 3. Resultados por corrida

| Corrida | VU máx. | Iteraciones | Peticiones | Media | Mediana | p95 | Errores |
|---|---:|---:|---:|---:|---:|---:|---:|
| run1 | 50 | 2 150 | 2 151 | 3,93 ms | 3,64 ms | 5,56 ms | 0 |
| run2 | 50 | 2 150 | 2 151 | 3,35 ms | 3,21 ms | 4,72 ms | 0 |
| run3 | 50 | 2 150 | 2 151 | 2,79 ms | 2,56 ms | 4,66 ms | 0 |
| run4 | 50 | 2 150 | 2 151 | 3,99 ms | 2,95 ms | 7,58 ms | 0 |
| run5 | 50 | 2 150 | 2 151 | 3,19 ms | 2,85 ms | 5,23 ms | 0 |

**Totales:** 10 750 iteraciones, 10 755 peticiones, **0 fallidas**,
21 500/21 500 comprobaciones superadas.

## 4. Estadística

Con cinco repeticiones ya es posible dar un intervalo de confianza, cosa que la
medición anterior no permitía y declaraba no permitir.

| Métrica | Media | Desv. típica | IC 95 % |
|---|---:|---:|---|
| p95 | 5,55 ms | 1,19 | **[4,07, 7,03] ms** |
| Media | 3,45 ms | 0,51 | [2,82, 4,08] ms |
| Mediana | 3,04 ms | 0,41 | [2,54, 3,55] ms |

El intervalo es el de la media por la **t de Student** con 4 grados de libertad
(t = 2,776). Se recalcula con:

```bash
python scripts/analisis-k6-50vu.py
```

### Qué se puede y qué no se puede afirmar

**Sí:** el p95 del catálogo bajo 50 usuarios concurrentes está, con un 95 % de
confianza, entre 4,07 y 7,03 ms. El umbral de REQ-R-002 son 200 ms, de modo que
el requisito se cumple **por un margen de más de un orden de magnitud**, y el
límite superior del intervalo sigue estando 28 veces por debajo del umbral.

**No:** no se aplica ninguna prueba inferencial de comparación, porque **no hay
dos grupos que comparar**: las cinco corridas son del mismo tratamiento. Un
contraste de hipótesis exigiría medir también una condición alternativa (por
ejemplo, con la caché deshabilitada), y eso no se hizo. Tampoco se informa
tamaño de efecto, por la misma razón: no hay efecto entre condiciones que
cuantificar.

### Una advertencia sobre lo cómodo de estas cifras

Un p95 de 5,55 ms con 50 usuarios concurrentes es un resultado excelente, y por
eso mismo conviene decir de qué depende: **el acervo es sintético y pequeño**.
La consulta se resuelve casi íntegramente desde memoria, y estas cifras no
autorizan a predecir el comportamiento con un catálogo real de decenas de miles
de títulos. Es la misma limitación que impidió, en la medición anterior,
afirmar que Redis mejorase el rendimiento.

## 5. Amenazas a la validez de esta medición

- **Validez de constructo.** Se mide un único endpoint de lectura. `REQ-R-001`
  (login) y `REQ-R-003` (préstamo) siguen sin medir: el arnés no los ejercita.
- **Validez interna.** Cliente y servidor comparten máquina y red Docker, así
  que no hay latencia de red real. La medición acota el coste del servidor, no
  la experiencia de un usuario remoto.
- **Validez externa.** Acervo sintético y pequeño; véase el apartado anterior.
- **Validez de conclusión.** n = 5 es suficiente para un intervalo de confianza,
  pero es una muestra pequeña: el intervalo del p95 es ancho en términos
  relativos (±27 % de la media).
