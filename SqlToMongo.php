#!/usr/bin/php
<?php

use DI\ContainerBuilder;

require __DIR__ . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions('Config/DependenceInjectionConfig.php');
$containerBuilder->useAnnotations(true);
$containerBuilder->useAutowiring(true);
$container = $containerBuilder->build();

$cliView = $container->get('Views\CliView');
$cliView->draw();

$sqlToMongo = $container->get('SqlToMongoDb');
$sqlToMongo->run();
