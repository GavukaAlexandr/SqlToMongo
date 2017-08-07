<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 04.08.17
 * Time: 23:07
 */

namespace DataBase;

use DI\Annotation\Inject;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class MongoDbConnectionTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAnnotations(true);
        $containerBuilder->useAutowiring(true);
        $container = $containerBuilder->build();

        $container->injectOn($this);

        parent::__construct($name, $data, $dataName);
    }

    public function setUp()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAnnotations(true);
        $containerBuilder->useAutowiring(true);
        $container = $containerBuilder->build();

        $container->injectOn($this);

        parent::setUp();
    }

    public function testConfigFile()
    {
        $file = __DIR__ . '/../Config/config.yml';
        $this->assertIsReadable($file, 'config.yml is not readable');
        $this->AssertFileExists($file, 'Config.yml not exist!');

        $configFile = yaml_parse_file($file);

        $this->assertNotNull($configFile['config']['MongoDB']['uri'],
            "not specified URI field in config.yml");
        $this->assertNotNull($configFile['config']['MongoDB']['host'],
            "not specified HOST field in config.yml");
        $this->assertNotNull($configFile['config']['MongoDB']['port'],
            "not specified PORT field in config.yml");
    }

    /**
     * @Inject
     * @param MongoDbConnection $mongoDbConnection
     *
     * @return MongoDbConnection
     */
    public function testMongoDbConnection(MongoDbConnection $mongoDbConnection)
    {
        $this->assertNotNull($mongoDbConnection);

        return $mongoDbConnection;
    }
}
