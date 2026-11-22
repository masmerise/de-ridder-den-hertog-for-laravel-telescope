<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

/** @internal */
final readonly class Endpoint
{
    private const array HOSTS = ['renh.online', 'test.renh.online'];

    private const string PATH = '/rhapi_web/awws/rhapi.awws';

    public static function matches(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($host) || ! is_string($path)) {
            return false;
        }

        return in_array(strtolower($host), self::HOSTS, true)
            && strtolower($path) === self::PATH;
    }

    private function __construct() {}
}
