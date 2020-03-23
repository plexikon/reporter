<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Support\ServiceProvider;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;

class ReporterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getConfigPath() => config_path('reporter.php')],
                'config'
            );
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'reporter');

        $this->registerMessageFactories();
    }

    protected function registerMessageFactories(): void
    {
        $config = config('reporter.message');

        $this->app->bindIf(MessageAlias::class, $config['alias']);
        $this->app->bindIf(MessageSerializer::class, $config['serializer']);
        $this->app->bindIf(MessageFactory::class, $config['factory']);

        $this->app->singleton(PublisherManager::class);
    }

    public function provides(): array
    {
        return [
            MessageAlias::class, MessageSerializer::class, MessageFactory::class,
            PublisherManager::class
        ];
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../../config/reporter.php';
    }
}
