<?php

use DataBase\ConnectionInterface;
use DataBase\DatabaseConfiguration;
use DataBase\DataBaseConfigurationInterface;
use DataBase\MongoDbConnection;

return [

    ConnectionInterface::class => DI\object(MongoDbConnection::class),
    DataBaseConfigurationInterface::class => DI\object(DatabaseConfiguration::class)
];
