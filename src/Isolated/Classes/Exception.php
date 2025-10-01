<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use Leconfe\OaiMetadata\Isolated\Enums\ErrorCodes;

class Exception
{
    protected string $message;
    protected ErrorCodes $code;

    public function __construct(string $message, ErrorCodes|string $code)
    {
        $this->message = $message;
        $this->code  = $code instanceof ErrorCodes ? $code : ErrorCodes::from($code);
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): ErrorCodes
    {
        return $this->code;
    }
}