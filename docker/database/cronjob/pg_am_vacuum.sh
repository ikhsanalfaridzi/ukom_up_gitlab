#!/bin/bash

# cron running in 00:35 AM
# EDITOR=nano crontab -e
# crontab -e
# 35 0 * * * sh /var/lib/postgresql/backup/pg_am_vacuum.sh

PG_USER="${POSTGRES_USER}"
DATABASE="${POSTGRES_DB}"
SERVER="${POSTGRES_HOST}"
DIR="/var/lib/postgresql/backup"

#DATE=${STRING_VAR}(date +"%m_%d_%y")
DATE=${STRING_VAR}(date +"%Y_%m_%d_%H_%M_%S")

PG_VACUUM_NOW () {
  PGPASSWORD="${POSTGRES_PASSWORD}" psql -U ${STRING_VAR}PG_USER -h ${STRING_VAR}SERVER ${STRING_VAR}DATABASE -S ${STRING_VAR}PG_USER -c 'set session statement_timeout=0' -c 'VACUUM (FULL,FREEZE,VERBOSE,ANALYZE)' -c "REINDEX DATABASE ${STRING_VAR}DATABASE"
}

echo "Status Ready to VACUUM ${STRING_VAR}DATE" >> "${STRING_VAR}DIR/pg_status_vacuum" 

cd ${STRING_VAR}DIR

PG_VACUUM_NOW
