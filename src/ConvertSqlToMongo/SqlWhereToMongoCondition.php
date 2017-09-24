<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 24.09.17
 * Time: 20:11
 */

namespace ConvertSqlToMongo;


use Views\CliView;

class SqlWhereToMongoCondition
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

    /** @var CliView $cliView */
    private $cliView;

    public function __construct(CliView $cliView)
    {
        $this->cliView = $cliView;
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
    public function prepareConditions(
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
            array_splice($where, 0, 1);

            /** recursive call for sub_tree bracket_expression */
            $this->prepareConditions($where['0']['sub_tree'], $filter, true);

            /** for recursive processing of operations in brackets */
            if ($bracketExpression === true) {
                unset($bracketExpression);
            }

            /** delete sub tree of element from array */
            array_splice($where, 0, 1);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        /** prepare OR */
        if ($where['0']['base_expr'] === 'OR') {
            array_splice($where, 0, 1);
            $this->prepareOr($filter, $where);
        }

        if (count($where) <= 0) {
            return $filter;
        }

        /** prepare AND */
        if ($where['0']['base_expr'] === 'AND') {
            array_splice($where, 0, 1);
            $this->prepareWhereAnd($filter, $where);
        }

        /** if array where not empty, prepareConditions() will be called again */
        if (count($where) > 0) {
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
        $elements = [];

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
        $result = null;
        if (array_key_exists($operator, $this->conditions)) {
            $result = $this->conditions[$operator];
        } else {
            $this->cliView->printErrorInCli('Conditions operator' . "$operator" . 'not valid!');
        }

        /** @var string $result */
        return $result;
    }
}
