<?php

declare(strict_types=1);

namespace Webkernel\Imagery;

use Illuminate\Support\ServiceProvider;
use Webkernel\Imagery\Commands\GenerateIconEnumsCommand;
use Webkernel\Imagery\Commands\InstallIconsCommand;

/**
 * Registers icon picker + brand asset helpers for webkernel/imagery.
 */
final class ImageryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/imagery.php', 'imagery');

        $this->app->singleton(IconSetManager::class, static fn (): IconSetManager => new IconSetManager);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'imagery');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'imagery');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/imagery.php' => config_path('imagery.php'),
            ], 'imagery-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/imagery'),
            ], 'imagery-views');

            $this->commands([
                InstallIconsCommand::class,
                GenerateIconEnumsCommand::class,
            ]);
        }
    }
}
