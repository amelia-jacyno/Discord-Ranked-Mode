#!/bin/bash

mkdir cache
chown www-data:www-data cache

bin/doctrine orm:generate:proxies cache/doctrine/proxies

vendor/bin/phinx migrate

/usr/bin/crontab /etc/cron.d/crontab
service cron start

php-fpm