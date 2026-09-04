#!/bin/sh
set -e

# Esperar a que la BD esté lista (SQLite siempre lo está)
cd /var/www/html


# Exponer storage/public (avatares subidos) en el web root
php artisan storage:link --force 2>/dev/null || true

# Iniciar Apache en primer plano
exec apache2-foreground
