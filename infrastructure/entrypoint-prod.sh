#!/bin/bash

mkdir cache
chown -R www-data:www-data cache

vendor/bin/phinx migrate

/usr/bin/crontab /etc/cron.d/crontab
service cron start

php-fpm