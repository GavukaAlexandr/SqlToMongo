FROM ubuntu:xenial

MAINTAINER Alexandr Gavuka <alexandrgavuka@gmail.com>

WORKDIR /home/SqlToMongo

#install php7.1 and extensions
RUN apt-get update && DEBIAN_FRONTEND='noninteractive' apt-get -y install software-properties-common apt-transport-https language-pack-en-base
ENV locale-gen LC_ALL=en_US.UTF-8
ENV LANG=en_US.UTF-8
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && DEBIAN_FRONTEND='noninteractive' apt-get install -y php7.1
RUN apt-get  install -y php7.1-cli
RUN apt-get install -y php-dev
RUN apt-get install -y php-pear
RUN apt-get install -y libyaml-dev
RUN apt-get install -y php7.1-mbstring
RUN pecl channel-update pecl.php.net
RUN pecl install yaml
RUN echo "extension=yaml.so" >> /etc/php/7.1/cli/php.ini
RUN apt-get install -y pkg-config libssl-dev openssl
RUN pecl install mongodb
RUN apt-get install -y php-mongodb

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

#copy project files in WORKDIR
COPY . /home/SqlToMongo

RUN composer install

EXPOSE 27017

CMD php SqlToMongo.php
