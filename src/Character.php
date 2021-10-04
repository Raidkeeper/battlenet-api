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

        $this->apiBase = 'wow/character/'.$this->realm.'/'.urlencode(strtolower($this->name));
    }

    public function getProfile(): Error|ApiResponse
    {
        $response = $this->client->fromEndpoint('profile', $this->apiBase)->get();
        if ($response instanceof Error) {
            return $response;
        }
        return new ApiResponse($response, $this->client->getLocale());
    }

    public function getEquipment(): Error|ApiResponse
    {
        $response = $this->client->fromEndpoint('profile', $this->apiBase.'/equipment')->get();
        if ($response instanceof Error) {
            return $response;
        }
        return new ApiResponse($response, $this->client->getLocale());
    }
}
