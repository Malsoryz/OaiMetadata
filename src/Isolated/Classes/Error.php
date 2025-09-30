<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use Leconfe\OaiMetadata\Isolated\Enums\ErrorCodes;

class Error
{
    public readonly string $message;
    public readonly ErrorCodes $code;

    public function __construct(string $message, ErrorCodes|string $code)
    {
        $this->message = $message;
        $this->code  = $code instanceof ErrorCodes ? $code : ErrorCodes::from($code);
    }
}