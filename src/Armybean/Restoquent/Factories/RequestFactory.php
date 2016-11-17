<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 08:41
 * Filename: RequestFactory.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Factories;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Framework\FactoryDriver;
use Guzzle\Http\Client;
use Httpful\Httpful;
use Illuminate\Container\Container;

class RequestFactory extends FactoryDriver {

    /**
     * Guzzle Client attached to request returned by build()
     *
     * @var Client
     */
    protected $client;

    /**
     * Build a new FactoryDriver
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->client = new Httpful();
    }

    /**
     * Getter function to access the HTTP Client
     *
     * @return Client
     */
    public function &getClient()
    {
        return $this->client;
    }

    /**
     * Function to return a string representaion of the namespace that all classes built by the factory should be
     * contained within.
     *
     * @return string - namespace string
     */
    public function getDriverNamespace()
    {
        return "\Restoquent\Requests";
    }

    /**
     * Function to return the interface that the drivers produced by the factory must implement.
     *
     * @return string
     */
    public function getDriverInterface()
    {
        return "\Restoquent\Requests\RequestableInterface";
    }

    /**
     * Function to return a string that should be suffixed to the studly-cased driver name of all the drivers that the
     * factory can return.
     *
     * @return string
     */
    public function getDriverNameSuffix()
    {
        return 'Request';
    }

    /**
     * Function to return a string that should be prefixed to the studly-cased driver name of all the drivers that the
     * factory can return.
     *
     * @return string
     */
    public function getDriverNamePrefix()
    {
        return '';
    }

    /**
     * Function to return an array of arguments that should be passed to the constructor of a new driver instance.
     *
     * @return array
     */
    public function getDriverArgumentsArray()
    {
        return [$this->app];
    }

    /**
     * Function to return the string representation of the driver itself based on a value fetched from the config file.
     * This function will itself access the config, and return the driver setting.
     *
     * @return string
     */
    public function getDriverConfigValue()
    {
        return Config::get('request.driver');
    }
}
