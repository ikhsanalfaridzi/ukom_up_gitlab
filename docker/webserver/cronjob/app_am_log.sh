#!/bin/bash

# cron running in 01:30 AM
# EDITOR=nano crontab -e
# crontab -e
# 30 1 * * * sh /var/webapps/cronjob/app_am_log.sh


SERVER="http://${SERVER_NAME}/cli"
DIR="/var/webapps/cronjob"

#DATE=(date +"%m_%d_%y")
DATE=${STRING_VAR}(date +"%Y_%m_%d_%H_%M_%S")

SET_LOG_NOW ()
{
	curl ${STRING_VAR}SERVER
	echo "Status Ready Load ${STRING_VAR}SERVER at ${STRING_VAR}DATE " >> "${STRING_VAR}DIR/app_am_log_status" 
}

SET_LOG_NOW