[![Build Status](https://travis-ci.org/GavukaAlexandr/SqlToMongo.svg?branch=master)](https://travis-ci.org/GavukaAlexandr/SqlToMongo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GavukaAlexandr/SqlToMongo/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/GavukaAlexandr/SqlToMongo/?branch=dev)
[![Code Coverage](https://scrutinizer-ci.com/g/GavukaAlexandr/SqlToMongo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/GavukaAlexandr/SqlToMongo/?branch=master)

DEPENDENCIES

php libraries:
* mongodb/mongodb
* greenlion/php-sql-parser
* php-di/php-di
* league/climate

REQUIREMENTS
* PHP ^7.1
* libyaml-dev
* yaml
* php7.1-mongodb
* php-mongodb

LAUNCH

install Docker
https://docs.docker.com/engine/installation/
````
git clone git@github.com:GavukaAlexandr/SqlToMongo.git
cd SqlToMongo/

Change host in ./Config/config.yml if you need to access the MongoDB
from the container docker on the host machine
host: '172.17.0.1'

sudo docker build . -t sql-to-mongodb
docker run -it sql-to-mongodb

````
![Image](https://github.com/GavukaAlexandr/SqlToMongo/tree/master/images/Screenshot.png)



