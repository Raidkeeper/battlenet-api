<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Raidkeeper\Api\Battlenet\Client;

final class ClientTest extends TestCase
{
    public function testClassCreation(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testEndpointSetup(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $client->fromEndpoint('profile', 'wow/character/foo/bar');

        $this->assertEquals($client->headers[0], 'User-Agent: Raidkeeper <gitlab.com/raidkeeper/raidkeeper>');
        $this->assertEquals($client->headers[1], 'Battlenet-Namespace: profile-us');
    }
}