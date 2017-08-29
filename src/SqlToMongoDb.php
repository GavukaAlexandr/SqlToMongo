<?php

use DataBase\ConnectionInterface;
use DataBase\MongoDbConnection;
use League\CLImate\CLImate;
use MongoDB\Client;
use PHPSQLParser\PHPSQLParser;

class SqlToMongoDb
{
    private const EQ = '$eq';
    private const NE = '$ne';
    private const GT = '$gt';
    private const GTE = '$gte';
    private const LT = '$lt';
    private const LTE = '$lte';

    public $conditions = [
        '=' => self::EQ,
        '<>' => self::NE,
        '>' => self::GT,
        '>=' => self::GTE,
        '<' => self::LT,
        '<=' => self::LTE,
    ];

    /**
     * @var Client|MongoDbConnection
     */
    private $connection;

    /**
     * @var PHPSQLParser
     */
    private $parser;

    /**
     * @var CLImate
     */
    private $cliMate;

    /**
     * @param ConnectionInterface|MongoDbConnection $connection
     * @param PHPSQLParser $parser
     * @param CLImate $cliMate
     */
    public function __construct(
        ConnectionInterface $connection,
        PHPSQLParser $parser,
        CLImate $cliMate)
    {
        $this->connection = $connection;
        $this->parser = $parser;
        $this->cliMate = $cliMate;
    }

    public function run()
    {
        $sql = $this->getSql();
        $parsedSql = $this->parseSql((string)$sql);

        $data = $this->prepareMongoQuery($parsedSql);
    }

    /**
     * SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 AND (firstName='Alexandr' OR firstName='Inna') ORDER BY age ASC, firstName DESC, lastName ASC SKIP 3 LIMIT 5
     *
     * @param array $parsedSql
     * @return array
     */
    public function prepareMongoQuery(array $parsedSql)
    {
        $filter = [];
        $options = [];

        if (array_key_exists('SELECT', $parsedSql)) {
            $options['projection'] = $this->prepareSelect($parsedSql['SELECT']);
        }

        if (array_key_exists('FROM', $parsedSql)) {
            $collectionName = $parsedSql['FROM']['0']['table'];
        } else {
            $this->printError('statements "FROM" missing');
        }

        if (array_key_exists('WHERE', $parsedSql)) {
            $filter = $this->prepareConditions($parsedSql['WHERE']);
        }

        if (array_key_exists('ORDER BY', $parsedSql)) {
            $options['sort'] = $this->prepareSort($parsedSql['ORDER']);
        }
        if (array_key_exists('SKIP', $parsedSql)) {
            $options['skip'] = $parsedSql['SKIP']['1'];
        }
        if (array_key_exists('LIMIT', $parsedSql)) {
            $options['limit'] = $parsedSql['LIMIT']['rowcount'];
        }

        $dbName = $this->connection->getConfig()->getDbName();

        /** @var Client $connection */
        $collection = $this->connection
            ->getConnection()
            ->$dbName
            ->$collectionName;
        $data = $collection->find($filter, $options)->toArray();

        return $data;
    }

    /**
     * @param array $orderBy
     * @return array
     * @internal param $parsedSql
     */
    private function prepareSort(array $orderBy): array
    {
        //todo replace array_walk in array_map
        $column = array_column($orderBy, 'base_expr');
        $sortParams = array_column($orderBy, 'direction');
        $sortColumn = array_combine($column, $sortParams);
        array_walk($sortColumn,
            function (&$value) {
                if ($value === 'ASC') {
                    $value = 1;
                }

                if ($value === 'DESC') {
                    $value
                        = -1;
                }
            });

        return $sortColumn;
    }

    /**
     * @param array $select
     * @return array
     */
    private function prepareSelect(array $select): array
    {
        //todo implement *, field, field.subfield, field.*
        $documentFields = array_column($select, 'base_expr');

        if ($documentFields['0'] === '*') {
            return [];
        } else {
            $preparedDocumentFields = array_flip($documentFields);

            array_walk($preparedDocumentFields,
                function (&$value) {
                    $value = 1;
                });
            return $preparedDocumentFields;
        }
    }

