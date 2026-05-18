#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

if [ ! -f config/jwt/private.pem ]; then
  mkdir -p config/jwt
  PASS="${JWT_PASSPHRASE:-growth_mvp_jwt_pass}"
  openssl genrsa -aes256 -passout "pass:${PASS}" -out config/jwt/private.pem 4096 2>/dev/null || \
    openssl genrsa -out config/jwt/private.pem 4096
  openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem \
    -passin "pass:${PASS}" 2>/dev/null || \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
  chown www-data:www-data config/jwt/private.pem config/jwt/public.pem 2>/dev/null || true
  chmod 640 config/jwt/private.pem
  chmod 644 config/jwt/public.pem
fi

# Ensure php-fpm can read keys (volume mount may reset ownership)
if [ -f config/jwt/private.pem ]; then
  chown www-data:www-data config/jwt/private.pem config/jwt/public.pem 2>/dev/null || true
  chmod 640 config/jwt/private.pem 2>/dev/null || true
  chmod 644 config/jwt/public.pem 2>/dev/null || true
fi

php bin/console doctrine:migrations:migrate --no-interaction

exec "$@"
