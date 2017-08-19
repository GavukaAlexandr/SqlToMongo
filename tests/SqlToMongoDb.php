<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16.08.17
 * Time: 20:57
 */

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class SqlToMongoDbTest extends TestCase
{
    public function setUp()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            __DIR__ .
            '../../Config/DependenceInjectionConfig.php');
        $containerBuilder->useAnnotations(true);
        $containerBuilder->useAutowiring(true);
        $container = $containerBuilder->build();

        $container->injectOn($this);

        parent::setUp();
    }

//    /**
//     * @param array $where
//     * @param array $conditions
//     * @param array $filter
//     * @param null $logicalOperator
//     */
//    public function TestPrepareConditions(
//        array $where,
//        array $conditions,
//        array &$filter,
//        $logicalOperator = null)
//    {
//
//    }
}
