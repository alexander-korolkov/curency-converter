FROM php:8.3-fpm

WORKDIR /var/www/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /var/www/
RUN chmod -R 776 /var/www/
RUN apt-get update && \
    apt-get install -y git && \
    apt-get install -y unzip && \
    apt-get install -y libicu-dev && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install intl && \
    docker-php-ext-enable intl && \
    rm -rf /var/lib/apt/lists/*

RUN pecl install --force redis \
    && docker-php-ext-enable redis

RUN apt-get clean && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/pear