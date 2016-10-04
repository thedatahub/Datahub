FROM debian:jessie

MAINTAINER Inuits

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y
RUN apt-get install -y \
 curl \
 openjdk-7-jre-headless \
 php5-cli php5-json php5-curl \
 php5-intl php5-mongo \
 phpunit

RUN echo 'date.timezone = Europe/Brussels' >> /etc/php5/cli/php.ini
RUN echo '#!/bin/bash\n\
export SYMFONY__MONGODB_SERVER="mongodb://${MONGODB_PORT_27017_TCP_ADDR}:${MONGODB_PORT_27017_TCP_PORT}"\n\
export SYMFONY__CATMANDU_CLI_PATH=$(which catmandu)\n\
env | grep "SYMFONY"\n\
. "$@"' >> /usr/bin/docker_env_run
RUN chmod +x /usr/bin/docker_env_run

RUN apt-get install -y \
 cpanminus build-essential libexpat1-dev \
 libssl-dev libxml2-dev libxslt1-dev \
 libgdbm-dev

RUN cpanm Catmandu \
 Catmandu::LIDO

# RUN useradd tests
# RUN sed --in-place '/disable_functions/d' /etc/php5/cli/php.ini
# USER tests

WORKDIR /var/www
VOLUME ["/var/www"]
VOLUME ["/var/www/app/config/parameters.yml"]

# CMD ["--exclude-group", "benchmark"]
ENTRYPOINT ["/usr/bin/docker_env_run"]
