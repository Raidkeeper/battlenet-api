<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

class Character
{
    protected string $realm;
    protected string $name;
    protected int    $id;
    protected Client $client;
    protected string $locale;
    protected string $apiBase;

    public function __construct(string $name, string $realm, Client $client)
    {
        $this->name   = $name;
        $this->realm  = $realm;
        $this->client = $client;

        $this->apiBase = 'wow/character/'.$this->realm.'/'.urlencode(mb_strtolower($this->name, 'UTF-8'));
    }

    public function getProfile(): \Error|ApiResponse
    {
        return $this->client->fromEndpoint('profile', $this->apiBase)->get();
    }

    public function getEquipment(): \Error|ApiResponse
    {
        return $this->client->fromEndpoint('profile', $this->apiBase.'/equipment')->get();
    }

    public function getKeystones(): \Error|ApiResponse
    {
        return $this->client->fromEndpoint('profile', $this->apiBase.'/mythic-keystone-profile')->get();
    }
}
