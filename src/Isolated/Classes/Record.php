<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Classes\Identifier;
use Leconfe\OaiMetadata\Isolated\Enums\Granularity;
use Leconfe\OaiMetadata\Isolated\Interface\Serializable;

class Record
{
    protected Identifier $header;
    protected Serializable $metadata;

    public function __construct(Oai $repository)
    {
        $this->header = new Identifier(
            $repository->getOaiIdentifier()->createIdentifier(1234567890),
            $repository->getGranularity()->format(now())
        );
    }
}