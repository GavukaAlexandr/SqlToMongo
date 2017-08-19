<?php

use DataBase\ConnectionInterface;
use DataBase\DatabaseConfiguration;
use DataBase\DataBaseConfigurationInterface;
use DataBase\MongoDbConnection;
use function DI\object;
use function DI\get;

return [

    ConnectionInterface::class => object(MongoDbConnection::class),
    DataBaseConfigurationInterface::class => object(DatabaseConfiguration::class)
];
