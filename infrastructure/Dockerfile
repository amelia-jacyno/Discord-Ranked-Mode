FROM php:8.1-apache

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get -y install cron && apt-get clean

COPY ./infrastructure/crontab /etc/cron.d/crontab
RUN chmod 644 /etc/cron.d/crontab
RUN touch /var/log/cron.log
RUN /usr/bin/crontab /etc/cron.d/crontab