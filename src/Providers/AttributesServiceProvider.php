<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Attributes\Models\Attribute;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Attributes\Models\AttributeEntity;
use Rinvex\Attributes\Console\Commands\MigrateCommand;
use Rinvex\Attributes\Console\Commands\PublishCommand;
use Rinvex\Attributes\Console\Commands\RollbackCommand;

class AttributesServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.attributes.migrate',
        PublishCommand::class => 'command.rinvex.attributes.publish',
        RollbackCommand::class => 'command.rinvex.attributes.rollback',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.attributes');

        // Bind eloquent models to IoC container
        $this->registerModels([
            'rinvex.attributes.attribute' => Attribute::class,
            'rinvex.attributes.attribute_entity' => AttributeEntity::class,
        ]);

        // Register attributes entities
        $this->app->singleton('rinvex.attributes.entities', function ($app) {
            return collect();
        });

        // Register console commands
        // $this->registerCommands($this->commands);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Publish Resources
        // $this->publishesConfig('rinvex/laravel-attributes');
        // $this->publishesMigrations('rinvex/laravel-attributes');
        // ! $this->autoloadMigrations('rinvex/laravel-attributes') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $package = 'rinvex/laravel-attributes';
        $isModule = false;
        $namespace = str_replace('laravel-', '', $package);
        $basePath = $isModule ? $this->app->basePath($package) : $this->app->basePath('vendor/'.$package);
        $path = $basePath.'/database/migrations';

        $this->publishConfigFrom($path, $namespace);
        $this->publishMigrationsFrom($path, $namespace);
        ! $this->autoloadMigrations('rinvex/laravel-attributes') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Can publish resources.
     *
     * @return bool
     */
    protected function publishesResources(): bool
    {
        return ! $this->app->environment('production') || $this->app->runningInConsole() || $this->runningInDevzone();
    }

    /**
     * Can autoload migrations.
     *
     * @param string $module
     *
     * @return bool
     */
    protected function autoloadMigrations(string $module): bool
    {
        return $this->publishesResources() && $this->app['config'][str_replace(['laravel-', '/'], ['', '.'], $module).'.autoload_migrations'];
    }
}
