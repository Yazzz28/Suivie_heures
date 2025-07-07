#!/bin/sh

echo "📡 Checking network connectivity..."
ping -c 1 mysql || echo "⚠️ Cannot ping mysql"

# Construire la DATABASE_URL (pour Symfony par exemple)
export DATABASE_URL="mysql://${DB_USERNAME}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_DATABASE}?serverVersion=8.0.32&charset=utf8mb4"

# Attendre que MySQL soit prêt
echo "⏳ Waiting for MySQL to be ready..."
max_tries=30
count=0
while [ $count -lt $max_tries ]; do
  if mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; then
    echo "✅ MySQL is ready!"
    break
  fi
  echo "🔁 Waiting for MySQL..."
  sleep 2
  count=$((count + 1))
done

# Corriger les permissions pour www-data
echo "🔧 Fixing permissions on var/ and log/"
mkdir -p var/log
chown -R www-data:www-data var
chmod -R ug+rwX var

# Nettoyer et recréer le dossier cache
echo "🧹 Cleaning previous cache..."
rm -rf var/cache
mkdir -p var/cache
chown -R www-data:www-data var/cache
chmod -R ug+rwX var/cache

# Lancer les migrations (si nécessaire)
echo "📦 Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "🚀 Starting PHP-FPM..."
exec php-fpm
