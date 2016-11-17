<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 15:16
 * Filename: auth.php
 *
 * $Rev$
 * $Date$
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Driver
    |--------------------------------------------------------------------------
    |
    | This parameter specifies the driver to use for authenticating requests with the remote API.
    |
    | Supported Options: basic
    |
    | basic - This driver will use HTTP Basic Authentication, and set the `auth.basic.username` and
    |         `auth.basic.password` config values on the request.
    |
    */

    'driver' => null,

    //credentials for HTTP Basic Authentication
    'basic'  => [
        'username' => '{basic_username}',
        'password' => '{basic_password}',
    ],

];
