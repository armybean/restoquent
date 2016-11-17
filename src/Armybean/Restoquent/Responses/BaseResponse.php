<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 12:00
 * Filename: BaseResponse.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses;

class BaseResponse {

    /**
     * Response object managed by this
     * class
     *
     * @var \Httpful\Response
     */
    protected $response;

    /**
     * Build a new ResponseManager
     *
     * @param \Httpful\Response $response
     */
    public function __construct(\Httpful\Response $response = null)
    {
        $this->response = $response;
    }

    public function getStatusCode()
    {
        return $this->response->code;
    }
}
