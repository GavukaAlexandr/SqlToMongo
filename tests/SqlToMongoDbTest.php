<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 16.08.17
 * Time: 20:57
 */

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class SqlToMongoDbTest extends TestCase
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
//        $containerBuilder->useAnnotations(true);
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
        $class = new ReflectionClass('SqlToMongoDb');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @dataProvider selectProvider
     * @param array $select
     * @param array $result
     */
    public function testPrepareSelect(array $select, array $result)
    {
        $sqlToMongoDb = $this->container->get('SqlToMongoDb');

        $privateMethodForTest = static::getMethod('prepareSelect');
        $selectFields = $privateMethodForTest->invokeArgs($sqlToMongoDb, $select);
        $this->assertEquals($result, $selectFields);
    }

    public function selectProvider(): array
    {
        return [
            'select' => [
                [
                    [
                        '0' =>
                            [
                                'expr_type' => 'colref',
                                'alias' => false,
                                'base_expr' => 'firstName',
                                'no_quotes' =>
                                    [
                                        'delim' => false,
                                        'parts' =>
                                            [
                                                0 => 'firstName',
                                            ],
                                    ],
                                'sub_tree' => false,
                                'delim' => ',',
                            ],
                        '1' =>
                            [
                                'expr_type' => 'colref',
                                'alias' => false,
                                'base_expr' => 'lastName',
                                'no_quotes' =>
                                    [
                                        'delim' => false,
                                        'parts' =>
                                            [
                                                0 => 'lastName',
                                            ],
                                    ],
                                'sub_tree' => false,
                                'delim' => ',',
                            ],
                        '2' =>
                            [
                                'expr_type' => 'colref',
                                'alias' => false,
                                'base_expr' => 'age',
                                'no_quotes' =>
                                    [
                                        'delim' => false,
                                        'parts' =>
                                            [
                                                0 => 'age',
                                            ],
                                    ],
                                'sub_tree' => false,
                                'delim' => false,
                            ],
                    ]],
                'result' => [
                    'firstName' => 1,
                    'lastName' => 1,
                    'age' => 1,
                ],
            ],
            'selectAll' => [
                [[
                    0 =>
                        [
                            'expr_type' => 'colref',
                            'alias' => false,
                            'base_expr' => '*',
                            'sub_tree' => false,
                            'delim' => false,
                        ],
                ]],
                'result' => [],
            ],
        ];
    }

    /**
     * @dataProvider sortProvider
     * @param array $sort
     * @param array $result
     */
    public function testPrepareSort(array $sort, array $result)
    {
        $sqlToMongoDb = $this->container->get('SqlToMongoDb');

        $privateMethodForTest = static::getMethod('prepareSort');
        $selectFields = $privateMethodForTest->invokeArgs($sqlToMongoDb, $sort);
        $this->assertEquals($result, $selectFields);
    }


    public function sortProvider()
    {
        return [
            'sortASC' => [
                [
                    'sort' => [
                        0 => [
                            'expr_type' => 'colref',
                            'base_expr' => 'age',
                            'no_quotes' => [
                                'delim' => false,
                                'parts' => [
                                    0 => 'age',
                                ],
                            ],
                            'sub_tree' => false,
                            'direction' => 'ASC',
                        ],
                        1 => [
                            'expr_type' => 'colref',
                            'base_expr' => 'firstName',
                            'no_quotes' => [
                                'delim' => false,
                                'parts' => [
                                    0 => 'firstName',
                                ],
                            ],
                            'sub_tree' => false,
                            'direction' => 'DESC',
                        ],
                        2 => [
                            'expr_type' => 'colref',
                            'base_expr' => 'lastName',
                            'no_quotes' => [
                                'delim' => false,
                                'parts' => [
                                    0 => 'lastName',
                                ],
                            ],
                            'sub_tree' => false,
                            'direction' => 'ASC',
                        ],

                    ],
                ],
                'result' => [
                    'age' => 1,
                    'firstName' => -1,
                    'lastName' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider operatorsProvider
     * @param array &$operators
     * @param array $expected
     */
    public function testUpperCaseOperators(array $operators, array $expected)
    {
        $sqlToMongoDb = $this->container->get('SqlToMongoDb');

        $privateMethodForTest = static::getMethod('uppercaseOperators');
        $privateMethodForTest->invokeArgs($sqlToMongoDb, [&$operators]);
        $this->assertEquals($expected, $operators);
    }

    public function operatorsProvider()
    {
        return [
            'operators' => [

                'operator' => array(
                    0 => array(
                        'expr_type' => 'colref',
                        'base_expr' => 'age',
                        'no_quotes' =>
                            array(
                                'delim' => false,
                                'parts' =>
                                    array(
                                        0 => 'age',
                                    ),
                            ),
                        'sub_tree' => false,
                    ),
                    1 => array(
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ),
                    2 =>
                        array(
                            'expr_type' => 'const',
                            'base_expr' => '20',
                            'sub_tree' => false,
                        ),
                    3 =>
                        array(
                            'expr_type' => 'operator',
                            'base_expr' => 'AND',
                            'sub_tree' => false,
                        ),
                    4 =>
                        array(
                            'expr_type' => 'bracket_expression',
                            'base_expr' => '( gender=female or lastName=lastName58 )',
                            'sub_tree' =>
                                array(
                                    0 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'gender',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'gender',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    1 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ),
                                    2 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'female',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'female',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    3 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => 'or',
                                            'sub_tree' => false,
                                        ),
                                    4 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'lastName',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'lastName',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    5 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ),
                                    6 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'lastName58',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'lastName58',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                ),
                        ),
                ),

                'result' => array(
                    0 => array(
                        'expr_type' => 'colref',
                        'base_expr' => 'age',
                        'no_quotes' =>
                            array(
                                'delim' => false,
                                'parts' =>
                                    array(
                                        0 => 'age',
                                    ),
                            ),
                        'sub_tree' => false,
                    ),
                    1 =>
                        array(
                            'expr_type' => 'operator',
                            'base_expr' => '>',
                            'sub_tree' => false,
                        ),
                    2 =>
                        array(
                            'expr_type' => 'const',
                            'base_expr' => '20',
                            'sub_tree' => false,
                        ),
                    3 =>
                        array(
                            'expr_type' => 'operator',
                            'base_expr' => 'AND',
                            'sub_tree' => false,
                        ),
                    4 =>
                        array(
                            'expr_type' => 'bracket_expression',
                            'base_expr' => '( gender=female or lastName=lastName58 )',
                            'sub_tree' =>
                                array(
                                    0 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'gender',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'gender',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    1 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ),
                                    2 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'female',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'female',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    3 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => 'OR',
                                            'sub_tree' => false,
                                        ),
                                    4 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'lastName',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'lastName',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                    5 =>
                                        array(
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ),
                                    6 =>
                                        array(
                                            'expr_type' => 'colref',
                                            'base_expr' => 'lastName58',
                                            'no_quotes' =>
                                                array(
                                                    'delim' => false,
                                                    'parts' =>
                                                        array(
                                                            0 => 'lastName58',
                                                        ),
                                                ),
                                            'sub_tree' => false,
                                        ),
                                ),
                        ),
                )
            ],
        ];
    }

    /**
     * @dataProvider sqlToMongoQueryProvider
     * @param array $query
     * @param $expected
     */
    public function testPrepareMongoQuery(array $query, array $expected)
    {
        $sqlToMongoDb = $this->container->get('SqlToMongoDb');

        $privateMethodForTest = static::getMethod('prepareMongoQuery');
        $result = $privateMethodForTest->invokeArgs($sqlToMongoDb, [$query]);
        $this->assertEquals($expected, $result);
    }

    public function sqlToMongoQueryProvider()
    {
        return [
            'queryWithAllOperators' => [

                'data' => array(
                    'SELECT' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'firstName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'firstName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => ',',
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'lastName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'lastName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => ',',
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => false,
                                ),
                        ),
                    'FROM' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'table',
                                    'table' => 'SqlToMongo',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'SqlToMongo',
                                                ),
                                        ),
                                    'alias' => false,
                                    'hints' => false,
                                    'join_type' => 'JOIN',
                                    'ref_type' => false,
                                    'ref_clause' => false,
                                    'base_expr' => 'SqlToMongo',
                                    'sub_tree' => false,
                                ),
                        ),
                    'WHERE' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'operator',
                                    'base_expr' => '>',
                                    'sub_tree' => false,
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'const',
                                    'base_expr' => '18',
                                    'sub_tree' => false,
                                ),
                            3 =>
                                array(
                                    'expr_type' => 'operator',
                                    'base_expr' => 'AND',
                                    'sub_tree' => false,
                                ),
                            4 =>
                                array(
                                    'expr_type' => 'bracket_expression',
                                    'base_expr' => '(firstName=\'name1\' OR firstName=\'name2\')',
                                    'sub_tree' =>
                                        array(
                                            0 =>
                                                array(
                                                    'expr_type' => 'colref',
                                                    'base_expr' => 'firstName',
                                                    'no_quotes' =>
                                                        array(
                                                            'delim' => false,
                                                            'parts' =>
                                                                array(
                                                                    0 => 'firstName',
                                                                ),
                                                        ),
                                                    'sub_tree' => false,
                                                ),
                                            1 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => '=',
                                                    'sub_tree' => false,
                                                ),
                                            2 =>
                                                array(
                                                    'expr_type' => 'const',
                                                    'base_expr' => '\'name1\'',
                                                    'sub_tree' => false,
                                                ),
                                            3 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => 'OR',
                                                    'sub_tree' => false,
                                                ),
                                            4 =>
                                                array(
                                                    'expr_type' => 'colref',
                                                    'base_expr' => 'firstName',
                                                    'no_quotes' =>
                                                        array(
                                                            'delim' => false,
                                                            'parts' =>
                                                                array(
                                                                    0 => 'firstName',
                                                                ),
                                                        ),
                                                    'sub_tree' => false,
                                                ),
                                            5 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => '=',
                                                    'sub_tree' => false,
                                                ),
                                            6 =>
                                                array(
                                                    'expr_type' => 'const',
                                                    'base_expr' => '\'name2\'',
                                                    'sub_tree' => false,
                                                ),
                                        ),
                                ),
                        ),
                    'ORDER' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'ASC',
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'firstName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'firstName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'DESC',
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'lastName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'lastName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'ASC',
                                ),
                        ),
                    'SKIP' =>
                        array(
                            0 => ' ',
                            1 => '3',
                            2 => ' ',
                        ),
                    'LIMIT' =>
                        array(
                            'offset' => '',
                            'rowcount' => '5',
                        ),
                ),
                'result' => array (
                    'filter' =>
                        array (
                            'age' =>
                                array (
                                    '$gt' => 18,
                                ),
                            '$or' =>
                                array (
                                    0 =>
                                        array (
                                            'firstName' =>
                                                array (
                                                    '$eq' => '\'name1\'',
                                                ),
                                        ),
                                    1 =>
                                        array (
                                            'firstName' =>
                                                array (
                                                    '$eq' => '\'name2\'',
                                                ),
                                        ),
                                ),
                        ),
                    'options' =>
                        array (
                            'projection' =>
                                array (
                                    'firstName' => 1,
                                    'lastName' => 1,
                                    'age' => 1,
                                ),
                            'sort' =>
                                array (
                                    'age' => 1,
                                    'firstName' => -1,
                                    'lastName' => 1,
                                ),
                            'skip' => 3,
                            'limit' => 5,
                        ),
                    'collectionName' => 'SqlToMongo',
                )
            ],
        ];
    }

    /**
     * @dataProvider sqlQueryProvider
     * @param $query
     * @param $expected
     */
    public function testParseSql(string $query, array $expected)
    {
        $sqlToMongoDb = $this->container->get('SqlToMongoDb');

        $privateMethodForTest = static::getMethod('parseSql');
        $result = $privateMethodForTest->invokeArgs($sqlToMongoDb, [$query]);
        $this->assertEquals($expected, $result);
    }

    public function sqlQueryProvider()
    {
        return [
            'queryWithAllOperators' => [

                'query' => "SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 AND (firstName='name1' OR firstName='name2') ORDER BY age ASC, firstName DESC, lastName ASC SKIP 3 LIMIT 5",
                'result' => array(
                    'SELECT' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'firstName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'firstName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => ',',
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'lastName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'lastName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => ',',
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'colref',
                                    'alias' => false,
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'delim' => false,
                                ),
                        ),
                    'FROM' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'table',
                                    'table' => 'SqlToMongo',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'SqlToMongo',
                                                ),
                                        ),
                                    'alias' => false,
                                    'hints' => false,
                                    'join_type' => 'JOIN',
                                    'ref_type' => false,
                                    'ref_clause' => false,
                                    'base_expr' => 'SqlToMongo',
                                    'sub_tree' => false,
                                ),
                        ),
                    'WHERE' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'operator',
                                    'base_expr' => '>',
                                    'sub_tree' => false,
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'const',
                                    'base_expr' => '18',
                                    'sub_tree' => false,
                                ),
                            3 =>
                                array(
                                    'expr_type' => 'operator',
                                    'base_expr' => 'AND',
                                    'sub_tree' => false,
                                ),
                            4 =>
                                array(
                                    'expr_type' => 'bracket_expression',
                                    'base_expr' => '(firstName=\'name1\' OR firstName=\'name2\')',
                                    'sub_tree' =>
                                        array(
                                            0 =>
                                                array(
                                                    'expr_type' => 'colref',
                                                    'base_expr' => 'firstName',
                                                    'no_quotes' =>
                                                        array(
                                                            'delim' => false,
                                                            'parts' =>
                                                                array(
                                                                    0 => 'firstName',
                                                                ),
                                                        ),
                                                    'sub_tree' => false,
                                                ),
                                            1 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => '=',
                                                    'sub_tree' => false,
                                                ),
                                            2 =>
                                                array(
                                                    'expr_type' => 'const',
                                                    'base_expr' => '\'name1\'',
                                                    'sub_tree' => false,
                                                ),
                                            3 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => 'OR',
                                                    'sub_tree' => false,
                                                ),
                                            4 =>
                                                array(
                                                    'expr_type' => 'colref',
                                                    'base_expr' => 'firstName',
                                                    'no_quotes' =>
                                                        array(
                                                            'delim' => false,
                                                            'parts' =>
                                                                array(
                                                                    0 => 'firstName',
                                                                ),
                                                        ),
                                                    'sub_tree' => false,
                                                ),
                                            5 =>
                                                array(
                                                    'expr_type' => 'operator',
                                                    'base_expr' => '=',
                                                    'sub_tree' => false,
                                                ),
                                            6 =>
                                                array(
                                                    'expr_type' => 'const',
                                                    'base_expr' => '\'name2\'',
                                                    'sub_tree' => false,
                                                ),
                                        ),
                                ),
                        ),
                    'ORDER' =>
                        array(
                            0 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'age',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'age',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'ASC',
                                ),
                            1 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'firstName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'firstName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'DESC',
                                ),
                            2 =>
                                array(
                                    'expr_type' => 'colref',
                                    'base_expr' => 'lastName',
                                    'no_quotes' =>
                                        array(
                                            'delim' => false,
                                            'parts' =>
                                                array(
                                                    0 => 'lastName',
                                                ),
                                        ),
                                    'sub_tree' => false,
                                    'direction' => 'ASC',
                                ),
                        ),
                    'SKIP' =>
                        array(
                            0 => ' ',
                            1 => '3',
                            2 => ' ',
                        ),
                    'LIMIT' =>
                        array(
                            'offset' => '',
                            'rowcount' => '5',
                        ),
                )
            ],
        ];
    }
}
