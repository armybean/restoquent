<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 15:28
 * Filename: AuthenticationInterface.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Requests\Auth;

use Illuminate\Container\Container;

interface AuthenticationInterface {

    /**
     * Constructor, likely never called in implementation but rather through the Factory
     *
     * @param Container $app
     */
    public function __construct(Container $app);

    /**
     * Function to add the necessary authentication to the request
     *
     * @param \Httpful\Request $request Request passed by reference
     *
     * @return void
     */
    public function authenticateRequest(&$request);
}
