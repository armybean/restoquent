<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 11:04
 * Filename: TransporterInterface.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Transporters;

use Armybean\Restoquent\Requests\RequestableInterface;

interface TransporterInterface {

    /**
     * Function to set the appropriate headers on a request object
     * to facilitate a particular transport language
     *
     * @param \Httpful\Request $request
     */
    public function setHeaderOnRequest(\Httpful\Request &$request);

    /**
     * Function to convert a response object into an associative
     * array of data
     *
     * @param \Httpful\Response $response
     *
     * @return array
     */
    public function parseResponseToData(\Httpful\Response $response);

    /**
     * Function to parse the response string into an object
     * specific to the type of transport mechanism used i.e. json, xml etc
     *
     * @param \Httpful\Response $response
     *
     * @return \stdClass
     */
    public function parseResponseStringToObject(\Httpful\Response $response);

    /**
     * Set the request body for the given request.
     *
     * @param RequestableInterface $request
     * @param                      $body
     */
    public function setRequestBody(RequestableInterface &$request, $body);
}
