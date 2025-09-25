<?php

namespace Leconfe\OaiMetadata\Oai;

use Leconfe\OaiMetadata\Oai\Metadata\DublinCore;

enum Sets: string 
{
    case Paper = 'paper';

    public function getData(): array
    {
        return match ($this) {
            self::Paper => [
                'setSpec' => 'ppr',
                'setName' => 'Published Leconfe Submission',
                'setDescription' => [
                    'oai_dc:dc' => [
                        '_attributes' => DublinCore::getMetadataAttributes(),
                        'dc:description' => 'Jurnal Conference Leconfe Terpublikasi',
                    ],
                ]
            ],
        };
    }

    public static function getListSets(): array
    {
        return [
            'set' => array_map(function ($case) {
                return $case->getData();
            }, static::cases()),
        ];
    }
}