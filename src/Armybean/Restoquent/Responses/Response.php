<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 11:59
 * Filename: Response.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses;

use Armybean\Restoquent\Facades\TransporterFactory;
use Armybean\Restoquent\Transporters\TransporterInterface;
use Illuminate\Container\Container;

class Response extends BaseResponse {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Response object managed by this class
     *
     * @var \Httpful\Response
     */
    protected $response;

    /**
     * Build a new RequestManager
     *
     * @param Container         $app
     * @param \Httpful\Response $response
     */
    public function __construct(Container $app, \Httpful\Response $response = null)
    {
        $this->app = $app;

        parent::__construct($response);
    }

    /**
     * Getter to access the IoC Container
     *
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Magic function to pass methods not found on this class down to the Httpful response object that is being wrapped
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
     * Create a new instance of the given model.
     *
     * @param Container         $app
     * @param \Httpful\Response $response
     *
     * @return Response
     */
    public function newInstance(Container $app, \Httpful\Response $response)
    {

        // This method just provides a convenient way for us to generate fresh model instances of this current model. It
        // is particularly useful during the hydration of new objects via the Eloquent query builder instances.
        $r = new static($app, $response);

        return $r;
    }

    /**
     * Function to take a response object and convert it into an array of data that is ready for use
     *
     * @return array Parsed array of data
     */
    public function parseResponseToData()
    {
        /** @var TransporterInterface $transporter */
        $transporter = TransporterFactory::build();

        return $transporter->parseResponseToData($this->response);
    }

    /**
     * Function to take a response string (as a string) and depending on the type of string it is, parse it into an
     * object.
     *
     * @return object
     */
    public function parseResponseStringToObject()
    {
        /** @var TransporterInterface $transporter */
        $transporter = TransporterFactory::build();

        return $transporter->parseResponseStringToObject($this->response);
    }
}
