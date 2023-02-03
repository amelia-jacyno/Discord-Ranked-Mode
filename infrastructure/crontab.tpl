*/15 * * * * /usr/local/bin/php ${APP_PATH}/bin/drm update_players | ts '[%Y-%m-%d %H:%M:%S]' >> ${APP_PATH}/log/cron.log 2>&1
