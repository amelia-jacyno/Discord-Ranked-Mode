*/15 * * * * /usr/local/bin/php ${APP_PATH}/bin/drm update_players | ts '[%Y-%m-%d %H:%M:%S]' >> ${APP_PATH}/log/cron.log 2>&1
*/15 * * * * /usr/local/bin/php ${APP_PATH}/bin/drm create_backup | ts '[%Y-%m-%d %H:%M:%S]' >> ${APP_PATH}/log/cron.log 2>&1
