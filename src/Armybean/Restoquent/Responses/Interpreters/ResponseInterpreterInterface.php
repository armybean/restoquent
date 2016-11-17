<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 12:43
 * Filename: ResponseInterpreterInterface.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses\Interpreters;

use Armybean\Restoquent\Responses\BaseResponse;
use Illuminate\Container\Container;

/**
 * Interface to be implemented by classes that are deciphering API responses to determine the success or failure of
 * various requests
 */
interface ResponseInterpreterInterface {

    /**
     * Constructor to setup the interpreter
     *
     * @param Container $app
     */
    public function __construct(Container $app);

    /**
     * Function to return a boolean value indicating wether the request was successful or not
     *
     * @param $response - Restoquent response to interpret
     *
     * @return boolean
     */
    public function success(BaseResponse $response);

    /**
     * Function to return a boolean value indicating wether the request indicated something was not found
     *
     * @param $response - Restoquent response to interpret
     *
     * @return boolean
     */
    public function notFound(BaseResponse $response);

    /**
     * Function to return a boolean value indicating wether the request was considered invalid
     *
     * @param $response - Restoquent response to interpret
     *
     * @return boolean
     */
    public function invalid(BaseResponse $response);

    /**
     * Function to return a boolean value indicating wether the request was ended in an error state
     *
     * @param $response - Restoquent response to interpret
     *
     * @return boolean
     */
    public function error(BaseResponse $response);
}

