#!/bin/sh

sh -c "envsubst < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf"
sh -c "envsubst < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf"

## remove existing ssl in volume
sh -c "rm -rf /var/webapps_ssl/certificate.crt"
sh -c "rm -rf /var/webapps_ssl/certificate.key"
## replace new ssl
sh -c "cp -rf /var/webapps_ssl_am/ssl/certificate.crt /var/webapps_ssl/certificate.crt"
sh -c "cp -rf /var/webapps_ssl_am/ssl/certificate.key /var/webapps_ssl/certificate.key"
sh -c "rm -rf /var/webapps_ssl_am"

## remove existing cron in volume
# sh -c "rm -rf /var/webapps/cronjob/app_am_log.sh"
# sh -c "rm -rf /var/webapps/cronjob/app_am_log_status"
## replace new cron config
# sh -c "envsubst < /var/webapps/cronjob_am/app_am_log.sh > /var/webapps/cronjob_am/app_am_log.sh.custom"
# sh -c "cp -rf /var/webapps/cronjob_am/app_am_log.sh.custom /var/webapps/cronjob/app_am_log.sh"
# sh -c "rm -rf /var/webapps/cronjob_am"

## create scheduler
# sh -c 'echo "*/15 0 * * * /bin/sh /var/webapps/cronjob/app_am_log.sh" >> /etc/crontabs/root'
#sh -c 'echo "*/1 * * * * /bin/sh /var/webapps/cronjob/app_am_log.sh" >> /etc/crontabs/root'
# sh -c "crond -l 2 -f > /dev/stdout 2 > /dev/stderr" &

exec nginx -g 'daemon off;'