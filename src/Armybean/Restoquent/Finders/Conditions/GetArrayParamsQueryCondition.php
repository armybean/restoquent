<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 14:52
 * Filename: GetArrayParamsQueryCondition.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Finders\Conditions;

use Armybean\Restoquent\Facades\Config;
use Illuminate\Container\Container;

/**
 * Class to manage query conditions for a request, where the query conditions are passed as HTTP GET array parameters
 * which are nested within a particular parameter name (defined in the config). The resulting GET params might be
 * something like:
 *
 * <code>
 * search[0][property]=someProperty
 * search[0][operator]=<
 * search[0][value]=1234
 * search[1][property]=anotherProperty
 * search[1][operator]=LIKE
 * search[1][value]=someString
 * logical_operator=AND
 * </code>
 */
class GetArrayParamsQueryCondition implements QueryConditionInterface {

    /**
     * Constant to refer to array entries that contain a property or attribute to which a condition should be applied
     */
    const PROPERTY = 'property';

    /**
     * Constant to refere to array entries that contain the operator that a value should be matched against on a
     * property
     */
    const OPERATOR = 'operator';

    /**
     * Constant to refer to array entries that contain a value that should be used in a conditional match against a
     * property
     */
    const VALUE = 'value';

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Collection of conditions, each condition will be a 3 key array having an entry for property, operator and value
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * The logical operator that should be used to group the conditions herein together
     *
     * @var string
     */
    protected $logicalOperator;

    /**
     * Build a new GetArrayParamsQueryCondition
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Function to return a new popuplated instance, typically this would be called from the Facade.
     *
     * @return GetArrayParamsQueryCondition
     */
    public function newInstance()
    {
        $instance = new static($this->app);

        return $instance;
    }

    /**
     * Function to add a query condition
     *
     * @param string $property The field the condition operates on
     * @param string $operator The operator (=, <, >, <= and so on)
     * @param string $value    The value the condition should match
     *
     * @return void
     */
    public function addCondition($property, $operator, $value)
    {
        $this->conditions[] = [
            self::PROPERTY => $property,
            self::OPERATOR => $operator,
            self::VALUE    => $value
        ];
    }

    /**
     * Function to set the logical operator for the combination of any conditions that have been passed to the
     * addCondition() function
     *
     * @param string $operator
     *
     * @return void
     */
    public function setLogicalOperator($operator)
    {
        if ($operator != $this->getLogicalOperatorAnd() && $operator != $this->getLogicalOperatorOr())
        {
            throw new \InvalidArgumentException("Invalid logical operator: {$operator}");
        }

        $this->logicalOperator = $operator;
    }

    /**
     * Function to get the string representing the AND logical operator
     *
     * @return string
     */
    public function getLogicalOperatorAnd()
    {
        return Config::get('query_condition.get_array_params.and_operator');
    }

    /**
     * Function to get the string representing the OR logical operator
     *
     * @return string
     */
    public function getLogicalOperatorOr()
    {
        return Config::get('query_condition.get_array_params.or_operator');
    }

    /**
     * Function to add all the conditions that have been given to the class to a given request object
     *
     * @param \Httpful\Request $request Request passed by reference
     *
     * @return void
     */
    public function addToRequest(&$request)
    {
        $request->body($this->toArray());
    }

    /**
     * Function to convert the conditions and logical operator represented in this class to an array, this is useful
     * for testing
     *
     * @return array
     */
    public function toArray()
    {
        $container = Config::get('query_condition.get_array_params.container_parameter');
        $property = Config::get('query_condition.get_array_params.property');
        $operator = Config::get('query_condition.get_array_params.operator');
        $value = Config::get('query_condition.get_array_params.value');

        $params = [];

        $x = 0;
        foreach ($this->conditions as $condition)
        {
            $params["{$container}[$x][{$property}]"] = $condition[self::PROPERTY];
            $params["{$container}[$x][{$operator}]"] = $condition[self::OPERATOR];
            $params["{$container}[$x][{$value}]"] = $condition[self::VALUE];

            $x++;
        }

        if (isset($this->logicalOperator))
        {
            $params[Config::get('query_condition.get_array_params.logical_operator')] = $this->logicalOperator;
        }

        return $params;
    }

    /**
     * Function to convert the conditions and logical operator represented in this class to a querystring, this is
     * useful for testing
     *
     * @return string
     */
    public function toQueryString()
    {
        return http_build_query($this->toArray());
    }
}

