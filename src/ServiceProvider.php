<?php

/**
 * Laravel service provider for registering the routes and publishing the configuration.
 */

namespace ArieTimmerman\Laravel\SAML;

use ArieTimmerman\Laravel\SAML\Console\Commands\ParseMetadata;
use ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepository;
use ArieTimmerman\Laravel\SAML\Providers\HostedIdentityProviderProcessor;
use SimpleSAML\Configuration;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'saml.php' => config_path('saml.php'),
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'saml_sp.php' => config_path('saml_sp.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations');

        // $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');

        $this->loadViewsFrom(__DIR__ . '/../views/', 'saml');

        $router->middleware('SAMLState', 'ArieTimmerman\Laravel\SAML\Middleware\SAMLState');

        $this->app->bindIf('ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepositoryInterface', RemoteServiceProviderConfigRepository::class);
        $this->app->bindIf('ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface', HostedIdentityProviderProcessor::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ParseMetadata::class
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Configuration::loadFromArray([], '[ARRAY]', 'simplesaml');

        $this->deepMergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'saml.php',
            'saml'
        );

        $this->mergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'saml_sp.php',
            'saml_sp'
        );
    }

    protected function deepMergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, array_replace_recursive(require $path, $config));
    }
}
