<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Integration\Traits;

use Illuminate\Contracts\Foundation\Application;
use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\EventPublisher;
use Plexikon\Reporter\Manager\ReporterDriverManager;
use Plexikon\Reporter\QueryPublisher;

trait RegisterServicePublisher
{
    /**
     * @var Application
     */
    protected $app;

    protected function registerPublishers(): void
    {
        $this->app->bind(CommandPublisher::class, function (Application $app) {
            return $app->get(ReporterDriverManager::class)->commandPublisher('default');
        });

        $this->app->bind(EventPublisher::class, function (Application $app) {
            return $app->get(ReporterDriverManager::class)->eventPublisher('default');
        });

        $this->app->bind(QueryPublisher::class, function (Application $app) {
            return $app->get(ReporterDriverManager::class)->queryPublisher('default');
        });
    }
}
