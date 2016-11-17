<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 08:59
 * Filename: UrlGenerator.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Url;

use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Resource\Model;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Container\Container;

class UrlGenerator {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new UrlGenerator
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Getter to access the IoC Container
     *
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Function to get the URI with placeholders for data
     * that a POST request should be made to in order to create
     * a new entity.
     *
     * @param Model $model
     * @param array $options Array of options to replace placeholders with
     *
     * @return string
     */
    public function getCreateUri($model, $options = [])
    {
        return $this->getCollectionUri($model, $options);
    }

    /**
     * Function to get the URI with placeholders for data that a GET request should be made to in order to retreive a
     * collection of Entities
     *
     * @param Model $model
     * @param array $options Array of options to replace placeholders with
     *
     * @return string
     */
    public function getCollectionUri(Model $model, array $options = [])
    {
        $uri = $this->getURI($model);
        foreach ($options as $key => $value)
        {
            $uri = str_replace($key, $value, $uri);
        }

        return $uri;
    }

    /**
     * Function to return the name of the URI to hit based on the interpreted name of the class in question. For
     * example a User class would resolve to /users
     *
     * @param Model $model
     *
     * @return string The URI to hit
     */
    public function getURI(Model $model)
    {
        if ($uri = $model->getURI())
        {
            return $uri;
        }

        return '/' . Inflector::pluralize(Inflector::tableize($model->getResourceName()));
    }

    /**
     * Function to get the URI with placeholders for data that a PUT/PATCH request should be made to in order to update
     * an existing entity.
     *
     * @param Model $model
     * @param array $options Array of options to replace placeholders with
     *
     * @return string
     */
    public function getUpdateUri(Model $model, array $options = [])
    {
        return $this->getInstanceUri($model, $options);
    }

    /**
     * Function to get the URI with placeholders for data that a GET request should be made to in order to retreive an
     * instance of an Entity
     *
     * @param Model $model
     * @param array $options Array of options to replace placeholders with
     *
     * @return string
     */
    public function getInstanceUri(Model $model, array $options = [])
    {
        $uri = implode('/', [$this->getURI($model), ':id']);
        foreach ($options as $key => $value)
        {
            $uri = str_replace($key, $value, $uri);
        }

        if ( ! starts_with($uri, '/'))
        {
            $uri = '/' . $uri;
        }

        return Config::get('request.base_uri') . $uri;
    }

    /**
     * Function to get the URI with placeholders for data that a DELETE request should be made to in order to delete an
     * existing entity.
     *
     * @param Model $model
     * @param array $options Array of options to replace placeholders with
     *
     * @return string
     */
    public function getDeleteUri(Model $model, array $options = [])
    {
        return $this->getInstanceUri($model, $options);
    }
}
