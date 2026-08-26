#!/bin/sh
set -e

cd /var/www/html

# Un clon nuevo no trae .env (está en .gitignore).
if [ ! -f .env ]; then
    echo "→ creando .env desde .env.example"
    cp .env.example .env
fi

# Con el bind mount, vendor puede faltar si el volumen se recreó.
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "→ instalando dependencias de Composer"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    echo "→ generando APP_KEY"
    php artisan key:generate --force
fi

# El healthcheck de compose ya garantiza que MySQL responde, pero la base
# puede tardar un instante más en aceptar al usuario de la aplicación.
echo "→ esperando a MySQL"
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
done

# Los seeders usan firstOrCreate, así que reejecutarlos en cada arranque
# no duplica datos.
echo "→ migrando y sembrando"
php artisan migrate --force --seed

chown -R www-data:www-data storage bootstrap/cache

echo "→ listo, arrancando php-fpm"
exec "$@"
