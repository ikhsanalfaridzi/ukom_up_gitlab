FROM php:8.2.22-fpm-alpine3.20

ENV TMP_DIR_MIGRATE=/_tmp_migrate \
	TMP_DIR_APP_MIGRATE=/_tmp_appsource

# path base on execute docker run or compose
COPY docker/platform/ $TMP_DIR_MIGRATE/

RUN \ 
	apk update \
	&& apk add --no-cache \
		zlib-dev \
		libpq-dev \
		libzip-dev \
		libjpeg-turbo-dev \
		libpng-dev \
		libwebp-dev \
		freetype-dev \
		postgresql-dev \
		zip \
		unzip \
	&& docker-php-ext-configure pgsql \
	&& docker-php-ext-configure pdo_pgsql \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install \
		gd \
		intl \
		pdo \
		pdo_pgsql \
		pgsql \
		zip \
		bcmath \
		opcache \

	# timezone
	&& apk add tzdata \
	&& cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
	&& echo "Asia/Jakarta" > /etc/timezone \
	&& apk del tzdata \

	## Apps Mapping
	&& mkdir /var/webapps \

	&& chmod +x /var/webapps/ \

	## PHP CONFIG ##
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && mv "$TMP_DIR_MIGRATE/config/php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini" 

RUN \ 
    ## Clean up
    rm -rf $TMP_DIR_APP_MIGRATE \
	&& rm -rf $TMP_DIR_MIGRATE

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set Working Directory
WORKDIR /var/webapps

# Copy script entrypoint ke dalam container
COPY docker/platform/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# script sebagai entrypoint
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
