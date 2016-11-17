<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 07:50
 * Filename: Model.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent\Resource;

use Armybean\Restoquent\Facades\AuthFactory;
use Armybean\Restoquent\Facades\Collection;
use Armybean\Restoquent\Facades\Config;
use Armybean\Restoquent\Facades\ErrorHandlerFactory;
use Armybean\Restoquent\Facades\Instance;
use Armybean\Restoquent\Facades\RequestFactory;
use Armybean\Restoquent\Facades\ResponseInterpreterFactory;
use Armybean\Restoquent\Facades\UrlGenerator;
use Armybean\Restoquent\Finders\Conditions\QueryConditionInterface;
use Armybean\Restoquent\Finders\Conditions\QueryResultOrderInterface;
use Armybean\Restoquent\Requests\RequestableInterface;
use Armybean\Restoquent\Responses\Interpreters\ResponseInterpreterInterface;
use Armybean\Restoquent\Responses\Response;
use Illuminate\Container\Container;

/**
 * Class Model
 *
 * @package Armybean\Restoquent\Resource
 */
class Model {

    /**
     * Property to hold the data about entities for which this resource is nested beneath.  For example if this
     * entity was 'Employee' which was a nested resource under a 'Company' and the instance URI should be
     * /companies/:company_id/employees/:id then you would assign this string with 'Company:company_id'. Doing this
     * will allow you to pass in ':company_id' as an option to the URI creation functions and ':company_id' will be
     * replaced with the value passed.
     *
     * Alternativley you could set the value to something like 'Company:100'. You could do this before a call like:
     *
     * <code>
     * $e = new Employee;
     * $e->nestedUnder = 'Company:100';
     * $found = Employee::find(1, [], $e);
     * //this would generate /companies/100/employees/1
     * </code>
     *
     *
     * This value can be nested as a comma separated string as well. So you could set something like
     * "Company:company_id,Employee:employee_id,Preference:pref_id" which would generate
     * /companies/:company_id/employees/:employee_id/preferences/:pref_id
     *
     * @var string
     */
    public $nestedUnder;

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * The name of the resource which is used to determine the resource URI through the use of reflection. By default
     * if this is not set the class name will be used.
     *
     * @var string
     */
    protected $resourceName;

    /**
     * Property to overwrite the getURI() function with a static value of what remote API URI path to hit
     *
     * @var string
     */
    protected $uri;

    /**
     * Array of instance values
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Remote resource's primary key property
     *
     * @var string
     */
    protected $identityProperty;

    /**
     * Var to hold instance errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Comma separated list of properties that can't be set via mass assignment
     *
     * @var string
     */
    protected $guarded = '';

    /**
     * Comma separated list of properties that may be in
     * a GET request but should not be added to a create or
     * update request
     *
     * @var string
     */
    protected $readOnlyFields = '';

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Function to fill an instance's properties from an array of keys and values
     *
     * @param array $attributes Associative array of properties and values
     *
     * @return void
     */
    public function fill(array $attributes = [])
    {
        $guarded = $this->getGuardedAttributes();

        foreach ($attributes as $property => $value)
        {
            if ( ! in_array($property, $guarded))
            {
                $this->properties[$property] = $value;
            }
        }
    }

    /**
     * Function to return an array of properties that should not be set via mass assignment
     *
     * @return array
     */
    public function getGuardedAttributes()
    {
        $attrs = array_map('trim', explode(',', $this->guarded));

        // the identityProperty should always be guarded
        if ( ! in_array($this->getIdentityProperty(), $attrs))
        {
            $attrs[] = $this->getIdentityProperty();
        }

        return $attrs;
    }

    /**
     * Getter function to return the identity property
     *
     * @return string
     */
    public function getIdentityProperty()
    {
        return $this->identityProperty ?: Config::get('resource.identity_property');
    }

    /**
     * Function to find an instance of an Entity record
     *
     * @param int   $id        The primary identifier value for the record
     * @param array $getParams Array of GET parameters to pass
     * @param Model $instance  An instance to use for interpreting url values
     *
     * @return Model           An instance of the entity requested
     */
    public static function find($id, array $getParams = [], Model $instance = null)
    {
        $m = $instance ?: new static;

        return Instance::fetch($m, $id, $getParams);
    }

