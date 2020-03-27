<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Support\ServiceProvider;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\PayloadSerializer;

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
        $message = config('reporter.message');

        $this->app->bindIf(MessageAlias::class, $message['alias']);
        $this->app->bindIf(MessageSerializer::class, $message['serializer']);
        $this->app->bindIf(PayloadSerializer::class, $message['payload_serializer']);
        $this->app->bindIf(MessageFactory::class, $message['factory']);

        $this->app->singleton(PublisherServiceManager::class);
    }

    public function provides(): array
    {
        return [
            MessageAlias::class, MessageSerializer::class, PayloadSerializer::class, MessageFactory::class,
            PublisherServiceManager::class
        ];
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../../config/reporter.php';
    }
}
