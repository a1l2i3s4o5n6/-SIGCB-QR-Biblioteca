# SIGCB-QR — Makefile para reproducibilidad
# Uso: make up | make down | make test | make logs | make metrics

.PHONY: up down test logs metrics clean

up:
	docker compose up --build -d
	@echo "Sistema operativo. API: http://localhost:8080, Swagger: http://localhost:8080/swagger-ui.html, Frontend: http://localhost:8000"

down:
	docker compose down

test:
	cd sigcb-qr-api && mvn clean test

logs:
	docker compose logs -f

metrics:
	@echo "=== Hit Ratio Redis ==="
	@docker exec sigcbqr-redis redis-cli INFO stats | grep -i hit
	@echo ""
	@echo "=== Healthcheck ==="
	@docker compose ps

clean:
	docker compose down -v
	docker system prune -f
