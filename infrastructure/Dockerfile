FROM php:8.1-apache as base

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Configure Apache
RUN sed -ri -e 's!/var/www/html!/app/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/app/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install dependencies
RUN apt-get update && apt-get -y install cron gettext-base moreutils zip && apt-get clean

# Configure Cron
COPY ./infrastructure/crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN touch /var/log/cron.log

FROM base as dev
WORKDIR /app

FROM base as prod
WORKDIR /app

# Copy the application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --ignore-platform-reqs