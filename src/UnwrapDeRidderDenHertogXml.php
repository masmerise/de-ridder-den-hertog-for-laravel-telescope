<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Telescope\Watchers\ClientRequestWatcher;
use Throwable;

final readonly class UnwrapDeRidderDenHertogXml
{
    use ForwardsCalls;

    public function __construct(private ClientRequestWatcher $watcher) {}

    public function register(Application $app): void
    {
        $app['events']->listen(ConnectionFailed::class, $this->watcher->recordFailedRequest(...));
        $app['events']->listen(ResponseReceived::class, $this->recordResponse(...));
    }

    public function recordResponse(ResponseReceived $event): void
    {
        if ($event->request->url() === 'https://renh.online/RHAPI_WEB/awws/RHAPI.awws') {
            try {
                /** @var \GuzzleHttp\Psr7\Request $request */
                $request = $event->request->toPsrRequest();
                /** @var \GuzzleHttp\Psr7\Response $response */
                $response = $event->response->toPsrResponse();

                $data = SoapRequest::unwrap($event->request->body());
                $request = $request->withBody(Utils::streamFor(json_encode($data)));
                $request = new Request($request);
                $request->withData($data);

                $data = SoapResponse::unwrap($event->response->body());
                $response = $response->withBody(Utils::streamFor(json_encode($data)));
                $response = new Response($response);
                $response->transferStats = $event->response->transferStats;

                $event = new ResponseReceived($request, $response);
            } catch (Throwable) {
                //
            }
        }

        $this->watcher->recordResponse($event);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->forwardCallTo($this->watcher, $name, $arguments);
    }
}
