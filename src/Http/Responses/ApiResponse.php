<?php

namespace Bluelightco\LeverPhp\Http\Responses;

class ApiResponse
{
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public static function using($response): self
    {
        return new self($response);
    }

    public function toArray(): array
    {
        return json_decode($this->response->getBody()->getContents(), true);
    }

    public function getStatus(): int
    {
        return $this->response->getStatusCode();
    }
}
