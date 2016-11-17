<?php

/**
 * Created by PhpStorm.
 * User: dfranke
 * Date: 15.11.2016
 * Time: 08:13
 * Filename: Bootstrapper.php
 *
 * $Rev$
 * $Date$
 */

namespace Armybean\Restoquent;

use Illuminate\Container\Container;

class Bootstrapper {

    /**
     * The Container
     *
     * @var Container
     */
    protected $container;

    /**
     * Build a new Bootstrapper
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Bind paths to the container
     *
     * @return void
     */
    public function bindPaths()
    {
        $this->bindBase();
        $this->bindConfiguration();
    }

    ////////////////////////////////////////////////////////////////////////
    /////////////////////////////// BOOTSTRAPPIN ///////////////////////////
    ////////////////////////////////////////////////////////////////////////

    /**
     * Bind the base path to the Container
     *
     * @return void
     */
    protected function bindBase()
    {
        if ($this->app->bound('path.base'))
        {
            return;
        }

        $this->app->instance('path.base', getcwd());
    }

    /**
     * Bind paths to the configuration files
     *
     * @return void
     */
    protected function bindConfiguration()
    {
        $path = $this->app['path.base'] ? $this->app['path.base'] . '/' : '';
        $logs = $this->app->bound('path.storage') ? str_replace($this->unifySlashes($path), null,
            $this->unifySlashes($this->app['path.storage'])) : '.restoquent';

        $paths = [
            'config' => '.restoquent',
            'logs'   => $logs . '/logs',
        ];

        foreach ($paths as $key => $file)
        {
            $filename = $path . $file;

            // Check whether we provided a file or folder
            if ( ! is_dir($filename) and file_exists($filename . '.php'))
            {
                $filename .= '.php';
            }

            // Use configuration in current folder if none found
            $realpath = realpath('.') . '/' . $file;
            if ( ! file_exists($filename) and file_exists($realpath))
            {
                $filename = $realpath;
            }

            $this->app->instance('path.restoquent.' . $key, $filename);
        }
    }

    /**
     * Unify the slashes to the UNIX mode (forward slashes)
     *
     * @param string $path
     *
     * @return string
     */
    protected function unifySlashes($path)
    {
        return str_replace('\\', '/', $path);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// PATHS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Export the configuration files
     *
     * @return string
     */
    public function exportConfiguration()
    {
        $source = __DIR__ . '/../config';
        $destination = $this->getConfigurationPath();

        // Unzip configuration files
        $this->app['files']->copyDirectory($source, $destination);

        return $destination;
    }

    /**
     * Get the path to the configuration folder
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        // Return path to Laravel configuration
        if ($this->app->bound('path'))
        {
            $laravel = $this->app['path'] . '/config/packages/armybean/restoquent';
            if (file_exists($laravel))
            {
                return $laravel;
            }
        }

        return $this->app['path.restoquent.config'];
    }

    /**
     * Replace placeholders in configuration
     *
     * @param string $folder
     * @param array  $values
     *
     * @return void
     */
    public function updateConfiguration($folder, array $values = [])
    {
        // Replace stub values in files
        $files = $this->app['files']->files($folder);
        foreach ($files as $file)
        {
            foreach ($values as $name => $value)
            {
                $contents = str_replace('{' . $name . '}', $value, file_get_contents($file));
                $this->app['files']->put($file, $contents);
            }
        }
    }
}
