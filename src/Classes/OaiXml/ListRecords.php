<?php

namespace OaiMetadataFormat\Classes\OaiXml;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use OaiMetadataFormat\Metadata\Metadata;

class ListRecords
{
    protected array $record = [];

    public function __construct(
        array $header,
        // array $data,
        // Metadata|string|null $metadata = null,
    ) {
        $this->record = ['record' => []];

        if (Arr::get($header, 'identifier')) {
            Arr::set($this->record, 'record.header.identifier', $header['identifier']);
        } else throw new InvalidArgumentException("\$header[] must have 'identifier'");
        
        if (Arr::get($header, 'datestamp')) {
            $getDatestamp = $header['datestamp'] instanceof Carbon
                ? $header['datestamp']
                : Carbon::parse($header['datestamp']);

            Arr::set($this->record, 'record.header.datestamp', $getDatestamp->format('Y-m-d'));
        } else throw new InvalidArgumentException("\$header[] must have 'datestamp'");
        
        if (Arr::get($header, 'setSpec')) {
            $getSetSpec = is_array($header['setSpec'])
                ? Arr::mapWithKeys($header['setSpec'], fn ($item) => ['_value' => $item])
                : $header['setSpec'];

            Arr::set($this->record, 'record.header.setSpec', $getSetSpec);
        };

        // Arr::set($this->record, 'record.metadata', $metadata::handle($data));
    }

    public function getRecord(): array
    {
        return $this->record;
    }
}