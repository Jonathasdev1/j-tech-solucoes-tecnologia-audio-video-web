FROM php:8.2-apache

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

RUN chmod +x /var/www/html/render/start.sh

CMD ["/var/www/html/render/start.sh"]
