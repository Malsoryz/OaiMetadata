<?php

namespace Leconfe\OaiMetadata\Oai\Metadata;

use App\Models\Submission;
use Leconfe\OaiMetadata\Oai\Identifier\Granularity;

use Leconfe\OaiMetadata\Contracts\Oai\HasMetadata;

use Illuminate\Support\Str;

enum DublinCore: string implements HasMetadata
{
    case Title = 'title';
    case Creator = 'creator';
    case Contributor = 'contributor';
    case Subject = 'subject';
    case Description = 'description';
    case Publisher = 'publisher';
    case Date = 'date';
    case Type = 'type';
    case Format = 'format';
    case Identifier = 'identifier';
    case Source = 'source';
    case Language = 'language';
    case Relation = 'relation';
    case Coverage = 'coverage';
    case Rights = 'rights';

    public const METADATA_PREFIX = 'oai_dc';
    public const ELEMENT_PREFIX = 'dc';
    public const SCHEMA_LOCATION = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    public const METADATA_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

    public static function make(DublinCore|string $element, array|string $values): array
    {
        $dcElement = $element instanceof DublinCore ? $element : DublinCore::from($element);

        $data = [];

        if (is_array($values)) {
            foreach (array_filter($values, fn ($item) => ! is_null($item)) as $value) {
                $data[]['_value'] = $value;
            }
            return [self::ELEMENT_PREFIX.':'.$dcElement->value, $data];
        }

        return [self::ELEMENT_PREFIX.':'.$dcElement->value, $values];
    }

    public static function getMetadataFormat(): array
    {
        return [
            'metadataPrefix' => self::METADATA_PREFIX,
            'schema' => self::SCHEMA_LOCATION,
            'metadataNamespace' => self::METADATA_NAMESPACE,
        ];
    }

    public static function getMetadataAttributes(): array
    {
        return [
            'xmlns:' . self::METADATA_PREFIX => self::METADATA_NAMESPACE,
            'xmlns:' . self::ELEMENT_PREFIX => 'http://purl.org/dc/elements/1.1/',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => self::METADATA_NAMESPACE.' '.self::SCHEMA_LOCATION,
        ];
    }

    public static function getElementOrder(): array
    {
        return [
            self::Title->value,
            self::Creator->value,
            self::Contributor->value,
            self::Subject->value,
            self::Description->value,
            self::Publisher->value,
            self::Date->value,
            self::Type->value,
            self::Format->value,
            self::Identifier->value,
            self::Source->value,
            self::Language->value,
            self::Relation->value,
            self::Coverage->value,
            self::Rights->value,
        ];
    }

    public static function serialize(Submission $paper): array
    {
        $metadataRootElement = self::METADATA_PREFIX.':'.self::ELEMENT_PREFIX;

        $data = [
            'title' => $paper->getLocalizedMeta('title'),
            'date' => Granularity::Second->format($paper->published_at),
            'creator' => $paper->authors->pluck('fullname')->toArray(),
            'format' => 'application/pdf',
            'type' => 'Text',
            'identifier' => [
                route('livewirePageGroup.conference.pages.paper', [
                    'conference' => $paper->conference,
                    'submission' => $paper->id,
                ]),
                $paper->doi?->doi,
            ],
            'subject' => $paper->getMeta('keywords'),
            'source' => $paper->proceeding->seriesTitle().'; '.$paper->getMeta('article_pages'),
            'description' => Str::of($paper->getLocalizedMeta('abstract'))->stripTags(),
            'relation' => route('livewirePageGroup.conference.pages.paper', [
                'conference' => $paper->conference,
                'submission' => $paper->id,
            ]),
            'language' => array_keys($paper->getMeta('abstract')),
        ];

        return static::makeElement($data);
    }

    public static function makeElement(array $data): array
    {
        $metadataRootElement = self::METADATA_PREFIX.':'.self::ELEMENT_PREFIX;
        $newElement = [];

        $orders = static::getElementOrder();

        foreach ($orders as $order) {
            if (array_key_exists($order, $data)) {
                [$name, $dataValue] = static::make($order, $data[$order]);
                $newElement[$metadataRootElement][$name] = $dataValue;
            }
        }

        $newElement[$metadataRootElement]['_attributes'] = self::getMetadataAttributes();

        return $newElement;
    }
}