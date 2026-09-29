FROM nginx:1.22.0-alpine

ENV TMP_DIR_MIGRATE=/_tmp_migrate \
	TMP_DIR_SITES_MIGRATE=/_sites

# path base on execute docker run or compose
COPY docker/webserver $TMP_DIR_MIGRATE/

RUN \
	# move log
	ln -sf /dev/stdout /var/log/nginx/access.log \ 	&& ln -sf /dev/stderr /var/log/nginx/error.log \
	
	# move default sites
	&& mkdir /var/webapps \

	&& mv $TMP_DIR_MIGRATE/html /var/webapps/public/ \

	# timezone
	&& apk add tzdata \
	&& cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
	&& echo "Asia/Jakarta" > /etc/timezone \
	&& apk del tzdata \

	#cron job
	&& apk add curl \
    && mv $TMP_DIR_MIGRATE/builder/build.sh /init.sh \
    && chmod +x /init.sh \
	# move configuration
	&& cp $TMP_DIR_MIGRATE/config/webhost.conf /etc/nginx/nginx.conf.template \
	&& cp $TMP_DIR_MIGRATE/config/apphost.conf /etc/nginx/conf.d/default.conf.template

RUN \
	# remove temp
    rm -rf $TMP_DIR_MIGRATE \
    && rm -rf $TMP_DIR_SITES_MIGRATE

CMD ["/init.sh"]