<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use Illuminate\Support\Str;
use InvalidArgumentException;

class Set
{
    public readonly string $setSpec;
    public readonly string $setName;
    public readonly ?string $description;

    public function __construct(string $setSpec, string $setName, ?string $description = null)
    {
        if (! Str::of($setSpec)->contains(' ')) {
            $this->setSpec = $setSpec;
        } else throw new InvalidArgumentException("Invalid setSpec");

        $this->setName = $setName;
        $this->description = $description;
    }
}