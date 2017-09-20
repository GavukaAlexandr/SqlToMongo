<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 08.08.17
 * Time: 0:28
 */

namespace DataBase;


interface DataBaseConfigurationInterface
{
    public function loadConfig();

    public function getHost();

    public function getPort();

    public function getUsername();

    public function getPassword();

}
