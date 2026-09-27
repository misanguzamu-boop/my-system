FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

COPY . /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen 0.0.0.0:${PORT:-10000}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-10000}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
