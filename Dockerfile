FROM quay.io/cashrewards/php-base-ms

# <<<MSSQL --------------------------------
RUN  \
    #####################################
    # Ref from https://github.com/Microsoft/msphpsql/wiki/Dockerfile-for-adding-pdo_sqlsrv-and-sqlsrv-to-official-php-image
    #           https://blogs.msdn.microsoft.com/sqlnativeclient/2017/06/30/servicing-update-for-odbc-driver-13-1-for-linux-and-macos-released/
    #           https://github.com/merorafael/docker-php-fpm/blob/master/7.1/Dockerfile
    #####################################
    # Add Microsoft repo for Microsoft ODBC Driver 13 for Linux
    apt-get update -yqq && apt-get install -y apt-transport-https \
        && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
        && curl https://packages.microsoft.com/config/debian/8/prod.list > /etc/apt/sources.list.d/mssql-release.list \
        && apt-get update -yqq \

    # Install Dependencies
    	# && ACCEPT_EULA=Y apt-get -y install msodbcsql unixodbc-dev gcc g++ build-essential \
        && ACCEPT_EULA=Y apt-get install -y unixodbc unixodbc-dev libgss3 odbcinst msodbcsql locales \
        && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && locale-gen \

    # Install pdo_sqlsrv and sqlsrv from PECL. Replace pdo_sqlsrv-4.1.8preview with preferred version.
        && pecl install pdo_sqlsrv-4.1.8preview sqlsrv-4.1.8preview \
        && docker-php-ext-enable pdo_sqlsrv sqlsrv
# MSSQL>>>---------------------------------


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