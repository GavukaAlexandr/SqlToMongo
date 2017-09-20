<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 08.08.17
 * Time: 12:05
 */

namespace DataBase;


interface ConnectionInterface
{
    public function connection(DataBaseConfigurationInterface $databaseConfiguration);
    public function getConnection();
}
