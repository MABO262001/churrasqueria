#!/usr/bin/env bash

set -e

APP_DIR="$HOME/proyecto2_app"
PUBLIC_DIR="$HOME/public_html/inf513/grupo17sc/proyecto2"

cd "$APP_DIR"

echo "Actualizando código..."
git pull

echo "Instalando dependencias PHP..."
composer install --no-dev --optimize-autoloader --ignore-platform-req=php

echo "Preparando Laravel..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache

php artisan optimize:clear

echo "Compilando frontend..."
npm install
npm run build

echo "Sincronizando public..."
mkdir -p "$PUBLIC_DIR"
rsync -av --delete "$APP_DIR/public/" "$PUBLIC_DIR/"

echo "Corrigiendo index.php publico..."
cp "$APP_DIR/deploy/cpanel-index.php" "$PUBLIC_DIR/index.php"

echo "Corrigiendo storage publico..."
rm -rf "$PUBLIC_DIR/storage"
ln -s "$APP_DIR/storage/app/public" "$PUBLIC_DIR/storage" || true

echo "Cacheando Laravel..."
php artisan config:cache
php artisan view:cache

php artisan route:cache || true

echo "Deploy terminado."
