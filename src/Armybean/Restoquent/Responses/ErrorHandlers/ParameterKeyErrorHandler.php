<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 14:04
 * Filename: ParameterKeyErrorHandler.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses\ErrorHandlers;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

class ParameterKeyErrorHandler implements ErrorHandlerInterface {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Constructor to setup the interpreter
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Function to take the response object and return
     * an array of errors
     *
     * @param Response $response - response object
     *
     * @return array - array of string error messages
     */
    public function parseErrors(Response $response)
    {
        $result = $response->parseResponseStringToObject();
        $error_key = Config::get('error_handler.errors_key');

        if (property_exists($result, $error_key))
        {
            return $result->$error_key;
        }

        throw new \InvalidArgumentException("Error key [{$error_key}] does not exist in response");
    }
}
