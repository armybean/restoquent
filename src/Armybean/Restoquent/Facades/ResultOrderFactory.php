<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 15:18
 * Filename: ResultOrderFactory.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Facades;

use Armybean\Restoquent\RestoquentServiceProvider;
use Illuminate\Support\Facades\Facade;

class ResultOrderFactory extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        if ( ! static::$app)
        {
            static::$app = RestoquentServiceProvider::make();
        }

        return 'restoquent.order';
    }
}
