<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

class Identifier
{
    public readonly string $identifier;
    public readonly string $datestamp;
    public readonly array $setSpecs;

    public function __construct(string $identifier, string $datestamp, array $setSpecs = [])
    {
        // add validator of identifier
        $this->identifier = $identifier;

        $this->datestamp = $datestamp;
        $this->setSpecs = $setSpecs;
    }
}