<?php

namespace Leconfe\OaiMetadata\Isolated\Enums;

use Leconfe\OaiMetadata\Isolated\Classes\Metadata\DublinCore;

enum Metadata: string 
{
    case DublinCore = 'oai_dc';

    public function getClass(): string
    {
        return match ($this) {
            self::DublinCore => DublinCore::class,
        };
    }
}