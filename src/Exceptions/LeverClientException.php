<?php

namespace Bluelightco\LeverPhp\Exceptions;

use RuntimeException;

class LeverClientException extends RuntimeException
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct("Bluelightco\LeverPhp: " . $message, $code, $previous);
    }
}
