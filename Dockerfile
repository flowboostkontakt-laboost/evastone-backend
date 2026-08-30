FROM laravelsail/php84-composer:latest

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
        libavif-dev libicu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype --with-avif \
    && docker-php-ext-install -j"$(nproc)" gd intl pdo_mysql exif \
    && rm -rf /var/lib/apt/lists/*
