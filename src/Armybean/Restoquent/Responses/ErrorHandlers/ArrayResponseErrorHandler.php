<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 14:02
 * Filename: ArrayResponseErrorHandler.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses\ErrorHandlers;

use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

class ArrayResponseErrorHandler implements ErrorHandlerInterface {

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

        return $result;
    }
}
