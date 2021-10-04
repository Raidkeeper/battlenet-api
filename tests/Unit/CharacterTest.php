<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Raidkeeper\Api\Battlenet\Character;
use Raidkeeper\Api\Battlenet\Client;
use Raidkeeper\Api\Battlenet\Error;

final class CharacterTest extends TestCase
{

    public function testCharacterProfile(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $character = $client->loadCharacter('raidkeeper', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);
        $data = $character->getProfile();

        $this->assertEquals($data->name, 'Raidkeeper');
        $this->assertEquals($data->gender, 'Male');
        $this->assertEquals($data->faction, 'Alliance');
        $this->assertEquals($data->race, 'Dark Iron Dwarf');
        $this->assertEquals($data->character_class, 'Warrior');
    }

    public function testNotFoundCharacterProfile(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $character = $client->loadCharacter('foobarbazfizzbuzzinvalidcharactername', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);
	$data = $character->getProfile();
        $this->assertInstanceOf(Error::class, $data);
        $this->assertEquals($data->getCode(), 404);
        $this->assertEquals($data->getMessage(), 'Not Found');
    }
}
