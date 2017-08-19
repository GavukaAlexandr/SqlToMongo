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

    private $conditions = [
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
     * SELECT firstName, age
     * FROM User
     * db.getCollection('User').find({}, {firstName: 1, age: 1})
     *
     * SELECT firstName, age
     * FROM User
     * WHERE age > 23
     * db.getCollection('User').find({age: {$gt: ["20", "23"]} }, {firstName: 1, age: 1})
     *
     * а также можно применить:
     * ORDER BY:
     * find().sort( { name: 1 } )
     *
     * SKIP:
     * find().skip( 5 )
     *
     * LIMIT:
     * find().limit( 5 )
     *
     * SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 ORDER BY age ASC SKIP myRecords LIMIT 5
     * SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 AND (firstName='Alexandr' OR firstName='Inna') ORDER BY age ASC SKIP 3 LIMIT 5
     */
    public function prepareMongoQuery(array $parsedSql)
    {
        if (array_key_exists('SELECT', $parsedSql)) {
            $documentFields = array_column($parsedSql['SELECT'], 'base_expr');

            if (count($documentFields) !== 1 and $documentFields['0'] !== '*') {
                $preparedDocumentFields = array_flip($documentFields);

                array_walk($preparedDocumentFields,
                    function (&$value) {
                        $value = 1;
                    });

                $options['projections'] = $preparedDocumentFields;
            }
        }

        if (array_key_exists('FROM', $parsedSql)) {
            $collectionName = $parsedSql['FROM']['0']['table'];
        }

        if (array_key_exists('WHERE', $parsedSql)) { //todo {age: {$gt: ["20", "23"]} }
            $filter = [];
            $this->prepareWhere($parsedSql);
        }

        if (array_key_exists('ORDER BY', $parsedSql)) {
            $options1 = [
                'projection' => [     /*1 = show field, 0 = hide field*/
                    'firstName' => 1,
                    'lastName' => 1,
                    'age' => 1,
                    'hobby.' => 1,
                ],
                'sort' => ['firstName' => -1],   /*ASC = 1, DESC = -1*/
                'skip' => 10,
                'limit' => 50,
            ];
        }
        if (array_key_exists('SKIP', $parsedSql)) {

        }
        if (array_key_exists('LIMIT', $parsedSql)) {

        }

        $dbName = $this->connection->getConfig()->getDbName();
        $collectionName = 'User';

        /** @var Client $connection */
        $collection = $this->connection
            ->getConnection()
            ->$dbName
            ->$collectionName;
        $data = $collection->find($filter, $options)->toArray();

        return $data;
    }

    private function prepareWhere(array $parsedSql): array
    {

        $filter = [];
        $this->prepareConditions($parsedSql['WHERE'], $this->conditions, $filter);

        return $filter;
    }

    /**
     * Recursive handle conditions
     *
     * @param array $where
     * @param array $conditions
     * @param array $filter
     * @param null $logicalOperator AND or OR
     * @return array
     */
    private function prepareConditions(array $where, array $conditions, array &$filter, $logicalOperator = null): array
    {
        $filter1 = [
            'lastName' => "Gavuka",
            '$or' => [
                ['age' => ['$gt' => 5]],
                ['firstName' => ['$eq' => 'Alexandr']],
            ]
        ];

        /** field name */
        $fieldName = null;

        /** EQ, NE, GT, GTE, LT, LTE */
        $operator = null;

        /** field value */
        $fieldValue = null;

        foreach ($where as $key => $item) {
            /** Field */
            if ($item['expr_type'] === 'colref') {
                $fieldName = (string)$item['base_expr'];
            }

            /** Operator */
            if ($item['expr_type'] === 'operator') {
                if ($item['base_expr'] === 'AND' || $item['base_expr'] === 'OR') {
                    $logicalOperator = $item['base_expr'];
                }

                if (array_key_exists($item['base_expr'], $conditions)) {
                    $operator = (string)$conditions[$item['base_expr']];
                }
            }

            /** value */
            if ($item['expr_type'] === 'const') {
                $fieldValue = $item['base_expr'];
            }

            /** aggregation elements in filter[] */
            if (!empty($fieldName) &&
                !empty($operator) &&
                !empty($fieldValue)) {

                /** WHERE aggregation */
                if ($logicalOperator === null) {
                    $filter[$fieldName] = [$operator => $item['base_expr']];
                }

                /** OR aggregation */
                if ($logicalOperator === 'OR') {
                    $filter['$or'][] = [$fieldName => [$operator => $fieldValue]];
                }

                /** AND aggregation */
                if ($logicalOperator === 'AND' &&
                    $item['expr_type'] === 'const' &&
                    empty($item['sub_tree'])) {

                    $filter[$fieldName] = [$operator => $item['base_expr']];
                }

                list($fieldName, $operator, $fieldValue) = null;
            }

            /** AND + OR aggregation */
            if ($logicalOperator === 'AND' &&
                $item['expr_type'] === 'bracket_expression' &&
                !empty($item['sub_tree']) &&
                $item['sub_tree'][3]['base_expr'] === 'OR') {

                $this->prepareConditions($item['sub_tree'], $conditions, $filter, $logicalOperator = 'OR');
            }
        }

        return $filter;
    }

    public function view($data)
    {
        //todo метод отображения данных
        //todo вывести данные в виде таблицы
    }

    private
    function printError()
    {
        $this->cliMate->error('SQL is not correct, please enter the correct SQL');
        $this->run();
    }

    public
    function parseSql(string $sql): array
    {
        $this->parser->addCustomFunction('SKIP');

        $parsedSql = $this->parser->parse($sql);
        if ($parsedSql === false) {
            $this->printError();
        }
        return $parsedSql;
    }

    private
    function getSql(): string
    {
        for ($i = true; $i === true;) {
            $sql = readline('SQL to MongoDB > ');
            if (!empty($sql)) {
                break;
            }
        }

        return $sql;
    }
}
