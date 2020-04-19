<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Plexikon\Reporter\Contracts\Clock\Clock;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\PayloadSerializer;

class ReporterServiceProvider extends ServiceProvider implements DeferrableProvider
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

        $this->app->bindIf(Clock::class, config('reporter.clock'));

        $this->registerMessageFactories();

        $this->app->singleton(ReporterDriverManager::class);
    }

    protected function registerMessageFactories(): void
    {
        $message = config('reporter.message');

        $this->app->bindIf(MessageAlias::class, $message['alias']);
        $this->app->bindIf(MessageSerializer::class, $message['serializer']);
        $this->app->bindIf(PayloadSerializer::class, $message['payload_serializer']);
        $this->app->bindIf(MessageFactory::class, $message['factory']);
    }

    public function provides(): array
    {
        return [
            MessageAlias::class, MessageSerializer::class, PayloadSerializer::class, MessageFactory::class,
            ReporterDriverManager::class
        ];
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../../config/reporter.php';
    }
}
