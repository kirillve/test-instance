FROM debian:jessie-slim

EXPOSE 80

# Prepare Debian environment
ENV DEBIAN_FRONTEND noninteractive

# Performance optimization - see https://gist.github.com/jpetazzo/6127116
# this forces dpkg not to call sync() after package extraction and speeds up install
RUN echo "force-unsafe-io" > /etc/dpkg/dpkg.cfg.d/02apt-speedup
# we don't need an apt cache in a container
RUN echo "Acquire::http {No-Cache=True;};" > /etc/apt/apt.conf.d/no-cache

# System update
RUN echo "" > /etc/apt/sources.list
RUN echo "deb http://ftp.debian.org/debian jessie main non-free contrib" > /etc/apt/sources.list.d/jessie.list
RUN echo "deb http://http.debian.net/debian jessie-backports main non-free contrib" > /etc/apt/sources.list.d/jessie-backports.list
RUN echo "deb http://security.debian.org jessie/updates main non-free contrib" > /etc/apt/sources.list.d/jessie-security.list
RUN echo "deb http://ftp.debian.org/debian jessie-updates main non-free contrib" > /etc/apt/sources.list.d/jessie-updates.list
RUN set -x && \
    apt-get update -qq && \
    apt-get -y dist-upgrade && \
    apt-get install -y wget apt-transport-https apt-utils

# Postgres APT source
RUN echo "deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main" > /etc/apt/sources.list.d/pgdg.list
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -

RUN apt-get update -qq
RUN apt-get install -y pgdg-keyring
# Update and install system base packages
# don't allow the package to install its sysctl file (causes the install to fail)
# Failed to write '262144' to '/proc/sys/vm/max_map_count': Read-only file system
RUN set -x && \
    apt-get update -qq && \
    apt-get install -y \
        openssh-client \
        git \
        apache2 \
        libapache2-mod-php5 \
        postgresql-client-10 \
        curl \
        php5-curl \
        php5-cli \
        php5-gd \
        php5-imagick \
        php5-intl \
        php5-json \
        php5-mcrypt \
        php5-pgsql \
        php5-redis \
        php5-xsl \
        cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#php memory limit
RUN sed -i 's/memory_limit = .*/memory_limit = 128M/' /etc/php5/apache2/php.ini
RUN sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /etc/php5/apache2/php.ini

#apache2 modules & settings
RUN a2enmod rewrite php5 headers

# clear Apache default page
RUN truncate -s 0 /var/www/html/index.html

# Initialize application
RUN mkdir /app
WORKDIR /app

CMD /app/bin/_app_docker_init.sh && bash
