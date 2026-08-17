#!/bin/sh
set -e

# Esperar a que la BD esté lista (SQLite siempre lo está)
cd /var/www/html


# Iniciar Apache en primer plano
exec apache2-foreground
