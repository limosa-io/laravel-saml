<?php

namespace ArieTimmerman\Laravel\SAML\Tests;

use ArieTimmerman\Laravel\SAML\Tests\Model\User;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    protected $baseUrl = 'http://localhost';
    
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return ['ArieTimmerman\Laravel\SAML\ServiceProvider'];
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadLaravelMigrations('testbench');
        
        $this->withFactories(realpath(dirname(__DIR__).'/database/factories'));
        
        \ArieTimmerman\Laravel\SAML\RouteProvider::routes();
        
        factory(User::class, 100)->create();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app ['config']->set('app.url', 'http://localhost');

        $app['config']->set('app.key', 'base64:1234mRasdLA123F0JiF02Og3bLXbk5qPE8H3+vX2O5M=');

        ;
        $app ['config']->set('saml', include realpath(dirname(__DIR__).'/config/saml.php'));
        
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
    
    public function testGet()
    {
        $response = $this->get('/saml/v2/metadata.xml');
        
        $response->assertStatus(200);
    }
}
