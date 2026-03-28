FROM php:8.4-apache

# -----------------------------
# システムパッケージ
# -----------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# PHP拡張
# -----------------------------
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    xml

# -----------------------------
# Composer
# -----------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------
# Apache設定
# -----------------------------
RUN a2enmod rewrite
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

# -----------------------------
# 作業ディレクトリ
# -----------------------------
WORKDIR /var/www/html
