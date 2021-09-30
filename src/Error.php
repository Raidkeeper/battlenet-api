<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

class Error
{
    protected string $message;

    protected int $code;

    public function __construct(string $message, int $code = 500)
    {
        $this->message = $message;
        $this->code    = $code;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function code(): int
    {
        return $this->code;
    }
}
