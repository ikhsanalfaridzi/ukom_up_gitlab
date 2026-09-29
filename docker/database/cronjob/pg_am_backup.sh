#!/bin/bash

# cron running in 01:30 AM
# EDITOR=nano crontab -e
# crontab -e
# 30 1 * * * sh /var/lib/postgresql/backup/pg_am_backup.sh

PG_USER="${POSTGRES_USER}"
DATABASE="${POSTGRES_DB}"
SERVER="${POSTGRES_HOST}"
DIR="/var/lib/postgresql/backup"

#DATE=${STRING_VAR}(date +"%m_%d_%y")
DATE=${STRING_VAR}(date +"%Y_%m_%d_%H_%M_%S")
DATE_ONLY=${STRING_VAR}(date +"%d")
#FILE="${STRING_VAR}DATABASE"_"${STRING_VAR}DATE"
FILE="${STRING_VAR}DATABASE"_"daily1.sql"
FILE1="${STRING_VAR}DATABASE"_"daily1.am"
FILE2="${STRING_VAR}DATABASE"_"daily2.am"
FILE3="${STRING_VAR}DATABASE"_"daily3.am"
FILE4="${STRING_VAR}DATABASE"_"weekly.am"
FILE5="${STRING_VAR}DATABASE"_"monthly.am"
DOW=${STRING_VAR}(date +%u)

PG_BAK_NOW () {
  PGPASSWORD="${POSTGRES_PASSWORD}" pg_dump -U ${STRING_VAR}PG_USER -h ${STRING_VAR}SERVER ${STRING_VAR}DATABASE -S ${STRING_VAR}PG_USER -O -x --disable-triggers --no-tablespaces --quote-all-identifiers > ${STRING_VAR}FILE
}
AM_COMPRESS()
{
	tar -czvf "${STRING_VAR}FILE1" "${STRING_VAR}FILE"
	rm "${STRING_VAR}DIR/${STRING_VAR}FILE"
}

echo "Status Ready to dump ${STRING_VAR}DATE to ${STRING_VAR}FILE" >> "${STRING_VAR}DIR/pg_status_backup" 

cd ${STRING_VAR}DIR

# priority case bacekup, dayly, weekly, monthly (in case date 1 in moday priority dayly to monthly)

# when date 1 for monthly
if [[ ${STRING_VAR}DATE_ONLY == "01" ]]
then
	if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE1" ]
	then
		if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE5" ]
		then
			rm "${STRING_VAR}DIR/${STRING_VAR}FILE5"
		fi
		mv "${STRING_VAR}DIR/${STRING_VAR}FILE1" "${STRING_VAR}DIR/${STRING_VAR}FILE5"
	fi
fi

# when monday for weekly
if [[ ${STRING_VAR}DOW == "1" ]]
then
	if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE1" ]
	then
		if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE4" ]
		then
			rm "${STRING_VAR}DIR/${STRING_VAR}FILE4"
		fi
		mv "${STRING_VAR}DIR/${STRING_VAR}FILE1" "${STRING_VAR}DIR/${STRING_VAR}FILE4"
	fi
fi

# when daily
if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE1" ]
then
	if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE2" ]
	then
		if [ -f "${STRING_VAR}DIR/${STRING_VAR}FILE3" ]
		then
			rm "${STRING_VAR}DIR/${STRING_VAR}FILE3"
		fi
		mv "${STRING_VAR}DIR/${STRING_VAR}FILE2" "${STRING_VAR}DIR/${STRING_VAR}FILE3"
	fi
	mv "${STRING_VAR}DIR/${STRING_VAR}FILE1" "${STRING_VAR}DIR/${STRING_VAR}FILE2"
fi

PG_BAK_NOW 
AM_COMPRESS
