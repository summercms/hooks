<?php

namespace Larapack\Hooks\Tests;

use Illuminate\Filesystem\Filesystem;
use Larapack\Hooks\Hooks;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Larapack\Hooks\HooksServiceProvider'];
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Cleanup old hooks before testing
        app(Filesystem::class)->deleteDirectory(base_path('hooks'));

        // Clear old hooks
        $hook = app(Hooks::class);
        $hook->readJsonFile();

        // Delete testbench's fixures tests folder
        app(Filesystem::class)->deleteDirectory(base_path('tests'));

        // Clone hooks to testbench's vendor
        app(Filesystem::class)->deleteDirectory(base_path('vendor/larapack'));
        app(Filesystem::class)->makeDirectory(base_path('vendor/larapack'));
        app(Filesystem::class)->makeDirectory(base_path('vendor/larapack/hooks'));
        app(Filesystem::class)->copyDirectory(__DIR__.'/', base_path('larapack/hooks'));

        // Add hooks to testbench's composer.json
        $composer = json_decode(app(Filesystem::class)->get(base_path('composer.json')), true);
        $composer['require']['larapack/hooks'] = '*';
        $composer['autoload']['classmap'] = collect($composer['autoload']['classmap'])->filter(function ($value) {
            return $value !== 'tests/TestCase.php';
        })->all();
        $composer = app(Filesystem::class)->put(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        //app(Filesystem::class)->delete(base_path('composer.lock'));

        // Delete testbench's hooks tests folder
        app(Filesystem::class)->deleteDirectory(base_path('tests'));

        // Prepare Composer
        //app(Hooks::class)->composerCommand('install');
    }

    public function tearDown()
    {
        // Cleanup old hooks before testing
        app(Filesystem::class)->deleteDirectory(base_path('hooks'));

        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class PreparedHook
{
    public function __construct($data)
    {
        $this->data = $data;
    }
}
