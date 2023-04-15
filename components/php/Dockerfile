FROM php:fpm-alpine3.17

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \ 
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del pcre-dev ${PHPIZE_DEPS}

COPY src /var/www/html
