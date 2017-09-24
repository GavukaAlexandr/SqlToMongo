<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.09.17
 * Time: 15:10
 */

namespace Views;

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class CliViewTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            __DIR__ .
            '../../Config/DependenceInjectionConfig.php');
        $containerBuilder->useAnnotations(true);
        $containerBuilder->useAutowiring(true);
        $this->container = $containerBuilder->build();
        parent::__construct($name, $data, $dataName);
    }

    /**
     * get private method
     *
     * @param string $methodName
     * @return ReflectionMethod
     * @internal param $name
     */
    protected static function getMethod(string $methodName)
    {
        $class = new ReflectionClass('Views\CliView');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @dataProvider arrayForPrepareProvider
     */
    public function testPrepareArrayToPrint($data, $expected)
    {
        $cliView = $this->container->get('Views\CliView');
        $privateMethodForTest = static::getMethod('prepareArrayToPrint');
        $privateMethodForTest->invokeArgs($cliView, [&$data]);
        $this->assertEquals($expected, $data);
    }

    public function arrayForPrepareProvider()
    {
        return [
            'data' => [

                'simpleData' => array (
                    0 =>
                        array (
                            '_id' =>
                                array (
                                    'oid' => '59930459326cd606aa3b52ea',
                                ),
                            'firstName' => 'firstName96',
                            'lastName' => 'lastName58',
                            'age' => 78,
                            'gender' => 'male',
                            'hobby' =>
                                array (
                                    0 => 'hobby29',
                                    1 => 'hobby70',
                                    2 => 'hobby42',
                                ),
                        ),
                    1 =>
                        array (
                            '_id' =>
                                array (
                                    'oid' => '59930459326cd606aa3b52f4',
                                ),
                            'firstName' => 'firstName73',
                            'lastName' => 'lastName58',
                            'age' => 23,
                            'gender' => 'male',
                            'hobby' =>
                                array (
                                    0 => 'hobby49',
                                    1 => 'hobby40',
                                    2 => 'hobby71',
                                ),
                        ),
                    2 =>
                        array (
                            '_id' =>
                                array (
                                    'oid' => '59930489326cd606e277bd52',
                                ),
                            'firstName' => 'firstName82',
                            'lastName' => 'lastName24',
                            'age' => 60,
                            'gender' => 'female',
                            'hobby' =>
                                array (
                                    0 => 'hobby72',
                                    1 => 'hobby31',
                                    2 => 'hobby63',
                                ),
                        ),
                ),
                'result' => array (
                    0 =>
                        array (
                            '_id' => 'oid:59930459326cd606aa3b52ea',
                            'firstName' => 'firstName96',
                            'lastName' => 'lastName58',
                            'age' => 78,
                            'gender' => 'male',
                            'hobby' => '0:hobby29, 1:hobby70, 2:hobby42',
                        ),
                    1 =>
                        array (
                            '_id' => 'oid:59930459326cd606aa3b52f4',
                            'firstName' => 'firstName73',
                            'lastName' => 'lastName58',
                            'age' => 23,
                            'gender' => 'male',
                            'hobby' => '0:hobby49, 1:hobby40, 2:hobby71',
                        ),
                    2 =>
                        array (
                            '_id' => 'oid:59930489326cd606e277bd52',
                            'firstName' => 'firstName82',
                            'lastName' => 'lastName24',
                            'age' => 60,
                            'gender' => 'female',
                            'hobby' => '0:hobby72, 1:hobby31, 2:hobby63',
                        ),
                )
            ],
        ];
    }

    public function testPrintErrorInCli()
    {
        $cliView = $this->container->get('Views\CliView');
        $this->expectOutputString('');
        $cliView->printErrorInCli('');
    }
}
