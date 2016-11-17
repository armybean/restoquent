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
    | Supported Options: api_key, basic, (static_api_key)
    |
    | api_key - Add an additional key-value-pair `auth.api_key.field` to the `auth.api_key.location` to authenticate
    |           with an API key stored in the users session
    | basic   - This driver will use HTTP Basic Authentication, and set the `auth.basic.username` and
    |           `auth.basic.password` config values on the request.
    |
    | static_api_key - Add Add an additional key-value-pair `auth.static_api_key.field` to the
    |                  `auth.static_api_key.location` to authenticate with an static API key (this should only be used
    |                  for test purposes)
    |
    */

    'driver'         => null,

    // field definition where API key is stored
    'api_key'        => [
        'location' => 'header', // header || payload
        'field'    => 'api-key',
        'session'  => 'api_key'
    ],

    // credentials for HTTP Basic Authentication
    'basic'          => [
        'username' => '{basic_username}',
        'password' => '{basic_password}',
    ],

    // field definition where API key is stored (only for testing)
    'static_api_key' => [
        'location' => 'header', // header || payload
        'field'    => 'api-key',
        'value'    => '{api_key}'
    ],

];
