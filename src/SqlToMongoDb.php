<?php

use DataBase\ConnectionInterface;
use DataBase\MongoDbConnection;
use MongoDB\Client;
use PHPSQLParser\PHPSQLParser;
use Views\CliView;

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
     * @var CliView
     */
    private $cliView;

    /**
     * @param ConnectionInterface|MongoDbConnection $connection
     * @param PHPSQLParser $parser
     * @param CliView $cliView
//     * @internal param CLImate $cliMate
     */
    public function __construct(
        ConnectionInterface $connection,
        PHPSQLParser $parser,
        CliView $cliView)
    {
        $this->connection = $connection;
        $this->parser = $parser;
        $this->cliView = $cliView;
    }

    public function run()
    {
        $sql = $this->getSql();
        $parsedSql = $this->parseSql((string) $sql);
        $this->uppercaseOperators($parsedSql);

        /** get prepared query for MongoDB */
        $settings = $this->prepareMongoQuery($parsedSql);

        /** execute query */
        $dbName = $this->connection->getConfig()->getDbName();
        $collectionName = $settings['collectionName'];

        /** @var Client $connection */
        $collection = $this->connection
            ->getConnection()
            ->$dbName
            ->$collectionName;
        $data = $collection->find($settings['filter'], $settings['options'])->toArray();

        $this->cliView->render($data);


        $this->run();
    }

    /**
     * prepare SQL to array MongoDB query
     *
     * Example SQL statement
     * SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 AND (firstName='Alexandr' OR firstName='Inna') ORDER BY age ASC, firstName DESC, lastName ASC SKIP 3 LIMIT 5
     * SELECT * FROM User WHERE age>20 AND( gender=female or lastName=lastName58 )
     *
     * @param array $parsedSql
     * @return array
     */
    public function prepareMongoQuery(array $parsedSql)
    {
        $filter = [];
        $options = [];
        $collectionName = null;

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
            $options['skip'] = (int) $parsedSql['SKIP']['1'];
        }
        if (array_key_exists('LIMIT', $parsedSql)) {
            $options['limit'] = (int) $parsedSql['LIMIT']['rowcount'];
        }

        $data['filter'] = $filter;
        $data['options'] = $options;
        $data['collectionName'] = $collectionName;

        return $data;
    }

    /**
     * recursive uppercase operators in parsed SQL
     *
     * @param $parsedSql
     */
    private function uppercaseOperators(&$parsedSql)
    {
        foreach ($parsedSql as &$element) {
            if (is_array($element)) {
                if (array_key_exists('expr_type', $element)){
                    if ($element['expr_type'] === 'operator'){
                        $element['base_expr'] = strtoupper($element['base_expr']);
                    }

                    if ($element['sub_tree'] !== false) {
                        $this->uppercaseOperators($element['sub_tree']);
                    }
                } else {
                    if (is_array($element)){
                        $this->uppercaseOperators($element);
                    }
                }
            }

        }
    }

    /**
     * prepare sort(ORDER BY) statement
     *
     * @param array $orderBy
     * @return array
     * @internal param $parsedSql
     */
    private function prepareSort(array $orderBy): array
    {
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
     * prepare projection for MongoDB query
     *
     * @param array $select
     * @return array
     */
    private function prepareSelect(array $select): array
    {
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
     * Recursive prepare conditions WHERE, AND, OR, AND ( - OR - )
     *
     * @param array $where
     * @param array $filter
     * @param bool $bracketExpression
     * @return array
     * @internal param null $logicalOperator AND or OR
     */
    private function prepareConditions(
        array $where,
        array &$filter = [],
        $bracketExpression = false): array
    {
        if ($where['0']['expr_type'] === 'colref' &&
            $where['1']['expr_type'] === 'operator' &&
            $where['2']['expr_type'] === 'const' ||
            $where['2']['expr_type'] === 'colref') {

            if ($bracketExpression === true) {
                $this->prepareOr($filter, $where);
            } else {
                $this->prepareWhereAnd($filter, $where);
            }

        }

        if (count($where) <= 0) {
            return $filter;
        }

        /** recursive handle bracket_expression AND( - OR - ) */
        if ($where['0']['base_expr'] === 'AND' && $where['1']['expr_type'] === 'bracket_expression') {

            /** delete operator AND from array */
            array_splice($where,0,1);

            /** recursive call for sub_tree bracket_expression */
            $this->prepareConditions($where['0']['sub_tree'], $filter, true);

            /** for recursive processing of operations in brackets */
            if ($bracketExpression === true) {
                unset($bracketExpression);
            }

            /** delete sub tree of element from array */
            array_splice($where,0,1);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        /** prepare OR */
        if ($where['0']['base_expr'] === 'OR') {
            array_splice($where, 0,1);
            $this->prepareOr($filter, $where);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        /** prepare AND */
        if ($where['0']['base_expr'] === 'AND') {
            array_splice($where, 0,1);
            $this->prepareWhereAnd($filter, $where);
        }

        /** if array where not empty, prepareConditions() will be called again */
        if (count($where) > 0){
            $this->prepareConditions($where, $filter);
        }

        return $filter;
    }

    /**
     * prepare WHERE and AND conditions
     *
     * @param array $filter
     * @param array $where
     */
    private function prepareWhereAnd(array &$filter, array &$where)
    {
        /** @var array $elements = conditionsElements */
        $elements = $this->getConditionsElements($where);

            $filter[$elements['column']] = [$elements['operator'] => $elements['value']];
    }

    /**
     * prepare OR conditions
     *
     * @param array $filter
     * @param array $where
     */
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
     * prepare elements for conditions
     *
     * @param array $where
     * @return array
     */
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

    /**
     * getting and validation conditions operator
     *
     * @param string $operator
     * @return mixed
     */
    public function getOperator(string $operator)
    {
        if (array_key_exists($operator, $this->conditions)) {
            $result = $this->conditions[$operator];
        } else {
            $this->printError('Conditions operator' . "$operator" . 'not valid!');
        }

        /** @var string $result */
        return $result;
    }

    /**
     * print error and write greeting for input in CLI
     *
     * @param string $message
     */
    private function printError(string $message = 'SQL is not correct, please enter the correct SQL'): void
    {
        $this->cliView->printErrorInCli($message);
        $this->run();
    }

    /**
     * repeat greeting for input in CLI while the valid SQL hasn't input
     *
     * @return string
     */
    private function getSql(): string
    {
        $sql = null;
        while (empty($sql)) {
            $sql = readline('SQL to MongoDB >>> ');
        }

        /** @var string $sql */
        return $sql;
    }

    /**
     * @param string $sql
     * @return array
     */
    public function parseSql(string $sql): array
    {
        $parsedSql = $this->parser->parse($sql);
        if ($parsedSql === false) {
            $this->printError();
        }
        return $parsedSql;
    }
}
