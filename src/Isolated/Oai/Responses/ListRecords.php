<?php

namespace Leconfe\OaiMetadata\Isolated\Oai\Responses;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Interface\Responsable;

class ListRecords implements Responsable
{
    public readonly array $records;

    public function __construct(Oai $repository, array $query)
    {

    }

    public static function handle(Oai $repository, array $query): static
    {
        return new static($repository, $query);
    }
}