<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 09:11
 * Filename: RestRequest.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Requests;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Facades\ErrorHandlerFactory;
use Armybean\Restoquent\Facades\Response;
use Armybean\Restoquent\Facades\ResponseInterpreterFactory;
use Armybean\Restoquent\Facades\TransporterFactory;
use Armybean\Restoquent\Finders\Conditions\QueryConditionInterface;
use Armybean\Restoquent\Finders\Conditions\QueryResultOrderInterface;
use Armybean\Restoquent\Requests\Auth\AuthenticationInterface;
use Armybean\Restoquent\Resource\Model;
use Armybean\Restoquent\Responses\RawResponse;
use Armybean\Restoquent\Transporters\TransporterInterface;
use Httpful\Mime;
use Httpful\Request;
use Illuminate\Container\Container;

class RestRequest implements RequestableInterface {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Request client
     *
     * @var Client
     */
    protected $client;

    /**
     * Request object managed by this class
     *
     * @var \Httpful\Request
     */
    protected $request;

    /**
     * Build a new RestRequest
     *
     * @param Container $app
     * @param Client    $client
     */
    public function __construct(Container $app, $client = null)
    {
        $this->app = $app;
        //$this->client = $client == null ? new Client : $client;
    }

    /**
     * Getter function to access the HTTP Client
     *
     * @return Client
     */
    public function &getClient()
    {
        return $this->client;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Function to set the entities properties on the request object taking into account any properties that are read
     * only etc.
     *
     * @param Model $model
     */
    public function setModelProperties(Model $model)
    {
        $cantSet = $model->getReadOnlyFields();
        $fields = $model->attributes();

        $this->setPostParameters(array_diff_key($fields, $cantSet));
    }

    /**
     * Function to set POST parameters onto the request
     *
     * @param array $params Key value array of post params
     */
    public function setPostParameters($params = [])
    {
        $requestBody = $this->request->payload;
        if ( ! is_array($requestBody))
        {
            $requestBody = [$requestBody];
        }

        $this->setBody(array_merge($params, $requestBody));
    }

    /**
     * Function to add Query conditions to the request
     *
     * @param QueryConditionInterface $condition condition to add to the request
     *
     * @return void
     */
    public function addQueryCondition(QueryConditionInterface $condition)
    {
        $condition->addToRequest($this->request);
    }

    /**
     * Function to add Query result ordering conditions to the request
     *
     * @param QueryResultOrderInterface $resultOrder
     *
     * @return void
     */
    public function addQueryResultOrder(QueryResultOrderInterface $resultOrder)
    {
        $resultOrder->addToRequest($this->request);
    }

    /**
     * Function to add authentication to the request
     *
     * @param AuthenticationInterface $auth
     *
     * @return void
     */
    public function authenticate(AuthenticationInterface $auth)
    {
        $auth->authenticateRequest($this->request);
    }

    /**
     * Function to execute a raw GET request
     *
     * @param string $uri     uri to hit (i.e. /users)
     * @param array  $params  Querystring parameters to send
     * @param array  $headers Optional headers to use
     *
     * @return \Armybean\Restoquent\Responses\RawResponse
     */
    public function rawGet($uri, $params = [], $headers = [])
    {
        return $this->rawRequest($uri, 'GET', [], $params, [], $headers);
    }

    /**
     * Function to execute a raw request on the base URI with the given uri path
     * and params
     *
     * @param string $uri       uri to hit (i.e. /users)
     * @param string $method    Request method (GET, PUT, POST, PATCH, DELETE, etc.)
     * @param array  $params    PUT or POST parameters to send
     * @param array  $getParams Querystring parameters to send
     * @param array  $files     PUT or POST files to send (key = name, value = path)
     * @param array  $headers   Optional headers to use
     *
     * @return \Armybean\Restoquent\Responses\RawResponse
     */
    public function rawRequest($uri, $method, $params = [], $getParams = [], $files = [], $headers = [])
    {
        $this->request = self::createRequest(Config::get('request.base_uri'), $uri, $method);

        $this->setPostParameters($params);
        $this->setGetParameters($getParams);
        $this->setHeaders($headers);

        /** @var TransporterInterface $transporter */
        //encode the request body
        $transporter = TransporterFactory::build();
        $transporter->setRequestBody($this, $params);

        /** @var Response $response */
        $response = $this->sendRequest();

        //handle clean response with errors
        if (ResponseInterpreterFactory::build()->invalid($response))
        {
            //get the errors and set them to our local collection
            $errors = (array) ErrorHandlerFactory::build()->parseErrors($response);

            return new RawResponse(false, $response, $errors);
        }

        return new RawResponse(true, $response);
    }

    /**
     * Function to create a Httpful HTTP request
     *
     * @param string $uri              The protocol + host + the URI path after the host
     * @param string $httpMethod       The HTTP method to use for the request (GET, PUT, POST, DELTE etc.)
     * @param array  $requestHeaders   Any additional headers for the request
     * @param string $httpMethodParam  Post parameter to set with a string that contains the HTTP method type sent
     *                                 with a POST
     *
     * @return \Httpful\Request
     */
    public function createRequest($uri, $httpMethod = 'GET', $requestHeaders = [], $httpMethodParam = null)
    {
        if ( ! in_array(strtolower($httpMethod), ['get', 'put', 'post', 'patch', 'delete', 'head']))
        {
            throw new \InvalidArgumentException('Invalid HTTP method');
        }

        $method = strtolower($httpMethod);
        $method = $method == 'patch' ? 'put' : $method; //override patch calls with put

        $this->request = call_user_func(['\Httpful\Request', $method], $uri);

        if ($httpMethodParam != null && in_array($method, ['put', 'post']))
        {
            $this->setBody($httpMethodParam, Mime::FORM);
        }

        //set any additional headers on the request
        $this->setHeaders($requestHeaders);

        //setup how we get data back (xml, json etc)
        $this->setTransportLanguage();

        return $this->request;
    }

    /**
     * Function to set given file parameters on the request
     *
     * @param mixed $body
     * @param null  $contentType
     *
     * @return void
     */
    public function setBody($body, $contentType = null)
    {
        if (method_exists($this->request, 'body'))
        {
            $this->request->body($body, $contentType);
        }
    }

    /**
     * Function to set headers on the request
     *
     * @param array $requestHeaders Any additional headers for the request
     *
     * @return void
     */
    public function setHeaders(array $requestHeaders = [])
    {
        $this->request->addHeaders($requestHeaders);
    }

    /**
     * Function to set the language of data transport. Uses TransporterFactory to pull a Transportable object and set
     * up the request
     *
     * @return void
     */
    public function setTransportLanguage()
    {
        /** @var TransporterInterface $transporter */
        $transporter = TransporterFactory::build();
        $transporter->setHeaderOnRequest($this->request);
    }

    /**
     * Function to set GET parameters onto the request
     *
     * @param array $params Key value array of get params
     */
    public function setGetParameters(array $params = [])
    {
        $this->request->uri($this->request->uri . '?' . http_build_query($params));
    }

    /**
     * Function to send the request to the remote API
     *
     * @return \Armybean\Restoquent\Responses\Response
     */
    public function sendRequest()
    {
        try
        {
            $response = $this->request->send();
        }
        catch (\Exception $e)
        {
            $response = null;
        }

        return $this->app->make('restoquent.response')->newInstance($this->app, $response);
    }

    /**
     * Function to execute a raw POST request
     *
     * @param string $uri       uri to hit (i.e. /users)
     * @param array  $params    POST parameters to send
     * @param array  $getParams Querystring parameters to send
     * @param array  $files     files to send (key = name, value = path)
     * @param array  $headers   Optional headers to use
     *
     * @return RawResponse
     */
    public function rawPost($uri, $params = [], $getParams = [], $files = [], $headers = [])
    {
        return $this->rawRequest($uri, 'POST', $params, $getParams, $files, $headers);
    }

    /**
     * Function to execute a raw PUT request
     *
     * @param  string $uri       uri to hit (i.e. /users)
     * @param  array  $params    PUT parameters to send
     * @param  array  $getParams Querystring parameters to send
     * @param  array  $files     files to send (key = name, value = path)
     * @param  array  $headers   Optional headers to use
     *
     * @return RawResponse
     */
    public function rawPut($uri, $params = [], $getParams = [], $files = [], $headers = [])
    {
        return $this->rawRequest($uri, 'PUT', $params, $getParams, $files, $headers);
    }

    /**
     * Function to execute a raw PATCH request
     *
     * @param  string $uri       uri to hit (i.e. /users)
     * @param  array  $params    PATCH parameters to send
     * @param  array  $getParams Querystring parameters to send
     * @param  array  $files     files to send (key = name, value = path)
     * @param  array  $headers   Optional headers to use
     *
     * @return RawResponse
     */
    public function rawPatch($uri, $params = [], $getParams = [], $files = [], $headers = [])
    {
        return $this->rawRequest($uri, 'PATCH', $params, $getParams, $files, $headers);
    }

    /**
     * Function to execute a raw DELETE request
     *
     * @param  string $uri     uri to hit (i.e. /users)
     * @param  array  $params  Querystring parameters to send
     * @param  array  $headers Optional headers to use
     *
     * @return RawResponse
     */
    public function rawDelete($uri, $params = [], $headers = [])
    {
        return $this->rawRequest($uri, 'DELETE', [], $params, [], $headers);
    }
}
