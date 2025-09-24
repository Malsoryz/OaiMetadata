<?php

namespace Malsoryz\OaiXml\Oai\Query;

use Malsoryz\OaiXml\Oai\Query\verb\Identify;
use Malsoryz\OaiXml\Oai\Query\Verb\GetRecord;
use Malsoryz\OaiXml\Oai\Query\verb\ListRecords;
use Malsoryz\OaiXml\Oai\Query\verb\ListSets;
use Malsoryz\OaiXml\Oai\Query\verb\ListMetadataFormats;
use Malsoryz\OaiXml\Oai\Query\verb\ListIdentifiers;

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

    public function getClass(): string
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