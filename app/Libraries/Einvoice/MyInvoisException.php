<?php

namespace App\Libraries\Einvoice;

class MyInvoisException extends \RuntimeException
{
    public ?array $payload;

    public function __construct(string $message, int $code = 0, ?array $payload = null, ?\Throwable $prev = null)
    {
        parent::__construct($message, $code, $prev);
        $this->payload = $payload;
    }
}
