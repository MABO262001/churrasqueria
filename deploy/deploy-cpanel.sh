#!/usr/bin/env bash

set -euo pipefail

APP_DIR="$HOME/proyecto2_app"
PUBLIC_DIR="$HOME/proyecto2"

cd "$APP_DIR"

echo "Actualizando código..."
git pull

echo "Usando .env.example como .env..."
if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$APP_DIR/.env.backup_$(date +%Y%m%d_%H%M%S)"
fi

cp "$APP_DIR/.env.example" "$APP_DIR/.env"

echo "Instalando dependencias PHP..."
composer install --no-dev --optimize-autoloader --ignore-platform-req=php

echo "Preparando carpetas Laravel..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache

echo "Limpiando caché Laravel..."
php artisan optimize:clear

echo "Compilando frontend..."
npm install
npm run build

echo "Sincronizando public hacia carpeta real de cPanel..."
mkdir -p "$PUBLIC_DIR"

rsync -av --delete --exclude='storage' "$APP_DIR/public/" "$PUBLIC_DIR/"

echo "Copiando index.php especial para cPanel..."
cp "$APP_DIR/deploy/cpanel-index.php" "$PUBLIC_DIR/index.php"

echo "Copiando .htaccess..."
cp "$APP_DIR/public/.htaccess" "$PUBLIC_DIR/.htaccess"

chmod 644 "$PUBLIC_DIR/index.php"
chmod 644 "$PUBLIC_DIR/.htaccess"

echo "Revisando storage público..."
if [ ! -e "$PUBLIC_DIR/storage" ]; then
    ln -s "$APP_DIR/storage/app/public" "$PUBLIC_DIR/storage"
    echo "Storage enlazado correctamente."
else
    echo "Storage ya existe, no se modifica para evitar problemas de permisos."
fi

echo "Cacheando Laravel..."
php artisan config:cache
php artisan route:cache || true
php artisan view:cache

echo "Deploy terminado."
echo "URL: https://www.tecnoweb.org.bo/inf513/grupo17sc/proyecto2"
