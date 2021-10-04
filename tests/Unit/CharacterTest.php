<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Raidkeeper\Api\Battlenet\ApiResponse;
use Raidkeeper\Api\Battlenet\Character;
use Raidkeeper\Api\Battlenet\Client;

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
        $this->assertInstanceOf(\Error::class, $data->guild);
        $this->assertInstanceOf(\Error::class, $data->covenant_progress);
        $this->assertEquals('https://us.api.blizzard.com/profile/wow/character/sargeras/raidkeeper/equipment?namespace=profile-us', $data->equipment);

        $raw = $data->getRaw();
        $this->assertEquals($data->character_class, $raw->character_class->name->en_US);
    }

    public function testCharacterProfileAlternateLocale(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $client->setLocale('de_DE');
        $character = $client->loadCharacter('raidkeeper', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);

        $data = $character->getProfile();
        $this->assertEquals($data->character_class, 'Krieger'); // German response for Warrior
    }

    public function testNotFoundCharacterProfile(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $character = $client->loadCharacter('foobarbazfizzbuzzinvalidcharactername', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);
	    
        $data = $character->getProfile();
        $this->assertInstanceOf(\Error::class, $data);
        $this->assertEquals($data->getCode(), 404);
        $this->assertEquals($data->getMessage(), 'Not Found');
    }

    public function testCharacterEquipment(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $character = $client->loadCharacter('raidkeeper', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);

        $data = $character->getEquipment();
        $this->assertInstanceOf(ApiResponse::class, $data);
        $this->assertIsArray($data->equipped_items);
        $this->assertObjectHasAttribute('item', $data->equipped_items[0]);
        $this->assertObjectHasAttribute('level', $data->equipped_items[0]);
    }

    public function testCharacterKeystones(): void
    {
        $client = new Client('us', getenv('RK_TEST_BATTLENET_CLIENT_ID'), getenv('RK_TEST_BATTLENET_CLIENT_SECRET'));
        $character = $client->loadCharacter('raidkeeper', 'sargeras');
        $this->assertInstanceOf(Character::class, $character);

        $data = $character->getKeystones();
        $this->assertInstanceOf(ApiResponse::class, $data);
        $this->assertIsInt($data->current_period->period->id);
    }
}
