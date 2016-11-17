<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 09:09
 * Filename: RequestableInterface.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Requests;

use Armybean\Restoquent\Finders\Conditions\QueryConditionInterface;
use Armybean\Restoquent\Finders\Conditions\QueryResultOrderInterface;
use Armybean\Restoquent\Requests\Auth\AuthenticationInterface;
use Armybean\Restoquent\Resource\Model;
use Illuminate\Container\Container;

interface RequestableInterface {

    public function __construct(Container $app, $client = null);

    public function &getClient();

    public function createRequest($uri, $httpMethod = 'GET', $requestHeaders = [], $httpMethodParam = null);

    public function setHeaders(array $requestHeaders = []);

    public function setBody($body, $contentType = null);

    public function setPostParameters($params = []);

    public function setGetParameters(array $params = []);

    public function setModelProperties(Model $model);

    public function setTransportLanguage();

    public function addQueryCondition(QueryConditionInterface $condition);

    public function addQueryResultOrder(QueryResultOrderInterface $resultOrder);

    public function authenticate(AuthenticationInterface $auth);

    public function sendRequest();
}
