<?php

namespace Malsoryz\OaiXml\Oai\Metadata;

use App\Models\Submission;
use Malsoryz\OaiXml\Oai\Metadata\DublinCore;

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