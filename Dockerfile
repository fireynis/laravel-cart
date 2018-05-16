FROM php:7.1-cli

RUN apt-get update && apt-get install -y \
    libmcrypt-dev git zip unzip

RUN docker-php-ext-install mcrypt pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');"

RUN mv composer.phar /usr/local/bin/composer
