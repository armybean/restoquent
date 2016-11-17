<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 13:32
 * Filename: Collection.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Responses;

class Collection implements \Iterator {

    /**
     * Associative array of metadata related to the collection
     *
     * @var array
     */
    public $metaData = [];
    /**
     * Var to hold the actual source array collection
     *
     * @var array
     */
    private $collection;

    /**
     * Constructor for the collection
     *
     * @param array $givenArray array of objects
     */
    public function __construct(array $givenArray)
    {
        $this->collection = $givenArray;
    }

    /**
     * Function to conform with Iterator interface.
     *
     * @see Iterator
     *
     * @return \Restoquent\Resource\Model
     */
    public function rewind()
    {
        return reset($this->collection);
    }

    /**
     * Function to conform with Iterator interface.
     *
     * @see Iterator
     *
     * @return \Restoquent\Resource\Model
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Function to conform with Iterator interface.
     *
     * @see Iterator
     *
     * @return \Restoquent\Resource\Model
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * Function to conform with Iterator interface.
     *
     * @see Iterator
     *
     * @return \Restoquent\Resource\Model
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * Function to conform with Iterator interface.
     *
     * @see Iterator
     *
     * @return bool
     */
    public function valid()
    {
        return key($this->collection) !== null;
    }

    /**
     * Function to return the size of the collection
     *
     * @return int size of collection
     */
    public function size()
    {
        return count($this->collection);
    }

    /**
     * Function to return the first item of the collection
     *
     * @return null|\Restoquent\Resource\Model
     */
    public function first()
    {
        return (empty($this->collection) ? null : $this->collection[0]);
    }

    /**
     * Function to return the last item of the collection
     *
     * @return null|\Restoquent\Resource\Model
     */
    public function last()
    {
        return (empty($this->collection) ? null : $this->collection[count($this->collection) - 1]);
    }

    /**
     * Function to convert the collection to json using each collection elements attributes as an array then encoding
     * the array to json
     *
     * @param  string $collectionKey
     * @param  string $metaKey
     *
     * @return array
     */
    public function toJson($collectionKey = null, $metaKey = 'meta')
    {
        return json_encode($this->toArray($collectionKey, $metaKey));
    }

    /**
     * Function to convert the collection to an array using each collection elements attributes
     *
     * @param string $collectionKey
     * @param string $metaKey
     *
     * @return array
     */
    public function toArray($collectionKey = null, $metaKey = 'meta')
    {
        $entities = [];
        foreach ($this->collection as $entity)
        {
            $entities[] = $entity->attributes();
        }

        $col = $collectionKey ? [$collectionKey => $entities] : $entities;
        $met = $this->metaData ? [$metaKey => $this->metaData] : [];

        return array_merge($col, $met);
    }
}
