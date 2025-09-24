<?php

namespace Malsoryz\OaiXml\Enums;

use App\Models\Submission;
use Malsoryz\OaiXml\Enums\Metadata\DublinCore;

enum Metadata: string 
{
    case DublinCore = 'oai_dc';

    public function getClass()
    {
        return match ($this) {
            self::DublinCore => DublinCore::class,
        };
    }

    public function serialize(Submission $paper): array
    {
        return match ($this) {
            self::DublinCore => DublinCore::serialize($paper),
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