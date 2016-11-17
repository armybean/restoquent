<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 07:57
 * Filename: Config.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Facades;

use Armybean\Restoquent\RestoquentServiceProvider;
use Illuminate\Support\Facades\Facade;

class Config extends Facade {

    protected static function getFacadeAccessor()
    {
        if ( ! static::$app)
        {
            static::$app = RestoquentServiceProvider::make();
        }

        return 'restoquent.config-manager';
    }
}
