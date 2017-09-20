[![Build Status](https://travis-ci.org/GavukaAlexandr/SqlToMongo.svg?branch=master)](https://travis-ci.org/GavukaAlexandr/SqlToMongo)

DEPENDENCIES

libyaml-dev
yaml php extensions
php7.1-mongodb
````
sudo apt-get install php-pear libyaml-dev
sudo pecl install yaml
You should add "extension=yaml.so" to php.ini

pecl install mongodb
sudo apt-get install php7.1-mongodb

````

LAUNCH

if the service mongod is started, it must be stopped
sudo service mongod stop

start mongoDb service from project dir
````
mongod --port 27017 --dbpath PROJECTS_DIR.../sql_to_mongo/mongoDb
````

Change host in ./Config/config.yml if you need to access the MongoDB
from the container docker on the host machine
````
host: '172.17.0.1'
````
