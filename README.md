# Battlenet WoW API Client

[![Build Status](https://app.travis-ci.com/Raidkeeper/battlenet-api.svg?branch=main)](https://app.travis-ci.com/Raidkeeper/battlenet-api) [![Maintainability](https://api.codeclimate.com/v1/badges/ed6fcf6bd55309d13082/maintainability)](https://codeclimate.com/github/Raidkeeper/battlenet-api/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/ed6fcf6bd55309d13082/test_coverage)](https://codeclimate.com/github/Raidkeeper/battlenet-api/test_coverage)

This is an API client for Blizzard's World of Warcraft game, primarily used by [Raidkeeper](https://raidkeeper.com) but available for use with any application.

## Installation
You should install this using composer:
```bash
composer require raidkeeper/battlenet-api
```

## Usage
```php
// You will need a Battlnet ClientID and ClientSecret from https://api.battlenet.com
$clientId     = 'foobar';
$clientSecret = 'fizzbuzz';
$region       = 'us';

// The Battlenet client grants access to the API
$client = new Raidkeeper\Api\Battlenet\Client($region, $clientId, $clientSecret);

// Create a new Character object to interact with the 
$character = $client->loadCharacter('charname', 'sargeras');

// Once you have the Character object, you can start reaching out to API endpoints
$data  = $character->getProfile();
$data2 = $character->getEquipment();

```

## License
This library is published under the [MIT License](/LICENSE)