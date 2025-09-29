<?php

namespace Leconfe\OaiMetadata\Oai\Metadata;

use App\Models\Submission;
use Leconfe\OaiMetadata\Oai\Metadata\DublinCore;

use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;

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

    public static function checkMetadataFormat(string $metadataFormat): OaiError|static
    {
        $getMetadataFormat = static::tryFrom($metadataFormat);

        if (is_null($getMetadataFormat)) {
            return new OaiError(
                __('OaiMetadata::error.metadata.cannot-disseminate', ['hint' => $metadataFormat]),
                ErrorCodes::CANNOT_DISSEMINATE_FORMAT
            );
        }

        return $getMetadataFormat;
    }
}