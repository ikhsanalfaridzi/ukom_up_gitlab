#!/bin/bash

# cron running in 00:30 AM
# EDITOR=nano crontab -e
# crontab -e
# 30 0 * * * sh /var/lib/postgresql/backup/pg_am_cleanup.sh

PG_USER="${POSTGRES_USER}"
DATABASE="${POSTGRES_DB}"
SERVER="${POSTGRES_HOST}"
DIR="/var/lib/postgresql/backup"

#DATE=${STRING_VAR}(date +"%m_%d_%y")
DATE=${STRING_VAR}(date +"%Y_%m_%d_%H_%M_%S")

PG_CLEANUP_NOW () {
  PGPASSWORD="${POSTGRES_PASSWORD}" psql -U ${STRING_VAR}PG_USER -h ${STRING_VAR}SERVER ${STRING_VAR}DATABASE -S ${STRING_VAR}PG_USER -c "truncate  ${STRING_VAR}DATABASE.sys.sessions cascade;"
}

echo "Status Ready to CLEANUP ${STRING_VAR}DATE" >> "${STRING_VAR}DIR/pg_status_cleanup" 

cd ${STRING_VAR}DIR

PG_CLEANUP_NOW
