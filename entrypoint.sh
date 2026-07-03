#!/bin/sh
set -e

echo "Waiting for database..."

for _ in $(seq 1 30); do
  if php -r "
    \$host = getenv('DB_HOST') ?: 'postgres';
    \$port = getenv('DB_PORT') ?: '5432';
    \$db = getenv('DB_DATABASE') ?: 'PhpApi';
    \$user = getenv('DB_USERNAME') ?: 'postgres';
    \$pass = getenv('DB_PASSWORD') ?: 'postgres';
    try {
        new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
  "; then
    break
  fi
  sleep 2
done

php artisan migrate --force --no-interaction
exec "$@"
