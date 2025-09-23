<?php

namespace Malsoryz\OaiXml\Enums;

use Malsoryz\OaiXml\Metadata\DublinCore;

enum Metadata: string 
{
    case DublinCore = 'oai_dc';

    public function serialize(array $data): array
    {
        return match ($this) {
            self::DublinCore => DublinCore::serialize($data),
        };
    }
}