<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 15:29
 * Filename: BasicAuthenticator.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Requests\Auth;

use Armybean\Restoquent\Facades\Config;
use Illuminate\Container\Container;

class BasicAuthenticator implements AuthenticationInterface {

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
        $username = Config::get('auth.basic.username');
        $password = Config::get('auth.basic.password');
        $request->authenticateWithBasic($username, $password);
    }
}
