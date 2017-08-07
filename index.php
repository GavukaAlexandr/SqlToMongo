#!/usr/bin/php
<?php

use DI\ContainerBuilder;

require __DIR__ . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(true);
$containerBuilder->useAutowiring(true);
$container = $containerBuilder->build();

$sqlToMongo = $container->get("DataBase\MongoDbConnection");
