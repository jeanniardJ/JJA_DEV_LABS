FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

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

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN composer self-update --2

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony \
    && chmod +x /usr/local/bin/symfony

COPY .devcontainer/php.ini /usr/local/etc/php/conf.d/99-jja-dev.ini

USER $USERNAME

WORKDIR /workspace