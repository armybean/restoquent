<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 12:45
 * Filename: HttpStatusCodeInterpreter.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses\Interpreters;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Responses\BaseResponse;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class HttpStatusCodeInterpreter implements ResponseInterpreterInterface {

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
     * Function to return a boolean value indicating whether the request was successful or not
     *
     * @param $response - Httpful response to interpret
     *
     * @return boolean
     */
    public function success(BaseResponse $response)
    {
        return $this->matchesStatus('response.http_status.success', $response->getStatusCode());
    }

    /**
     * Function to return a boolean value indicating whether the provided status is matched by the configured setting.
     *
     * Currently supports:
     *
     * @param $option
     * @param $status
     *
     * @return bool
     */
    protected function matchesStatus($option, $status)
    {
        $configValue = Config::get($option);
        if (is_array($configValue))
        {
            return Config::contains($option, $status);
        }

        return Str::is($configValue, $status);
    }

    /**
     * Function to return a boolean value indicating whether the request indicated something was not found
     *
     * @param $response - Httpful response to interpret
     *
     * @return boolean
     */
    public function notFound(BaseResponse $response)
    {
        return $this->matchesStatus('response.http_status.not_found', $response->getStatusCode());
    }

    /**
     * Function to return a boolean value indicating whether the request was considered invalid
     *
     * @param $response - Httpful response to interpret
     *
     * @return boolean
     */
    public function invalid(BaseResponse $response)
    {
        return $this->matchesStatus('response.http_status.invalid', $response->getStatusCode());
    }

    /**
     * Function to return a boolean value indicating whether the request was ended in an error state
     *
     * @param $response - Httpful response to interpret
     *
     * @return boolean
     */
    public function error(BaseResponse $response)
    {
        return $this->matchesStatus('response.http_status.error', $response->getStatusCode());
    }
}
