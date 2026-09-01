# SIGCB-QR — Makefile para reproducibilidad
# Uso: make up | down | test | verify | audit | metrics | logs | clean

.PHONY: up down test logs metrics clean verify audit docs-check

up:
	docker compose up --build -d
	@echo "Sistema operativo. API: http://localhost:8080, Swagger: http://localhost:8080/swagger-ui.html, Frontend: http://localhost:8000"

down:
	docker compose down

test:
	cd backend && mvn clean verify

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
