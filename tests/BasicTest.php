<?php

namespace ArieTimmerman\Laravel\SCIMServer\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    protected $baseUrl = 'http://localhost';
    
    use RefreshDatabase;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->loadLaravelMigrations('testbench');
        
        $this->withFactories(realpath(dirname(__DIR__).'/database/factories'));
        
        \ArieTimmerman\Laravel\SAML\RouteProvider::routes();
        
        factory(\App\User::class, 100)->create();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app ['config']->set('app.url', 'http://localhost');
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
        $response = $this->get('/test/something');
        
        $response->assertStatus(200);
    }
}
