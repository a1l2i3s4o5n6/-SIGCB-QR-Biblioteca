#!/bin/bash
# run-tests.sh — Ejecuta la suite del backend con cobertura, sin exigir nada
# instalado salvo Docker.
#
# Por qué existe:
# El README promete que el único requisito del proyecto es Docker, pero
# 'make test' invocaba 'mvn' directamente y por tanto exigía Maven y un JDK
# instalados. Quien clonaba el repositorio siguiendo las instrucciones no podía
# ejecutar la suite. Este script cierra esa contradicción: levanta PostgreSQL y
# Redis de prueba, ejecuta Maven dentro de un contenedor y limpia al terminar.
#
# La suite NO se puede ejecutar contra bases de datos simuladas: hay una prueba
# de contexto (SigcbQrApplicationTests) que arranca la aplicación completa y
# aplica las migraciones de Flyway. Por eso hacen falta servicios reales.
#
# Las imágenes van ancladas por digest, los mismos que docker-compose.yml, para
# que la suite se ejecute contra las mismas versiones que el sistema real.
#
# Uso:
#   bash scripts/run-tests.sh              # suite + cobertura
#   bash scripts/run-tests.sh -Dtest=X     # argumentos extra para Maven
#
# Código de salida: el de Maven.

set -uo pipefail

RED="sigcbqr-test-net"
PG="sigcbqr-test-pg"
REDIS="sigcbqr-test-redis"
PG_IMG="postgres:16@sha256:f1c3376c26f2609ab9f29f71f824103fe2fcd8ee0346485cb6122a4f93df6f94"
REDIS_IMG="redis:7@sha256:71da9275c5f3fcb97d0fa0c8c5b36cc995327265420f17a04bfd544f458059f7"
MAVEN_IMG="maven:3.9-eclipse-temurin-21"

# La contraseña de la base de datos de prueba es efímera: se genera aquí, vive
# lo que vive el contenedor y no se escribe en ningún fichero. Antes estaba
# fijada a 'test123' en el Makefile y en la documentación.
PGPASS="$(head -c 18 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')"

# Credenciales de los usuarios semilla (V7): aleatorias por corrida. Ninguna
# prueba inicia sesión contra la BD de prueba con las cuentas semilla, así que
# su valor exacto es irrelevante; solo se exige que existan para que Flyway
# resuelva los placeholders. JWT de prueba: aleatorio, igual que JWT_SECRET.
TEST_JWT_SECRET="$(head -c 64 /dev/urandom | base64 | tr -d '\n')"
SEED_ADMIN_PASSWORD="$(head -c 12 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')"
SEED_BIBLIO_PASSWORD="$(head -c 12 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')"
SEED_STUDENT_PASSWORD="$(head -c 12 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')"

limpiar() {
    echo ""
    echo "Limpiando entorno de prueba..."
    docker rm -f "$PG" "$REDIS" >/dev/null 2>&1
    docker network rm "$RED" >/dev/null 2>&1
    return 0
}
trap limpiar EXIT

echo "============================================================"
echo " Suite del backend — SIGCB-QR"
echo " Fecha: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
echo " Requisito: solo Docker (Maven y el JDK van en contenedor)"
echo "============================================================"

if ! docker info >/dev/null 2>&1; then
    echo "ERROR: Docker no responde. Arranca Docker Desktop y reintenta."
    exit 1
fi

# Restos de una ejecución anterior interrumpida.
docker rm -f "$PG" "$REDIS" >/dev/null 2>&1
docker network rm "$RED" >/dev/null 2>&1
docker network create "$RED" >/dev/null

echo ""
echo "1/3  Levantando PostgreSQL y Redis de prueba..."
docker run -d --name "$PG" --network "$RED" \
    -e POSTGRES_DB=sigcbqr_test \
    -e POSTGRES_USER=postgres \
    -e POSTGRES_PASSWORD="$PGPASS" \
    --health-cmd="pg_isready -U postgres" \
    --health-interval=3s --health-timeout=5s --health-retries=20 \
    "$PG_IMG" >/dev/null

