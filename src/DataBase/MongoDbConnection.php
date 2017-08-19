<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 03.08.17
 * Time: 14:12
 */

namespace DataBase;

use DI\Annotation\Inject;
use MongoDB\Client;
use MongoDB\Driver\Manager;

class MongoDbConnection implements ConnectionInterface
{
    /** @var Client */
    private $connection;

    /** @var DatabaseConfiguration|DataBaseConfigurationInterface  */
    private $config;

    /**
     * MongoDbConnection constructor.
     * @param DatabaseConfiguration|DataBaseConfigurationInterface $databaseConfiguration
     */
    public function __construct(DataBaseConfigurationInterface $databaseConfiguration)
    {
        $this->config = $databaseConfiguration;
        $this->connection($databaseConfiguration);
    }

    /**
     * @param DatabaseConfiguration|DataBaseConfigurationInterface $configuration
     */
    public function connection(DataBaseConfigurationInterface $configuration)
    {
        /**
         * for connect with auth use:
         * "mongodb://myusername:myp%40ss%3Aw%25rd@example.com/mydatabase"
         * or set params in array $uriOptions
         * http://php.net/manual/en/mongodb-driver-manager.construct.php
         */
        $this->connection = new Client($configuration->getHost() . $configuration->getPort());
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
    public function getConfig(): DatabaseConfiguration
    {
        return $this->config;
    }
}
