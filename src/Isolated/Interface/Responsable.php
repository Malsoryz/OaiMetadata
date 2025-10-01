<?php

namespace Leconfe\OaiMetadata\Isolated\Interface;

use Leconfe\OaiMetadata\Isolated\Oai;

interface Responsable
{
    public static function handle(Oai $repository, array $query): static;
}