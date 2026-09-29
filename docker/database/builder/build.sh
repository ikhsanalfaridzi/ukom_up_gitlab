#!/bin/sh

sh -c "envsubst < /var/lib/postgresql/cronjob_am/pg_am_cleanup.sh > /var/lib/postgresql/cronjob_am/pg_am_cleanup.sh.custom"
sh -c "envsubst < /var/lib/postgresql/cronjob_am/pg_am_vacuum.sh > /var/lib/postgresql/cronjob_am/pg_am_vacuum.sh.custom"
sh -c "envsubst < /var/lib/postgresql/cronjob_am/pg_am_backup.sh > /var/lib/postgresql/cronjob_am/pg_am_backup.sh.custom"

## remove existing cron in volume
sh -c "rm -rf /var/lib/postgresql/backup/pg_am_cleanup.sh"
sh -c "rm -rf /var/lib/postgresql/backup/pg_am_vacuum.sh"
sh -c "rm -rf /var/lib/postgresql/backup/pg_am_backup.sh"

sh -c "rm -rf /var/lib/postgresql/backup/pg_status_cleanup"
sh -c "rm -rf /var/lib/postgresql/backup/pg_status_vacuum"
sh -c "rm -rf /var/lib/postgresql/backup/pg_status_backup"

## replace new cron config
sh -c "cp -rf /var/lib/postgresql/cronjob_am/pg_am_cleanup.sh.custom /var/lib/postgresql/backup/pg_am_cleanup.sh"
sh -c "cp -rf /var/lib/postgresql/cronjob_am/pg_am_vacuum.sh.custom /var/lib/postgresql/backup/pg_am_vacuum.sh"
sh -c "cp -rf /var/lib/postgresql/cronjob_am/pg_am_backup.sh.custom /var/lib/postgresql/backup/pg_am_backup.sh"
sh -c "rm -rf /var/lib/postgresql/cronjob_am"

#sh -c 'echo "*/5 * * * * /bin/sh /var/lib/postgresql/backup/pg_am_cleanup.sh" >> /etc/crontabs/root'
#sh -c 'echo "*/6 * * * * /bin/sh /var/lib/postgresql/backup/pg_am_vacuum.sh" >> /etc/crontabs/root'
#sh -c 'echo "*/10 * * * * /bin/sh /var/lib/postgresql/backup/pg_am_backup.sh" >> /etc/crontabs/root'
sh -c 'echo "*/30 0 * * * /bin/sh /var/lib/postgresql/backup/pg_am_cleanup.sh" >> /etc/crontabs/root'
sh -c 'echo "*/40 0 * * * /bin/sh /var/lib/postgresql/backup/pg_am_vacuum.sh" >> /etc/crontabs/root'
sh -c 'echo "*/30 1 * * * /bin/sh /var/lib/postgresql/backup/pg_am_backup.sh" >> /etc/crontabs/root'
sh -c "crond -l 2 -f > /dev/stdout 2 > /dev/stderr"