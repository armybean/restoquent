<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 11:04
 * Filename: JsonTransporter.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Transporters;

use Armybean\Restoquent\Requests\RequestableInterface;
use Httpful\Mime;
use Httpful\Request;
use Httpful\Response;
use stdClass;

class JsonTransporter implements TransporterInterface {

    /**
     * Function to set the appropriate headers on a request object to facilitate a JSON transport
     *
     * @param Request $request
     */
    public function setHeaderOnRequest(Request &$request)
    {
        $request->expects(Mime::JSON);
    }

    /**
     * Function to convert a response object into an associative array of data
     *
     * @param Response $response
     *
     * @return array
     */
    public function parseResponseToData(Response $response)
    {
        return json_decode(json_encode($response->body), true);
    }

    /**
     * Function to parse the response string into an object specific to JSON
     *
     * @param Response $response
     *
     * @return stdClass
     */
    public function parseResponseStringToObject(Response $response)
    {
        return $response->body;
    }

    /**
     * Set the request body for the given request.
     *
     * @param RequestableInterface $request
     * @param mixed                $body
     */
    public function setRequestBody(RequestableInterface &$request, $body)
    {
        $request->setBody($body, Mime::JSON);
    }
}
