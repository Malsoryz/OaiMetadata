<?php

namespace Malsoryz\OaiXml\Enums\Metadata;

enum DublinCore: string 
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

    public const PREFIX = 'dc';

    public static function make(DublinCore|string $element, array|string $values): array
    {
        $dcElement = $element instanceof DublinCore ? $element : DublinCore::from($element);

        $data = [];

        if (is_array($values)) {
            foreach ($values as $value) {
                $data[]['_value'] = $value;
            }
            return [self::PREFIX.':'.$dcElement->value, $data];
        }

        return [self::PREFIX.':'.$dcElement->value, $values];
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
}