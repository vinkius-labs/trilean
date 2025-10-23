# syntax=docker/dockerfile:1.5

FROM php:8.2-cli

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libonig-dev \
    sqlite3 \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    intl \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /workspace

CMD ["sleep", "infinity"]
