#!/bin/bash

envsubst < infrastructure/crontab.tpl > /etc/cron.d/crontab
rm /etc/cron.d/crontab.tpl
chmod 644 /etc/cron.d/crontab
touch log/cron.log
/usr/bin/crontab /etc/cron.d/crontab
service cron start

apache2-foreground