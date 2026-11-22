<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

final readonly class TagAction
{
    public static function tag(IncomingEntry $entry): array
    {
        if ($entry->type !== EntryType::CLIENT_REQUEST) {
            return [];
        }

        if (! Endpoint::matches($entry->content['uri'] ?? '')) {
            return [];
        }

        if (! is_array($entry->content['payload'] ?? null) || ! array_key_exists('Action', $entry->content['payload'])) {
            return [];
        }

        $action = $entry->content['payload']['Action'];

        return ["Action:{$action}"];
    }
}
