<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use Leconfe\OaiMetadata\Isolated\Classes\Exception as OaiException;

class ExceptionBag
{
    protected array $items;

    public function __construct(array $exceptions = [])
    {
        $this->items = [];
        foreach ($exceptions as $exception) {
            $this->throw($exception);
        }
    }

    public function throw(OaiException $exception)
    {
        $this->items[] = $exception;
    }

    public function getExceptions(): array
    {
        return $this->items;
    }

    public function hasExceptions(): bool
    {
        return count($this->items) > 0;
    }
}