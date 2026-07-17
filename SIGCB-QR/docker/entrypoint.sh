#!/bin/sh
set -e

# Esperar a que la BD esté lista (SQLite siempre lo está)
cd /var/www/html

# Migrar y sembrar la base de datos
php artisan migrate --force
php artisan db:seed --force

# Iniciar Apache en primer plano
exec apache2-foreground
