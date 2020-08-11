FROM php:7.4.8-fpm

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug