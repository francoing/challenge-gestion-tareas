FROM php:8.3-fpm-alpine

# pdo_mysql para la base; zip lo usa Composer; bcmath y opcache son
# recomendados por Laravel. mysql-client deja usar mysqladmin al depurar.
RUN apk add --no-cache libzip-dev icu-dev oniguruma-dev mysql-client \
    && docker-php-ext-install pdo_mysql bcmath zip opcache \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# --no-scripts: los scripts de post-install corren artisan, y todavía
# no hay .env ni base de datos disponible durante el build.
RUN composer install --no-interaction --prefer-dist --no-scripts --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
