<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 15:31
 * Filename: RawResponse.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses;

class RawResponse extends BaseResponse {

    /**
     * Var to tell if the request was successful
     *
     * @var boolean
     */
    public $success = false;
    /**
     * Response object
     *
     * @var Response
     */
    protected $response = null;
    /**
     * Var to hold any errors returned
     *
     * @var array
     */
    private $errors = [];

    /**
     * Constructor
     *
     * @param boolean  $successful
     * @param Response $response
     * @param array    $errors
     */
    public function __construct($successful = false, Response $response = null, array $errors = [])
    {
        $this->success = $successful;
        $this->errors = $errors;

        parent::__construct($response);
    }

    /**
     * Magic function to pass methods not found on this class down to the Armybean\Restoquent\Responses\Response object
     * that is being wrapped
     *
     * @param string $method name of called method
     * @param array  $args   arguments to the method
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ( ! method_exists($this, $method))
        {
            return call_user_func_array([$this->response, $method], $args);
        }
    }

    /**
     * Getter for errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Getter for response
     *
     * @return object
     */
    public function response()
    {
        return $this->response->parseResponseStringToObject();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
