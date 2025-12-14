<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use Illuminate\Support\AggregateServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\ClientRequestWatcher;

final class ServiceProvider extends AggregateServiceProvider
{
    public function boot(): void
    {
        Telescope::hideRequestParameters(['APIGuid']);
        Telescope::tag(TagAction::tag(...));
    }

    public function register(): void
    {
        $this->app->extend(ClientRequestWatcher::class,
            static fn (ClientRequestWatcher $watcher) => new UnwrapDeRidderDenHertogXml($watcher)
        );
    }
}
