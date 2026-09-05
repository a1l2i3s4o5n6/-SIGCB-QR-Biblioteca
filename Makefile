# SIGCB-QR — Makefile para reproducibilidad
# Uso: make all | up | down | test | verify | audit | metrics | logs | clean
# La suite es autocontenida: 'make test' no necesita Maven ni JDK instalados.


.PHONY: all up down test logs metrics clean verify audit docs-check

# Reproducción end-to-end en un solo comando.
# Orden: validaciones estáticas -> infraestructura -> esquema -> suite -> auditoría.
all: verify up docs-check test audit
	@echo ""
	@echo "=== Reproducción completa terminada ==="
	@echo "API:      http://localhost:8080"
	@echo "Swagger:  http://localhost:8080/swagger-ui.html"
	@echo "Frontend: http://localhost:8000"

# Levanta la infraestructura y espera a que los healthcheck estén en verde.
up:
	docker compose up --build -d
	@echo "Esperando a que postgres y redis pasen el healthcheck..."
	@for i in $$(seq 1 30); do 		if [ "$$(docker inspect -f '{{.State.Health.Status}}' sigcbqr-postgres 2>/dev/null)" = "healthy" ] 		&& [ "$$(docker inspect -f '{{.State.Health.Status}}' sigcbqr-redis 2>/dev/null)" = "healthy" ]; then 			echo "Infraestructura lista."; exit 0; 		fi; 		sleep 2; 	done; 	echo "ERROR: los contenedores no alcanzaron el estado healthy en 60 s."; 	docker compose ps; exit 1

down:
	docker compose down

# Suite del backend con cobertura. Autocontenida: levanta PostgreSQL y Redis de
# prueba, ejecuta Maven en contenedor y limpia al terminar. No exige Maven ni
# JDK instalados, en coherencia con el unico requisito que declara el README.
# Para ejecutar en local con el wrapper: cd backend && ./mvnw verify
test:
	@bash scripts/run-tests.sh

logs:
	docker compose logs -f

# Comprobaciones que no necesitan el sistema levantado.
verify:
	@echo "=== Digests SHA256 de las imágenes (64 hex) ==="
	@python scripts/validate-digests.py
	@echo ""
	@echo "=== Matriz de trazabilidad (toda prueba citada debe existir) ==="
	@bash scripts/validate-traceability.sh
	@echo ""
	@echo "=== Índice de ADR ==="
	@bash scripts/validate-adr.sh
	@echo ""
	@echo "=== Coherencia de autoría (.mailmap y superficies) ==="
	@python scripts/validate-authors.py
	@echo ""
	@echo "=== Auditoría de SQL dinámico (con autotest del instrumento) ==="
	@bash scripts/audit-sql-dynamic.sh
	@echo ""
	@echo "=== Digest de la entrega ==="
	@python scripts/entrega-digest.py --check

# Comprueba que el diccionario de datos no se haya quedado desfasado.
# Necesita el contenedor de PostgreSQL en marcha.
docs-check:
	@python scripts/generar-diccionario-datos.py --check

# Auditoría de seguridad contra el sistema en marcha. Requiere 'make up'.
audit:
	@bash scripts/owasp-audit.sh

metrics:
	@echo "=== Hit Ratio Redis ==="
	@docker exec sigcbqr-redis redis-cli INFO stats | grep -i hit
	@echo ""
	@echo "=== Healthcheck ==="
	@docker compose ps

clean:
	docker compose down -v
	docker system prune -f
