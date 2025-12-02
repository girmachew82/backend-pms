FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# INI-Files
COPY ./opcache.ini "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini"
COPY ./xdebug.ini "$PHP_INI_DIR/conf.d/99-xdebug.ini"
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install Packages
RUN apt-get -y update && apt-get install -y libicu-dev libzip-dev zip libjpeg-dev libpng-dev libfreetype6-dev git
RUN docker-php-ext-configure intl
RUN docker-php-ext-configure gd '--with-jpeg' '--with-freetype'
RUN docker-php-ext-install intl opcache pdo_mysql zip gd
RUN pecl install xdebug
RUN a2enmod rewrite

# INSTALL APCU
RUN pecl install apcu-5.1.24 && docker-php-ext-enable apcu
RUN echo "extension=apcu.so" > /usr/local/etc/php/php.ini
RUN echo "apc.enable_cli=1" > /usr/local/etc/php/php.ini
RUN echo "apc.enable=1" > /usr/local/etc/php/php.ini
# APCU

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install NVM
RUN curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash

# RUN pecl install xdebug
# RUN a2enmod rewrite
# ...

# ----------------------------------------------------
# Install Node.js and npm via NodeSource (Recommended)
# ----------------------------------------------------
# Install necessary packages for NodeSource setup
RUN apt-get install -y curl gnupg

# Add NodeSource official GPG key
RUN curl -sL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

# Define the Node version (e.g., 20)
ENV NODE_MAJOR=20

# Add the NodeSource repository
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_MAJOR}.x nodistro main" > /etc/apt/sources.list.d/nodesource.list

# Update package lists and install Node.js (which includes npm)
RUN apt-get update && apt-get install -y nodejs