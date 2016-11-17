<?php

namespace Armybean\Restoquent;

use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class RestoquentServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register classes
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // done with boot()
        $this->app = static::make($this->app);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['armybean/restoquent'];
    }

    /**
     * @param null|Container $app
     *
     * @return \Illuminate\Container\Container|null
     */
    public static function make($app = null)
    {
        if ( ! $app)
        {
            $app = new Container;
        }

        $serviceProvider = new static($app);

        //bind paths
        $app = $serviceProvider->bindPaths($app);

        // Bind classes
        $app = $serviceProvider->bindCoreClasses($app);
        $app = $serviceProvider->bindClasses($app);

        return $app;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CLASS BINDINGS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Bind the Restoquent paths
     *
     * @param Container $app
     *
     * @return Container
     */
    public function bindPaths(Container $app)
    {
        $app->bind('restoquent.bootstrapper', function ($app)
        {
            return new Bootstrapper($app);
        });

        // Bind paths
        $app['restoquent.bootstrapper']->bindPaths();

        return $app;
    }

    /**
     * Bind the core classes
     *
     * @param Container $app
     *
     * @return Container
     */
    public function bindCoreClasses(Container $app)
    {
        $app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

        $app->bindIf('config', function ($app)
        {
            $fileloader = new FileLoader($app['files'], __DIR__ . '/../../config');

            return new Repository($fileloader, 'config');
        }, true);

        // Register factory and custom configurations
        $app = $this->registerConfig($app);

        return $app;
    }

    /**
     * Bind the ActiveResource classes to the Container
     *
     * @param Container $app
     *
     * @return Container
     */
    public function bindClasses(Container $app)
    {
        $app->singleton('restoquent.urls', function ($app)
        {
            return new Url\UrlGenerator($app);
        });

        $app->singleton('restoquent.config-manager', function ($app)
        {
            return new Support\ConfigManager($app);
        });

        $app->bind('restoquent.instance-finder', function ($app)
        {
            return new Finders\InstanceFinder($app);
        });

        $app->bind('restoquent.collection-finder', function ($app)
        {
            return new Finders\CollectionFinder($app);
        });

        $app->bind('restoquent.response', function ($app)
        {
            return new Responses\Response($app);
        });

        $app->bind('restoquent.model', function ($app)
        {
            return new Resource\Model();
        });

        // Factories
        $app->bind('restoquent.conditions', function ($app)
        {
            return new Factories\QueryConditionFactory($app);
        });

        $app->bind('restoquent.transporter', function ($app)
        {
            return new Factories\ApiTransporterFactory($app);
        });

        $app->bind('restoquent.order', function ($app)
        {
            return new Factories\QueryResultOrderFactory($app);
        });

        $app->bind('restoquent.interpreter', function ($app)
        {
            return new Factories\ResponseInterpreterFactory($app);
        });

        $app->bind('restoquent.error-handler', function ($app)
        {
            return new Factories\ErrorHandlerFactory($app);
        });

        $app->bind('restoquent.request-factory', function ($app)
        {
            return new Factories\RequestFactory($app);
        });

        $app->bind('restoquent.auth', function ($app)
        {
            return new Factories\AuthFactory($app);
        });

        return $app;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Register factory and custom configurations
     *
     * @param Container $app
     *
     * @return Container
     */
    protected function registerConfig(Container $app)
    {
        // Register config file(filename)
        $app['config']->package('armybean/restoquent', __DIR__ . '/../../config');
        $app['config']->getLoader();

        // Register custom config
        $custom = $app['path.restoquent.config'];
        if (file_exists($custom))
        {
            $app['config']->afterLoading('restoquent', function ($me, $group, $items) use ($custom)
            {
                $customItems = $custom . '/' . $group . '.php';
                if ( ! file_exists($customItems))
                {
                    return $items;
                }

                $customItems = include $customItems;

                return array_replace_recursive($items, $customItems);
            });
        }

        return $app;
    }

}
