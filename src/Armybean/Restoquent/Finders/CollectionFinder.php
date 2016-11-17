<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 13:23
 * Filename: CollectionFinder.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Finders;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Facades\RequestFactory;
use Armybean\Restoquent\Facades\UrlGenerator;
use Armybean\Restoquent\Finders\Conditions\QueryConditionInterface;
use Armybean\Restoquent\Finders\Conditions\QueryResultOrderInterface;
use Armybean\Restoquent\Requests\RequestableInterface;
use Armybean\Restoquent\Resource\Model;
use Armybean\Restoquent\Responses\Collection;
use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

class CollectionFinder {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new CollectionFinder
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Function to fetch a collection of Armybean\Restoquent\Resource\Model object from the remote API.
     *
     * @param Model                     $model       Instance of entity type being fetched
     * @param QueryConditionInterface   $condition   Query conditions for the request
     * @param QueryResultOrderInterface $resultOrder Result ordering requirements for the request
     * @param array                     $getParams   Additional GET parameters to send w/ request
     *
     * @return Collection
     */
    public function fetch(
        Model $model,
        QueryConditionInterface $condition = null,
        QueryResultOrderInterface $resultOrder = null,
        array $getParams = []
    )
    {
        /** @var RequestableInterface $request */
        // get a request object
        $request = RequestFactory::build();

        // init the request
        $request->createRequest(UrlGenerator::getCollectionUri($model), 'GET');

        // add query conditions if needed
        if ($condition)
        {
            $request->addQueryCondition($condition);
        }

        // add result ordering if needed
        if ($resultOrder)
        {
            $request->addQueryResultOrder($resultOrder);
        }

        // set any get parameters on the request
        $request->setGetParameters($getParams);

        /** @var Response $response */
        // actually send the request
        $response = $request->sendRequest();

        // get api response
        $data = $response->parseResponseToData();

        // make an array to hold results
        $records = [];

        // figure out wether a collection key is used
        $collection_key = Config::get('resource.collection_key');

        // set records array appropriatley
        $recordCollection = (isset($collection_key)) ? $data[$collection_key] : $data;

        // create an array of popuplated results
        foreach ($recordCollection as $values)
        {
            /** @var Model $instance */
            $instance = new $model($values);

            // inflate the ID property that should be guarded
            $id = $instance->getIdentityProperty();
            if (array_key_exists($id, $values))
            {
                $instance->{$id} = $values[$id];
            }

            // add the instance to the records array
            $records[] = $instance;
        }

        // create a collection object to return
        $collection = new Collection($records);

        // if there was a collection_key, put any extra data that was returned outside the collection key in the
        // metaData attribute
        if (isset($collection_key))
        {
            $collection->metaData = array_diff_key($data, array_flip((array) [$collection_key]));
        }

        return $collection;
    }
}
