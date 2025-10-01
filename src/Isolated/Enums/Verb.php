<?php

namespace Leconfe\OaiMetadata\Isolated\Enums;

use Leconfe\OaiMetadata\Isolated\Oai\Responses\Identify;
use Leconfe\OaiMetadata\Isolated\Oai\Responses\GetRecord;
use Leconfe\OaiMetadata\Isolated\Oai\Responses\ListRecords;
use Leconfe\OaiMetadata\Isolated\Oai\Responses\ListSets;
use Leconfe\OaiMetadata\Isolated\Oai\Responses\ListMetadataFormats;
use Leconfe\OaiMetadata\Isolated\Oai\Responses\ListIdentifiers;

enum Verb: string 
{
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

    public function getHandlerClass(): string
    {
        return match ($this) {
            self::Identify => Identify::class,
            self::GetRecord => GetRecord::class,
            self::ListRecords => ListRecords::class,
            self::ListSets => ListSets::class,
            self::ListMetadataFormats => ListMetadataFormats::class,
            self::ListIdentifiers => ListIdentifiers::class,
        };
    }

    public function requiredArguments(): array
    {
        return match ($this) {
            self::Identify, self::ListSets, self::ListMetadataFormats => [
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
        };
    }

    public function allowedArguments(): array
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
            self::ListSets => [
                self::QUERY_VERB,
                self::QUERY_RESUMPTION_TOKEN,
            ],
            self::ListMetadataFormats => [
                self::QUERY_VERB,
                self::QUERY_RESUMPTION_TOKEN,
                self::QUERY_IDENTIFIER,
            ],
        };
    }
}