    /**
     * Function to find a collection of Entity records from the remote api
     *
     * @param  QueryConditionInterface   $condition   query conditions
     * @param  QueryResultOrderInterface $resultOrder result ordering info
     * @param  array                     $getParams   additional GET params
     *
     * @return \Armybean\Restoquent\Responses\Collection
     */
    public static function all(
        QueryConditionInterface $condition = null,
        QueryResultOrderInterface $resultOrder = null,
        array $getParams = []
    ) {
        return Collection::fetch(new static, $condition, $resultOrder, $getParams);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     *
     * @return \Armybean\Restoquent\Resource\Model
     */
    public function newInstance(array $attributes = [])
    {
        // This method just provides a convenient way for us to generate fresh model instances of this current model.
        // It is particularly useful during the hydration of new objects.
        $model = new static;

        $model->fill((array) $attributes);

        return $model;
    }

    /**
     * Magic getter function for accessing instance properties
     *
     * @param string $key Property name
     *
     * @return mixed      The value stored in the property
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->properties))
        {
            return $this->properties[$key];
        }

        return null;
    }

    /**
     * Magic setter function for setting instance properties
     *
     * @param string $property Property name
     * @param mixed  $value    The value to store for the property
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->properties[$property] = $value;
    }

    /**
     * Magic unsetter function for unsetting an instance property
     *
     * @param string $property Property name
     *
     * @return void
     */
    public function __unset($property)
    {
        if (array_key_exists($property, $this->properties))
        {
            unset($this->properties[$property]);
        }
    }

    /**
     * Getter function to access the underlying attributes array for the entity
     *
     * @return array
     */
    public function attributes()
    {
        return $this->properties;
    }

    /**
     * Function to return any errors that may have prevented a save
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Function to get an associative array of fields with their values that are NOT read only
     *
     * @return array
     */
    public function getMutableFields()
    {
        $cantSet = $this->getReadOnlyFields();

        $mutableFields = [];

        //set the property attributes
        foreach ($this->properties as $key => $value)
        {
            if ( ! in_array($key, $cantSet))
            {
                $mutableFields[$key] = $value;
            }
        }

        return $mutableFields;
    }

    /**
     * Function to return an array of property names that are read only
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        $cantSet = array_map('trim', explode(',', $this->readOnlyFields));

        return $cantSet;
    }

    /**
     * Function to interpret the URI resource name based on the class called. Generally this would be the name of the
     * class.
     *
     * @return string The sub name of the resource
     */
    public function getResourceName()
    {
        if (isset($this->resourceName))
        {
            return $this->resourceName;
        }

        $full_class_arr = explode("\\", get_called_class());
        $class = end($full_class_arr);
        $this->resourceName = $class;

        return $class;
    }

    /**
     * Getter function to return a URI that has been manually set
     *
     * @return string
     */
    public function getURI()
    {
        return $this->uri ?: null;
    }

    /**
     * Function to handle persistance of the entity across the remote API. Function will handle either a CREATE or
     * UPDATE
     *
     * @return Boolean Success of the save operation
     */
    public function save()
    {
        /** @var RequestableInterface $request */
        //get a request object
        $request = RequestFactory::build();

        if ($this->getId() === false)
        {
            //make a CREATE request
            $request->createRequest(
                UrlGenerator::getCreateUri($this),
                'POST',
                [], // no extra headers
                Config::get('request.http_method_param')
            );
        }
        else
        {
            //make an UPDATE request
            $request->createRequest(
                UrlGenerator::getUpdateUri($this, [':' . $this->getIdentityProperty() => $this->getId()]),
                'PUT',
                [], // no extra headers
                Config::get('request.http_method_param')
            );
        }

        // add auth if it is needed
        if ($auth = AuthFactory::build())
        {
            $request->authenticate($auth);
        }

        // set the property attributes on the request
        $request->setModelProperties($this);

        /** @var Response $response */
        // actually send the request
        $response = $request->sendRequest();

        // handle clean response with errors
        if (ResponseInterpreterFactory::build()->invalid($response))
        {
            // get the errors and set them to our local collection
            $this->errors = ErrorHandlerFactory::build()->parseErrors($response);

            return false;
        }

        // get the response and inflate from that
        $data = $response->parseResponseToData();
        $this->fill($data);

        // inflate the ID property that should be guarded and thus not fillable
        $id = $this->getIdentityProperty();
        if (array_key_exists($id, $data))
        {
            $this->{$id} = $data[$id];
        }

        return true;
    }

    /**
     * Function to get the instance ID, returns false if there is not one
     *
     * @return int|bool
     */
    public function getId()
    {
        if (array_key_exists($this->getIdentityProperty(), $this->properties))
        {
            return $this->properties[$this->getIdentityProperty()];
        }

        return false;
    }

    /**
     * Function to delete an existing entity
     *
     * @return Boolean Success of the delete operation
     */
    public function destroy()
    {
        /** @var RequestableInterface $request */
        //get a request object
        $request = RequestFactory::build();

        //init the request
        $request->createRequest(
            UrlGenerator::getDeleteUri($this, [':' . $this->getIdentityProperty() => $this->getId()]),
            'DELETE',
            [], //no extra headers
            Config::get('request.http_method_param')
        );

        // add auth if it is needed
        if ($auth = AuthFactory::build())
        {
            $request->authenticate($auth);
        }

        // actually send the request
        $response = $request->sendRequest();

        /** @var ResponseInterpreterInterface $interpreter */
        $interpreter = ResponseInterpreterFactory::build();

        //handle clean response with errors
        if ($interpreter->success($response))
        {
            return true;
        }

        if ($interpreter->invalid($response))
        {
            //get the errors and set them to our local collection
            $this->errors = ErrorHandlerFactory::build()->parseErrors($response);
        }

        return false;
    }

}
