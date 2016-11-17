<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 17.11.2016
 * Time: 07:43
 * Filename: ApiKeyAuthenticator.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Requests\Auth;

use Armybean\Restoquent\Facades\Config;
use Illuminate\Container\Container;

class ApiKeyAuthenticator implements AuthenticationInterface {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Constructor, likely never called in implementation but rather through the Factory
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Function to add the necessary authentication to the request
     *
     * @param \Httpful\Request $request Request passed by reference
     *
     * @return void
     */
    public function authenticateRequest(&$request)
    {
        if (Config::get('auth.api_key.location') == 'header')
        {
            $request->addHeader(Config::get('auth.api_key.field'), \Session::get(Config::get('auth.api_key.session'),
                null));
        }
        else
        {
            $payload = $request->payload;
            if ( ! is_array($payload))
            {
                $payload = [$payload];
            }

            $request->body(array_merge($payload,
                [Config::get('auth.api_key.field') => \Session::get(Config::get('auth.api_key.session'), null)]));
        }
    }
}
