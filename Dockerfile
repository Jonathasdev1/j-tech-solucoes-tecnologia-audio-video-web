FROM php:8.2-cli

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 8080

# DESTAQUE: para este projeto sem rotas reescritas, o servidor embutido do PHP e suficiente e mais estavel no Railway.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
