<?php

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class SqlWhereToMongoConditionTest extends TestCase
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
        $class = new ReflectionClass('ConvertSqlToMongo\SqlWhereToMongoCondition');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @dataProvider whereProvider
     * @param array $where
     * @param array $expected
     */
    public function testPrepareConditions(array $where, array $expected)
    {
        $sqlWhereToMongoCondition = $this->container->get('ConvertSqlToMongo\SqlWhereToMongoCondition');
        $result = $sqlWhereToMongoCondition->prepareConditions($where);
        $this->assertEquals($expected, $result);
    }

    public function whereProvider(): array
    {
        return [
            'where' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 => [
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ],
                    2 => [
                        'expr_type' => 'const',
                        'base_expr' => '18',
                        'sub_tree' => false,
                    ],
                ],
                'result' => [
                    'age' => [
                        '$gt' => '18',
                    ],
                ],
            ],
            'whereAnd' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 => [
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ],
                    2 => [
                        'expr_type' => 'const',
                        'base_expr' => '18',
                        'sub_tree' => false,
                    ],
                    3 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'AND',
                        'sub_tree' => false,
                    ],
                    4 => [
                        'expr_type' => 'colref',
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
                    ],
                    5 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    6 => [
                        'expr_type' => 'const',
                        'base_expr' => "Alexandr",
                        'sub_tree' => false,
                    ],

                ],
                'result' => [
                    'age' => [
                        '$gt' => '18',
                    ],
                    'firstName' => [
                        '$eq' => 'Alexandr'
                    ],
                ],
            ],
            'whereAndAnd' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 => [
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ],
                    2 => [
                        'expr_type' => 'const',
                        'base_expr' => '18',
                        'sub_tree' => false,
                    ],
                    3 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'AND',
                        'sub_tree' => false,
                    ],
                    4 => [
                        'expr_type' => 'colref',
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
                    ],
                    5 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    6 => [
                        'expr_type' => 'const',
                        'base_expr' => "Alexandr",
                        'sub_tree' => false,
                    ],
                    7 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'AND',
                        'sub_tree' => false,
                    ],
                    8 => [
                        'expr_type' => 'colref',
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
                    ],
                    9 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    10 => [
                        'expr_type' => 'const',
                        'base_expr' => "Gavuka",
                        'sub_tree' => false,
                    ],


                ],
                'result' => [
                    'age' => [
                        '$gt' => '18',
                    ],
                    'firstName' => [
                        '$eq' => 'Alexandr'
                    ],
                    'lastName' => [
                        '$eq' => 'Gavuka'
                    ],
                ],
            ],
            'whereOr' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 => [
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ],
                    2 => [
                        'expr_type' => 'const',
                        'base_expr' => '18',
                        'sub_tree' => false,
                    ],
                    3 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'OR',
                        'sub_tree' => false,
                    ],
                    4 => [
                        'expr_type' => 'colref',
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
                    ],
                    5 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    6 => [
                        'expr_type' => 'const',
                        'base_expr' => "Alexandr",
                        'sub_tree' => false,
                    ],

                ],
                'result' => [
                    'age' =>
                        [
                            '$gt' => '18',
                        ],
                    '$or' =>
                        [
                            0 =>
                                [
                                    'firstName' =>
                                        [
                                            '$eq' => 'Alexandr',
                                        ],
                                ],
                        ],
                ],
            ],
            'whereOrOr' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 => [
                        'expr_type' => 'operator',
                        'base_expr' => '>',
                        'sub_tree' => false,
                    ],
                    2 => [
                        'expr_type' => 'const',
                        'base_expr' => '18',
                        'sub_tree' => false,
                    ],
                    3 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'OR',
                        'sub_tree' => false,
                    ],
                    4 => [
                        'expr_type' => 'colref',
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
                    ],
                    5 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    6 => [
                        'expr_type' => 'const',
                        'base_expr' => "Alexandr",
                        'sub_tree' => false,
                    ],
                    7 => [
                        'expr_type' => 'operator',
                        'base_expr' => 'OR',
                        'sub_tree' => false,
                    ],
                    8 => [
                        'expr_type' => 'colref',
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
                    ],
                    9 => [
                        'expr_type' => 'operator',
                        'base_expr' => '=',
                        'sub_tree' => false,
                    ],
                    10 => [
                        'expr_type' => 'const',
                        'base_expr' => "Inna",
                        'sub_tree' => false,
                    ],

                ],
                'result' => [
                    'age' => [
                        '$gt' => '18',
                    ],
                    '$or' => [
                        0 => [
                            'firstName' =>
                                [
                                    '$eq' => 'Alexandr',
                                ],
                        ],
                        1 => [
                            'firstName' => [
                                '$eq' => 'Inna',
                            ],
                        ],
                    ],
                ],
            ],
            'whereAndOr' => [
                'where' => [
                    0 => [
                        'expr_type' => 'colref',
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
                    ],
                    1 =>
                        [
                            'expr_type' => 'operator',
                            'base_expr' => '>',
                            'sub_tree' => false,
                        ],
                    2 =>
                        [
                            'expr_type' => 'const',
                            'base_expr' => '18',
                            'sub_tree' => false,
                        ],
                    3 =>
                        [
                            'expr_type' => 'operator',
                            'base_expr' => 'AND',
                            'sub_tree' => false,
                        ],
                    4 =>
                        [
                            'expr_type' => 'bracket_expression',
                            'base_expr' => '(firstName=Alexandr OR firstName=Inna)',
                            'sub_tree' =>
                                [
                                    0 =>
                                        [
                                            'expr_type' => 'colref',
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
                                        ],
                                    1 =>
                                        [
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ],
                                    2 =>
                                        [
                                            'expr_type' => 'const',
                                            'base_expr' => 'Alexandr',
                                            'sub_tree' => false,
                                        ],
                                    3 =>
                                        [
                                            'expr_type' => 'operator',
                                            'base_expr' => 'OR',
                                            'sub_tree' => false,
                                        ],
                                    4 =>
                                        [
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
                                        ],
                                    5 =>
                                        [
                                            'expr_type' => 'operator',
                                            'base_expr' => '=',
                                            'sub_tree' => false,
                                        ],
                                    6 =>
                                        [
                                            'expr_type' => 'const',
                                            'base_expr' => 'Inna',
                                            'sub_tree' => false,
                                        ],
                                ],

                        ],

                ],
                'result' => [
                    'age' =>
                        [
                            '$gt' => '18',
                        ],
                    '$or' =>
                        [
                            0 => [
                                'firstName' => [
                                    '$eq' => 'Alexandr',
                                ],
                            ],
                            1 => [
                                'firstName' => [
                                    '$eq' => 'Inna',
                                ],
                            ],
                        ],
                ],
            ],
        ];
    }
}