    /**
     * Recursive handle conditions
     *
     * @param array $where
     * @param array $filter
     * @param null $logicalOperator AND or OR
     * @return array
     */
    private function prepareConditions(
        array $where,
        array &$filter = [],
        /*$logicalOperator = null,*/
        $bracketExpression = false): array
    {
        //todo rewrite without foreach
//        /** field name */
//        $fieldName = null;
//
//        /** EQ, NE, GT, GTE, LT, LTE */
//        $operator = null;
//
//        /** field value */
//        $fieldValue = null;

        //todo WHERE отделить первые 3 елемента, вызвать метод prepare() для составления условия
        if ($where['0']['expr_type'] === 'colref' &&
            $where['1']['expr_type'] === 'operator' &&
            $where['2']['expr_type'] === 'const') {

            if ($bracketExpression === true) {
                $this->prepareOr($filter, $where);
            } else {
                $this->prepareWhereAnd($filter, $where);
            }

        }

        if (count($where) <= 0) {
            return $filter;
        }

        //todo AND(-OR-) вызов самого себя передав sub_tree
        if ($where['0']['base_expr'] === 'AND' && $where['1']['expr_type'] === 'bracket_expression') {

            /** delete operator AND from array */
            array_splice($where,0,1);

            /** recursive call for sub_tree bracket_expression */
            $this->prepareConditions($where['0']['sub_tree'], $filter, true);
            $bracketExpression = false;

            /** delete sub tree of element from array */
            array_splice($where,0,1);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        if ($where['0']['base_expr'] === 'OR') {
            array_splice($where, 0,1);
            $this->prepareOr($filter, $where);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        if ($where['0']['base_expr'] === 'AND') {
            array_splice($where, 0,1);
            $this->prepareWhereAnd($filter, $where);
        }

        if (count($where) > 0){
            $this->prepareConditions($where, $filter);
        }

//        foreach ($where as $key => $item) {
//            /** Field and Value*/
//            if ($item['expr_type'] === 'colref') {
//                if ($fieldName !== null) {
//                    if (is_numeric($item['base_expr'])) {
//                        $fieldValue = (int)$item['base_expr'];
//                    } else {
//                        $fieldValue = (string)$item['base_expr'];
//                    }
//                } else {
//                    $fieldName = $item['base_expr'];
//                    continue;
//
//                }
//            }
//
//            /** Operator */
//            if ($item['expr_type'] === 'operator') {
//                if ($item['base_expr'] === 'AND' || $item['base_expr'] === 'OR') {
//                    $logicalOperator = $item['base_expr'];
//                }
//
//                if (array_key_exists($item['base_expr'], $this->conditions)) {
//                    $operator = $this->conditions[$item['base_expr']];
//                }
//
//                continue;
//            }
//
//            /** value */
//            if ($item['expr_type'] === 'const') {
//                if (is_numeric($item['base_expr'])) {
//                    $fieldValue = (int)$item['base_expr'];
//                } else {
//                    $fieldValue = (string)$item['base_expr'];
//                }
//            }
//
//            /** aggregation elements in filter[] */
//            if ($fieldName !== null &&
//                $operator !== null &&
//                $fieldValue !== null) {
//
//                /** WHERE aggregation */
//                if ($logicalOperator === null) {
//                    $filter[$fieldName] = [$operator => $fieldValue];
//
//                    list($fieldName, $operator, $fieldValue) = null;
//                    continue;
//                }
//
//                /** OR aggregation */
//                if ($logicalOperator === 'OR') {
//                    $filter['$or'][] = [$fieldName => [$operator => $fieldValue]];
//                }
//
//                /** AND aggregation */
//                if ($logicalOperator === 'AND' &&
//                    $item['expr_type'] === 'const' &&
//                    empty($item['sub_tree'])) {
//
//                    $filter[$fieldName] = [$operator => $fieldValue];
//                }
//
//                list($fieldName, $operator, $fieldValue) = null;
//            }
//
//            /** AND + OR aggregation */
//            if ($logicalOperator === 'AND' &&
//                $item['expr_type'] === 'bracket_expression' &&
//                !empty($item['sub_tree']) &&
//                $item['sub_tree'][3]['base_expr'] === 'OR') {
//
//                $this->prepareConditions($item['sub_tree'], $filter, $logicalOperator = 'OR');
//            }
//        }

        return $filter;
    }

    private function prepareWhereAnd(array &$filter, array &$where)
    {
        /** @var array $elements = conditionsElements */
        $elements = $this->getConditionsElements($where);

            $filter[$elements['column']] = [$elements['operator'] => $elements['value']];
    }

    private function prepareOr(array &$filter, array &$where)
    {
        /** @var array $elements = conditionsElements */
        $elements = $this->getConditionsElements($where);

            $filter['$or'][] = [
                $elements['column'] => [
                    $elements['operator'] => $elements['value']
                ]
            ];

    }

    /**
     * get conditions operator
     *
     * @param string $operator
     * @return mixed
     */
    public function getOperator(string $operator)
    {
        if (array_key_exists($operator, $this->conditions)) {
            return $this->conditions[$operator];
        } else {
            $this->printError('Conditions operator' . "$operator" . 'not valid!');
        }
    }

    public function getConditionsElements(array &$where): array
    {
        /** @var array $condEl = conditionsElements */
        $condEl = array_splice($where, 0, 3);

        $elements['column'] = $condEl['0']['base_expr'];
        $elements['operator'] = $this->getOperator($condEl['1']['base_expr']);

        if (is_numeric($condEl['2']['base_expr'])) {
            $elements['value'] = (int) $condEl['2']['base_expr'];
        } else {
            $elements['value'] = (string) $condEl['2']['base_expr'];
        }

        return $elements;
    }

    private function printError(
        string $message = 'SQL is not correct, please enter the correct SQL'): void
    {
        $this->cliMate->error($message);
        $this->run();
    }

    /**
     * @param string $sql
     * @return array
     */
    public function parseSql(string $sql): array
    {
        $this->parser->addCustomFunction('SKIP');

        $parsedSql = $this->parser->parse($sql);
        if ($parsedSql === false) {
            $this->printError();
        }
        return $parsedSql;
    }

    /**
     * @return string
     */
    private function getSql(): string
    {
        for ($i = true; $i === true;) {
            $sql = readline('SQL to MongoDB > ');
            if (!empty($sql)) {
                break;
            }
        }

        return $sql;
    }

    public function view($data)
    {
        //todo сделать интерфейс с методом для отображения данных
        //todo вывести данные в виде таблицы
    }
}
