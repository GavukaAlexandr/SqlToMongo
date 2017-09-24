<?php

use ConvertSqlToMongo\SqlWhereToMongoCondition;
use DataBase\ConnectionInterface;
use DataBase\MongoDbConnection;
use MongoDB\Client;
use PHPSQLParser\PHPSQLParser;
use Views\CliView;

class SqlToMongoDb
{
    /**
     * @var  ConnectionInterface|Client|MongoDbConnection
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

    /** @var string $dbName */
    private $dbName = null;

    /** @var SqlWhereToMongoCondition  */
    private $mongoCondition;

    /**
     * @param ConnectionInterface|MongoDbConnection $connection
     * @param PHPSQLParser $parser
     * @param CliView $cliView
     * @param SqlWhereToMongoCondition $mongoCondition
     */
    public function __construct(
        ConnectionInterface $connection,
        PHPSQLParser $parser,
        CliView $cliView,
        SqlWhereToMongoCondition $mongoCondition)
    {
        $this->connection = $connection;
        $this->parser = $parser;
        $this->cliView = $cliView;
        $this->mongoCondition = $mongoCondition;
    }

    public function run()
    {
        $dbName = $this->getDbName();

        $sql = $this->getDataFromCli('SQL to MongoDB >>> ');
        $parsedSql = $this->parseSql((string) $sql);
        $this->uppercaseOperators($parsedSql);

        /** get prepared query for MongoDB */
        $settings = $this->prepareMongoQuery($parsedSql);

        /** execute query */
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
     * @return string
     */
    private function getDbName(): string
    {
        if (empty($this->dbName)) {
            $this->dbName = $this->getDataFromCli('enter the name of the database >>> ');
        }

        return $this->dbName;
    }

    /**
     * prepare SQL to array MongoDB query
     *
     * Example SQL statement
     * SELECT firstName, lastName, age FROM SqlToMongo WHERE age > 18 AND (firstName='name1' OR firstName='name2') ORDER BY age ASC, firstName DESC, lastName ASC SKIP 3 LIMIT 5
     * SELECT * FROM User WHERE age>20 AND( gender=female or lastName=lastName58 )
     *
     * @param array $parsedSql
     * @return array
     */
    public function prepareMongoQuery(array $parsedSql)
    {
        $data = [];
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
            $filter = $this->mongoCondition->prepareConditions($parsedSql['WHERE']);
        }

        if (array_key_exists('ORDER', $parsedSql)) {
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
    private function uppercaseOperators(array &$parsedSql)
    {
        foreach ($parsedSql as &$element) {
            if (is_array($element)) {
                if (array_key_exists('expr_type', $element)) {
                    if ($element['expr_type'] === 'operator') {
                        $element['base_expr'] = strtoupper($element['base_expr']);
                    }

                    if ($element['sub_tree'] !== false) {
                        $this->uppercaseOperators($element['sub_tree']);
                    }
                } else {
                    if (is_array($element)) {
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
            function(&$value) {
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
                function(&$value) {
                    $value = 1;
                });
            return $preparedDocumentFields;
        }
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
     * repeat greeting for input in CLI while the data hasn't input
     *
     * @return string
     */
    public function getDataFromCli($message): string
    {
        $data = null;
        while (empty($data)) {
            $data = readline($message);
        }

        /** @var string $data */
        return $data;
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
