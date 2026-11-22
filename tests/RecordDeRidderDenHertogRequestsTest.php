<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope\Tests;

use DeRidderDenHertog\Authentication\ApiGuid;
use DeRidderDenHertog\DeRidderDenHertog;
use DeRidderDenHertog\Laravel\Telescope\ServiceProvider;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;
use Laravel\Telescope\Watchers\ClientRequestWatcher;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class RecordDeRidderDenHertogRequestsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [TelescopeServiceProvider::class, ServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('telescope.enabled', true);
        $app['config']->set('telescope.watchers', [ClientRequestWatcher::class => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Telescope::flushEntries();
        Telescope::startRecording();

        MockClient::global([
            MockResponse::make($this->envelope(['Status' => 'Ok', 'Answer' => '', 'ErrorString' => '', 'Records' => ['APIFunctions' => []]])),
        ]);
    }

    protected function tearDown(): void
    {
        MockClient::destroyGlobal();

        parent::tearDown();
    }

    #[Test]
    public function it_records_unwrapped_production_requests(): void
    {
        $this->sdk()->getApiFunctions();

        $entry = $this->onlyClientRequestEntry();
        $this->assertSame('https://renh.online/RHAPI_WEB/awws/RHAPI.awws', $entry->content['uri']);
        $this->assertSame('GetAPIFunctions', $entry->content['payload']['Action']);
        $this->assertSame('********', $entry->content['payload']['APIGuid']);
        $this->assertSame(['Status' => 'Ok', 'Answer' => '', 'ErrorString' => '', 'Records' => ['APIFunctions' => []]], $entry->content['response']);
        $this->assertContains('Action:GetAPIFunctions', $entry->tags);
    }

    #[Test]
    public function it_records_unwrapped_test_environment_requests(): void
    {
        $this->sdk()->useTest()->getApiFunctions();

        $entry = $this->onlyClientRequestEntry();
        $this->assertSame('https://test.renh.online/rhapi_web/awws/rhapi.awws', $entry->content['uri']);
        $this->assertSame('GetAPIFunctions', $entry->content['payload']['Action']);
        $this->assertContains('Action:GetAPIFunctions', $entry->tags);
    }

    private function sdk(): DeRidderDenHertog
    {
        return DeRidderDenHertog::authenticate(ApiGuid::fromString('{6F9619FF-8B86-D011-B42D-00C04FC964FF}'));
    }

    private function envelope(array $result): string
    {
        $payload = base64_encode(json_encode($result));

        return '<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            . "<RHDataServiceResponse><RHDataServiceResult>{$payload}</RHDataServiceResult></RHDataServiceResponse>"
            . '</soap:Body></soap:Envelope>';
    }

    private function onlyClientRequestEntry(): IncomingEntry
    {
        $entries = array_values(array_filter(
            Telescope::$entriesQueue,
            static fn (IncomingEntry $entry) => $entry->type === EntryType::CLIENT_REQUEST,
        ));

        $this->assertCount(1, $entries);

        return $entries[0];
    }
}
