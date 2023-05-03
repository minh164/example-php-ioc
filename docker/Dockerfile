#FROM bitnami/php-fpm:8.1.4
FROM php:8.1-fpm

WORKDIR /var/www/app

# Install supervisor
RUN apt-get update
RUN apt-get install supervisor -y

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENTRYPOINT ["sh", "/var/www/app/init.sh"]
#CMD ["php-fpm7.4", "-F"]