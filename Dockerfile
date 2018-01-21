FROM quay.io/cashrewards/php-base-ms:mssql

# SOURCE CODE COPY----------------------------------------------------------------------------------------------
COPY ./src/ /var/www/html/
# COMPOSER ----------------------------------------------------------------------------------------------
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN /usr/local/bin/composer self-update \
    && cd /var/www/html \
    && /usr/local/bin/composer update

# SET FILE PERMISSION --------------------------------------------------------------------------------------
RUN chown -R :www-data /var/www/html \
 && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache
# THESE VOLUMES are shares with nginx container using "volumes_from"
VOLUME ["/etc/nginx/conf.d/", "/var/www/html/"]