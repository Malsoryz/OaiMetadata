<?php

namespace OaiMetadataFormat\Metadata;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

abstract class Metadata
{
    protected static string $metadataPrefix;
    protected static string $schema;
    protected static string $metadataNamespace;

    public static abstract function getMetadataFormat(): array;

    public static function makeRecordHeader(
        string $identifier,
        Carbon $datestamp,
        array|string $setSpecs = [],
    ): array
    {
        $getSetSpecs = Collection::wrap($setSpecs)
            ->map(function ($spec) {
                return ['_value' => $spec];
            });

        return [
            'header' => [
                'identifier' => $identifier,
                'datestamp' => $datestamp->format('Y-m-d'),
                ...($getSetSpecs->isNotEmpty()
                    ? ['setSpec' => $getSetSpecs]
                    : []
                )
            ],
        ];
    }

    // public static function makeRecord(): array
    // {

    // }
}