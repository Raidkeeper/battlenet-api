<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

class Character
{
    protected string $realm;
    protected string $name;
    protected int    $id;
    protected Client $client;

    public function __construct(string $name, string $realm)
    {
        $this->name   = $name;
        $this->realm  = $realm;
    }
}
