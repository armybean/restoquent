<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 08:38
 * Filename: InstanceFinder.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Finders;

use Armybean\Restoquent\Facades\AuthFactory;
use Armybean\Restoquent\Facades\RequestFactory;
use Armybean\Restoquent\Facades\ResponseInterpreterFactory;
use Armybean\Restoquent\Facades\UrlGenerator;
use Armybean\Restoquent\Requests\RequestableInterface;
use Armybean\Restoquent\Resource\Model;
use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

class InstanceFinder {

    /**
     * The IoC Container
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Build a new InstanceFinder
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Function to find an instance of an Entity record
     *
     * @param Model $model     Model to use for URL generation etc.
     * @param int   $id        The primary identifier value for the record
     * @param array $getParams Array of GET parameters to pass
     *
     * @return Model           An instance of the entity requested
     */
    public function fetch(Model $model, $id, array $getParams = [])
    {
        $instance = null;

        /** @var RequestableInterface $request */
        // get a request object
        $request = RequestFactory::build();

        // init the request
        $request->createRequest(UrlGenerator::getInstanceUri($model, [':id' => $id]), 'GET');

        // set any get parameters on the request
        $request->setGetParameters($getParams);

        // add auth if necessary
        if ($auth = AuthFactory::build())
        {
            $request->authenticate($auth);
        }

        /** @var Response $response */
        // actually send the request
        $response = $request->sendRequest();

        if ( ! ResponseInterpreterFactory::build()->success($response))
        {
            return null;
        }

        // craft the response into an object to return
        $data = $response->parseResponseToData();
        /** @var Model $instance */
        $instance = new $model($data);

        // inflate the ID property that should be guarded
        $id = $instance->getIdentityProperty();
        if (array_key_exists($id, $data))
        {
            $instance->{$id} = $data[$id];
        }

        return $instance;
    }
}
