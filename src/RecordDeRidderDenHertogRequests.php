<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Telescope\Watchers\ClientRequestWatcher;
use Psr\Http\Message\RequestInterface;
use Saloon\Config;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response as SaloonResponse;
use Throwable;
use WeakMap;

/**
 * Decorates Telescope's client request watcher so requests sent through the
 * De Ridder & Den Hertog SDK (Saloon + GuzzleSender, which bypasses Laravel's
 * Http facade) are recorded too, with their SOAP envelopes unwrapped.
 */
final class RecordDeRidderDenHertogRequests
{
    use ForwardsCalls;

    /** The middleware is registered globally once per process, so it routes to the latest watcher. */
    private static ?self $current = null;

    private static bool $middlewareRegistered = false;

    /** @var WeakMap<PendingRequest, float> */
    private WeakMap $startedAt;

    public function __construct(private readonly ClientRequestWatcher $watcher)
    {
        $this->startedAt = new WeakMap();
    }

    public function register(Application $app): void
    {
        $this->watcher->register($app);

        self::$current = $this;

        if (self::$middlewareRegistered) {
            return;
        }

        self::$middlewareRegistered = true;

        Config::globalMiddleware()
            ->onRequest(static function (PendingRequest $pendingRequest): void {
                self::$current?->start($pendingRequest);
            })
            ->onResponse(static function (SaloonResponse $response): void {
                self::$current?->recordSaloonResponse($response);
            })
            ->onFatalException(static function (FatalRequestException $exception): void {
                self::$current?->recordSaloonFailure($exception);
            });
    }

    public function start(PendingRequest $pendingRequest): void
    {
        if (Endpoint::matches($pendingRequest->getUrl())) {
            $this->startedAt[$pendingRequest] = microtime(true);
        }
    }

    public function recordSaloonResponse(SaloonResponse $response): void
    {
        try {
            $psrRequest = $response->getPsrRequest();

            if (! Endpoint::matches((string) $psrRequest->getUri())) {
                return;
            }

            $request = $this->unwrapRequest($psrRequest);

            $psrResponse = $response->getPsrResponse();
            try {
                $psrResponse = $psrResponse->withBody(
                    Utils::streamFor(json_encode(SoapResponse::unwrap($response->body())))
                );
            } catch (Throwable) {
                // Keep the raw envelope (e.g. SOAP faults) so the entry is still recorded.
            }

            $illuminateResponse = new Response($psrResponse);

            if (($startedAt = $this->startedAt[$response->getPendingRequest()] ?? null) !== null) {
                $illuminateResponse->transferStats = new TransferStats($psrRequest, $psrResponse, microtime(true) - $startedAt);
            }

            $this->watcher->recordResponse(new ResponseReceived($request, $illuminateResponse));
        } catch (Throwable) {
            // Recording must never interfere with the actual API call.
        }
    }

    public function recordSaloonFailure(FatalRequestException $exception): void
    {
        try {
            $psrRequest = $exception->getPendingRequest()->createPsrRequest();

            if (! Endpoint::matches((string) $psrRequest->getUri())) {
                return;
            }

            $this->watcher->recordFailedRequest(new ConnectionFailed(
                $this->unwrapRequest($psrRequest),
                new ConnectionException($exception->getMessage(), 0, $exception),
            ));
        } catch (Throwable) {
            // Recording must never interfere with the actual API call.
        }
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->forwardCallTo($this->watcher, $name, $arguments);
    }

    private function unwrapRequest(RequestInterface $psrRequest): Request
    {
        try {
            $data = SoapRequest::unwrap((string) $psrRequest->getBody());
        } catch (Throwable) {
            return new Request($psrRequest);
        }

        $request = new Request($psrRequest->withBody(Utils::streamFor(json_encode($data))));
        $request->withData($data);

        return $request;
    }
}
