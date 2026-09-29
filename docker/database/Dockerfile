FROM postgres:15.2-alpine3.17

ENV TMP_DIR_MIGRATE=/_tmp_migrate

COPY docker/database $TMP_DIR_MIGRATE/

RUN \
	apk add gettext \
	# timezone
	&& apk add tzdata \
	&& cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
	&& echo "Asia/Jakarta" > /etc/timezone \
	&& apk del tzdata \

	&& cp -r $TMP_DIR_MIGRATE/cronjob /var/lib/postgresql/cronjob_am \

    && mv $TMP_DIR_MIGRATE/builder/build.sh /init_am.sh \
    && chmod +x /init_am.sh \

	# remove temp
    && rm -rf $TMP_DIR_MIGRATE \

# add new execute custom and default postgres from images
CMD ["/init_am.sh"]
# execute default postgres from images for presistent services
CMD ["postgres"]