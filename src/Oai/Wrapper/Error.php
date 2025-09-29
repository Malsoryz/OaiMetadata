<?php

namespace Leconfe\OaiMetadata\Oai\Wrapper;

use Leconfe\OaiMetadata\Oai\Query\ErrorCodes as Codes;

class Error
{
    protected string $message;
    protected string $code;

    public function __construct(string $message, string $code)
    {
        $this->message = $message;

        $allErrors = Codes::allErrors();
        if (in_array($code, $allErrors)) {
            $this->code = $code;
        } else {
            throw new InvalidArgumentException("'{$code}' is not a valid ErrorCodes");
        }
    }

    public function __toArray()
    {
        return [
            '_value' => $this->message,
            '_attributes' => [
                'code' => $this->code,
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            '_value' => $this->message,
            '_attributes' => [
                'code' => $this->code,
            ]
        ];
    }

    public static function getErrorsFrom(array $errors): array
    {
        return array_map(function ($error) {
            if (! $error instanceof static) {
                throw new \UnexpectedValueException("Return array items expected to be instance of ".OaiError::class);
            }

            return $error->toArray();
        }, $errors);
    }
}