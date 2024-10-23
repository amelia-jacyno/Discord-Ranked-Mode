#!/bin/bash

mkdir cache
chown -R www-data:www-data cache
/usr/bin/crontab /etc/cron.d/crontab
service cron start

vendor/bin/phinx migrate

apache2-foreground