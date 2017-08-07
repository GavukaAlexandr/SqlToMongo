<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 03.08.17
 * Time: 14:12
 */

namespace DataBase;

use MongoDB\Client;

class MongoDbConnection
{
    /** @var DatabaseConfiguration */
    private $configuration;

    /** @var Client */
    private $connection;

    public function __construct(DatabaseConfiguration $databaseConfiguration)
    {
        $this->configuration = $databaseConfiguration;
        /**
         * for connect with auth use:
         * "mongodb://myusername:myp%40ss%3Aw%25rd@example.com/mydatabase"
         * or set params in array $uriOptions
         * http://php.net/manual/en/mongodb-driver-manager.construct.php
         */
        $this->connection = new Client($this->configuration->getHost() . $this->configuration->getPort());
    }

    /**
     * @return Client
     */
    public function getConnection(): Client
    {
        return $this->connection;
    }

    /**
     * @return DatabaseConfiguration
     */
    public function getConfiguration(): DatabaseConfiguration
    {
        return $this->configuration;
    }


}
