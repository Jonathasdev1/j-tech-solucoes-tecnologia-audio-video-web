FROM php:8.2-apache

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

# DESTAQUE: Railway/Render definem a porta via env PORT; ajustamos Apache em runtime.
CMD ["sh", "-c", "PORT=${PORT:-8080}; sed -ri \"s/^Listen .*/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -ri \"s#<VirtualHost \\*:.*>#<VirtualHost *:${PORT}>#\" /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
