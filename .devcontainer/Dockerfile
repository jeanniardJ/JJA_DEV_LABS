FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# Évite l'erreur GPG du dépôt Yarn présent dans l'image de base
RUN rm -f /etc/apt/sources.list.d/yarn*.list \
    && rm -f /usr/share/keyrings/yarnkey.gpg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
        git \
        unzip \
        zip \
        curl \
        ca-certificates \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        default-mysql-client \
        sqlite3 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP pour Symfony + MySQL/MariaDB
RUN docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        bcmath \
        opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Xdebug est déjà inclus dans l'image : ne pas le réinstaller avec PECL
RUN php -m | grep -q xdebug

RUN composer self-update --2

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony \
    && chmod +x /usr/local/bin/symfony

# Configuration PHP
COPY .devcontainer/php.ini /usr/local/etc/php/conf.d/99-jja-dev.ini

USER $USERNAME

WORKDIR /var/www/html