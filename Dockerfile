FROM php.8.2_apache
#Install MSQL extensions for PHP
RUN docker_php_ext_install pdo pdo_mysql mysql
#Copy all files to apache web directory
COPY   .  /var/www/html/
EXPOSE 80
