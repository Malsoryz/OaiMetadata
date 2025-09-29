<?php

namespace Leconfe\OaiMetadata\Contracts\Oai;

use App\Models\Submission;

interface HasMetadata
{
    public static function getMetadataFormat(): array;

    public static function serialize(Submission $paper): array; 
}