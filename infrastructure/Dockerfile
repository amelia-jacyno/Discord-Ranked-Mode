FROM php:8.1-apache

RUN sed -ri -e 's!/var/www/html!${APP_PATH}/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APP_PATH}/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get -y install cron gettext-base && apt-get clean

COPY ./infrastructure/crontab.tpl /etc/cron.d/crontab.tpl