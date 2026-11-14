FROM node:22-alpine AS frontend
WORKDIR /build
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.3-bookworm
RUN install-php-extensions pdo_mysql pdo_sqlite mbstring intl zip bcmath opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY backend/ ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
COPY --from=frontend /build/dist/ public/
COPY deploy/Caddyfile /etc/caddy/Caddyfile
COPY deploy/entrypoint.sh /usr/local/bin/pos-entrypoint
RUN chmod +x /usr/local/bin/pos-entrypoint
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr PORT=80
EXPOSE 80
ENTRYPOINT ["pos-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
