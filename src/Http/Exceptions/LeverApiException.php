<?php

namespace Bluelightco\LeverPhp\Http\Exceptions;

use RuntimeException;
use Throwable;

class LeverApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $statusCode = null,
        private readonly ?string $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public function isClientError(): bool
    {
        return $this->statusCode !== null && $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode !== null && $this->statusCode >= 500 && $this->statusCode < 600;
    }
}
