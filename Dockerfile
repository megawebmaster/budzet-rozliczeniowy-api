FROM php:7.4-alpine
MAINTAINER Amadeusz Starzykiewicz <megawebmaster@gmail.com>

RUN apk update
ARG host_uid
RUN (getent passwd $host_uid > /dev/null) || adduser -D -g '' dummy -u $host_uid

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin
RUN php -r "unlink('composer-setup.php');"
RUN ln -s /usr/bin/composer.phar /usr/local/bin/composer

# Install required dependencies (MySQL and XDebug)
RUN apk --no-cache add autoconf gcc g++ musl-dev libc-dev make
RUN apk --no-cache add mariadb-dev
RUN docker-php-ext-install pdo_mysql
RUN pecl install 'xdebug-2.9.6'
RUN apk del autoconf gcc g++ musl-dev libc-dev make
RUN apk --no-cache add git

# Install Symfony CLI
RUN wget -q "https://github.com/symfony/cli/releases/download/v4.18.3/symfony_linux_amd64.gz" -O - | gzip -d > /usr/bin/symfony
RUN chmod 755 /usr/bin/symfony

RUN mkdir /app
RUN chown dummy /app
WORKDIR /app

USER $host_uid

