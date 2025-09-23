<?php

namespace Malsoryz\OaiXml\Metadata;

use Malsoryz\OaiXml\Metadata\Metadata;
use Malsoryz\OaiXml\Enums\Metadata\DublinCore as DCEnum;

class DublinCore extends Metadata
{
    protected static string $prefix = 'dc';

    protected static string $metadataPrefix = 'oai_dc';
    protected static string $schema = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    protected static string $metadataNamespace = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

    public static function getMetadataFormat(): array
    {
        return [
            'metadataPrefix' => self::$metadataPrefix,
            'schema' => self::$schema,
            'metadataNamespace' => self::$metadataNamespace,
        ];
    }

    public static function getMetadataAttributes(): array
    {
        return [
            'xmlns:' . self::$metadataPrefix => self::$metadataNamespace,
            'xmlns:' . self::$prefix => 'http://purl.org/dc/elements/1.1/',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => self::$metadataNamespace.' '.self::$schema,
        ];
    }

    public static function serialize(array $data): array
    {
        $metadataRootElement = self::$metadataPrefix.':'.self::$prefix;

        $record = [];

        $orders = DCEnum::getElementOrder();

        foreach ($orders as $order) {
            if (array_key_exists($order, $data)) {
                [$name, $dataValue] = DCEnum::make($order, $data[$order]);
                $record[$metadataRootElement][$name] = $dataValue;
            }
        }

        $record[$metadataRootElement]['_attributes'] = self::getMetadataAttributes();

        return $record;
    }
}