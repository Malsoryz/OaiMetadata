<?php

namespace Leconfe\OaiMetadata\Oai\Metadata;

use App\Models\Submission;
use Leconfe\OaiMetadata\Oai\Metadata\DublinCore;

enum Metadata: string 
{
    case DublinCore = 'oai_dc';

    public function getClass()
    {
        return match ($this) {
            self::DublinCore => DublinCore::class,
        };
    }

    public static function getListMetadata(): array
    {
        return [
            'metadataFormat' => array_map(function ($case) {
                return $case->getClass()::getMetadataFormat();
            }, static::cases()),
        ];
    }
}