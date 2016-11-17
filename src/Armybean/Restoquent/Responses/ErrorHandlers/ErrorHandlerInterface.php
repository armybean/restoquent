<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 14:00
 * Filename: ErrorHandlerInterface.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses\ErrorHandlers;

use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

interface ErrorHandlerInterface {

    /**
     * Constructor to setup the interpreter
     *
     * @param Container $app
     */
    public function __construct(Container $app);

    /**
     * Function to take the response object and return an array of errors
     *
     * @param Response $response - response object
     *
     * @return array - array of string error messages
     */
    public function parseErrors(Response $response);
}
