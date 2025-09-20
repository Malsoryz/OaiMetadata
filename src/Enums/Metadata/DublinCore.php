<?php

namespace OaiMetadataFormat\Enums\Metadata;

use Illuminate\Support\Collection;

enum DublinCore: string {
    case Contributor = 'contributor';
    case Coverage = 'coverage';
    case Creator = 'creator';
    case Date = 'date';
    case Description = 'description';
    case Format = 'format';
    case Identifier = 'identifier';
    case Language = 'language';
    case Publisher = 'publisher';
    case Relation = 'relation';
    case Rights = 'rights';
    case Source = 'source';
    case Subject = 'subject';
    case Title = 'title';
    case Type = 'type';

    public const PREFIX = 'dc';

    public static function make(DublinCore|string $element, array|string $value): array
    {
        $dcElement = $element instanceof DublinCore ? $element : DublinCore::from($element);

        $data = Collection::wrap(is_array($value) ? $value : [$value])
            ->map(function (string $item) {
                return ['_value' => $item];
            })->toArray();

        return [
            self::PREFIX.':'.$dcElement->value => $data,
        ];
    }
}