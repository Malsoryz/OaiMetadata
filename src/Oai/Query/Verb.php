<?php

namespace Leconfe\OaiMetadata\Oai\Query;

use Leconfe\OaiMetadata\Oai\Query\Verb\Identify;
use Leconfe\OaiMetadata\Oai\Query\Verb\GetRecord;
use Leconfe\OaiMetadata\Oai\Query\Verb\ListRecords;
use Leconfe\OaiMetadata\Oai\Query\Verb\ListSets;
use Leconfe\OaiMetadata\Oai\Query\Verb\ListMetadataFormats;
use Leconfe\OaiMetadata\Oai\Query\Verb\ListIdentifiers;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public function requiredQuery(Request $request): array
    {
        $resumptionToken = $request->query(self::QUERY_RESUMPTION_TOKEN);
        $queries = [
            self::QUERY_VERB,
            is_null($resumptionToken) ? self::QUERY_METADATA_PREFIX : self::QUERY_RESUMPTION_TOKEN,
        ];

        return match ($this) {
            self::Identify => [
                self::QUERY_VERB,
            ],
            self::GetRecord => [
                self::QUERY_VERB,
                self::QUERY_IDENTIFIER,
                self::QUERY_METADATA_PREFIX,
            ],
            self::ListRecords, self::ListIdentifiers => $queries,
            self::ListSets, self::ListMetadataFormats => [
                self::QUERY_VERB,
            ],
        };
    }

    public function allowedQuery(Request $request): array
    {
        $resumptionToken = $request->query(self::QUERY_RESUMPTION_TOKEN);
        $queries = [
            self::QUERY_VERB,
            ...(is_null($resumptionToken) ? [
                self::QUERY_METADATA_PREFIX,
                self::QUERY_FROM,
                self::QUERY_UNTIL,
                self::QUERY_SET,
            ] : [
                self::QUERY_RESUMPTION_TOKEN,
            ]),
        ];

        return match ($this) {
            self::Identify => [
                self::QUERY_VERB,
            ],
            self::GetRecord => [
                self::QUERY_VERB,
                self::QUERY_IDENTIFIER,
                self::QUERY_METADATA_PREFIX,
            ],
            self::ListRecords, self::ListIdentifiers => $queries,
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