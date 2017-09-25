<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 04.08.17
 * Time: 23:07
 */

namespace DataBase;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @ContainerConfiguration('../../Config/DependenceInjectionConfig.php')
 *
 * Class MongoDbConnectionTest
 * @package DataBase
 */
class MongoDbConnectionTest extends TestCase
{
    /**
     * create container for PHP-DI and inject dependencies in method
     */
    public function setUp()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            __DIR__ .
            '../../Config/DependenceInjectionConfig.php');
//        $containerBuilder->useAnnotations(true);
        $containerBuilder->useAutowiring(true);
        $container = $containerBuilder->build();

        $container->injectOn($this);

        parent::setUp();
    }

    /**
     * get private method
     *
     * @param $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($name) {
        $class = new ReflectionClass('MyClass');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
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
}
