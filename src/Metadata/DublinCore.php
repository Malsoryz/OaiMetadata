<?php

namespace OaiMetadataFormat\Metadata;

use OaiMetadataFormat\Metadata\Metadata;

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
}