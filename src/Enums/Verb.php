<?php

namespace OaiMetadataFormat\Enums;

enum Verb: string {
    case Identify = 'Identify';
    case GetRecord = 'GetRecord';
    case ListRecords = 'ListRecords';
    case ListSets = 'ListSets';
    case ListMetadataFormats = 'ListMetadataFormats';
    case ListIdentifiers = 'ListIdentifiers';

    public const QUERY_VERB = 'verb';
    public const QUERY_IDENTIFIER = 'identifier';
    public const QUERY_METADATA_PREFIX = 'metadataPrefix';
    public const QUERY_RESUMPTION_TOKEN = 'resumptionToken';

    public const QUERY_FROM = 'from';
    public const QUERY_UNTIL = 'until';
    public const QUERY_SET = 'set';

    public function requiredQuery(): array
    {
        return match ($this) {
            self::Identify => [
                self::QUERY_VERB,
            ],
            self::GetRecord => [
                self::QUERY_VERB,
                self::QUERY_IDENTIFIER,
                self::QUERY_METADATA_PREFIX,
            ],
            self::ListRecords, self::ListIdentifiers => [
                self::QUERY_VERB,
                self::QUERY_METADATA_PREFIX,
            ],
            self::ListSets, self::ListMetadataFormats => [
                self::QUERY_VERB,
            ],
            default => [],
        };
    }

    public function allowedQuery(): array
    {
        return match ($this) {
            self::Identify => [
                self::QUERY_VERB,
            ],
            self::GetRecord => [
                self::QUERY_VERB,
                self::QUERY_IDENTIFIER,
                self::QUERY_METADATA_PREFIX,
            ],
            self::ListRecords, self::ListIdentifiers => [
                self::QUERY_VERB,
                self::QUERY_METADATA_PREFIX,
                self::QUERY_RESUMPTION_TOKEN,
                self::QUERY_FROM,
                self::QUERY_UNTIL,
                self::QUERY_SET,
            ],
            self::ListSets, self::ListMetadataFormats => [
                self::QUERY_VERB,
                self::QUERY_RESUMPTION_TOKEN,
            ],
            default => [],
        };
    }
}