docker run -d --name "$REDIS" --network "$RED" \
    --health-cmd="redis-cli ping" \
    --health-interval=3s --health-timeout=5s --health-retries=20 \
    "$REDIS_IMG" >/dev/null

echo -n "     Esperando healthcheck"
for _ in $(seq 1 40); do
    epg=$(docker inspect -f '{{.State.Health.Status}}' "$PG" 2>/dev/null)
    ere=$(docker inspect -f '{{.State.Health.Status}}' "$REDIS" 2>/dev/null)
    if [ "$epg" = "healthy" ] && [ "$ere" = "healthy" ]; then
        echo " listo."
        break
    fi
    echo -n "."
    sleep 2
done
if [ "$epg" != "healthy" ] || [ "$ere" != "healthy" ]; then
    echo ""
    echo "ERROR: los servicios de prueba no arrancaron (pg=$epg, redis=$ere)."
    exit 1
fi

echo ""
echo "2/3  Ejecutando la suite en $MAVEN_IMG ..."
echo ""

# El volumen sigcbqr-m2 cachea las dependencias entre ejecuciones: sin él, cada
# corrida vuelve a descargar el repositorio de Maven entero.
# Ojo con la precedencia: 'A && B || C && D' se agrupa como '((A && B) || C) && D',
# de modo que escribir 'pwd -W || pwd' aqui ejecutaria AMBOS y concatenaria las
# dos rutas. Se resuelve con un if explicito.
if RUTA_BACKEND="$(cd backend && pwd -W 2>/dev/null)" && [ -n "$RUTA_BACKEND" ]; then
    :   # Git Bash en Windows: pwd -W da la ruta nativa que Docker entiende
else
    RUTA_BACKEND="$(cd backend && pwd)"
fi

MSYS_NO_PATHCONV=1 docker run --rm --network "$RED" \
    -v "$RUTA_BACKEND":/app \
    -v sigcbqr-m2:/root/.m2 \
    -w /app \
    -e SPRING_DATASOURCE_URL="jdbc:postgresql://$PG:5432/sigcbqr_test" \
    -e SPRING_DATASOURCE_USERNAME=postgres \
    -e SPRING_DATASOURCE_PASSWORD="$PGPASS" \
    -e TEST_DATASOURCE_URL="jdbc:postgresql://$PG:5432/sigcbqr_test" \
    -e TEST_DATASOURCE_USERNAME=postgres \
    -e TEST_DATASOURCE_PASSWORD="$PGPASS" \
    -e TEST_JWT_SECRET="$TEST_JWT_SECRET" \
    -e SEED_ADMIN_PASSWORD="$SEED_ADMIN_PASSWORD" \
    -e SEED_BIBLIO_PASSWORD="$SEED_BIBLIO_PASSWORD" \
    -e SEED_STUDENT_PASSWORD="$SEED_STUDENT_PASSWORD" \
    -e REDIS_HOST="$REDIS" \
    -e JWT_SECRET="$(head -c 64 /dev/urandom | base64 | tr -d '\n')" \
    "$MAVEN_IMG" mvn -B clean verify "$@"
CODIGO=$?

echo ""
echo "3/3  Informe de cobertura:"
if [ -f backend/target/site/jacoco/jacoco.csv ]; then
    echo "     backend/target/site/jacoco/index.html"
    echo ""
    python - <<'EOF' 2>/dev/null || true
import csv, collections
t = collections.Counter()
for r in csv.DictReader(open('backend/target/site/jacoco/jacoco.csv')):
    for k in r:
        if k.endswith(('_MISSED', '_COVERED')):
            t[k] += int(r[k])
for c in ['INSTRUCTION', 'BRANCH', 'LINE']:
    m, cv = t[c + '_MISSED'], t[c + '_COVERED']
    if m + cv:
        print(f'     {c:12s} {cv:5d}/{m+cv:5d} = {100*cv/(m+cv):5.2f} %')
EOF
    echo ""
    echo "     Para publicar estas cifras, copia el crudo:"
    echo "       cp backend/target/site/jacoco/jacoco.{csv,xml} docs/mediciones/cobertura/"
else
    echo "     (no se generó informe de JaCoCo)"
fi

exit $CODIGO
