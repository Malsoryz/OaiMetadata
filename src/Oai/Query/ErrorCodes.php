<?php

namespace Leconfe\OaiMetadata\Oai\Query;

use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;

class ErrorCodes 
{
    public const BAD_ARGUMENT = 'badArgument';
    public const BAD_RESUMPTION_TOKEN = 'badResumptionToken';
    public const BAD_VERB = 'badVerb';
    public const CANNOT_DISSEMINATE_FORMAT = 'cannotDisseminateFormat';
    public const ID_DOES_NOT_EXIST = 'idDoesNotExist';
    public const NO_RECORD_MATCH = 'noRecordsMatch';
    public const NO_METADATA_FORMAT = 'noMetadataFormats';
    public const NO_SET_HIERARCHY = 'noSetHierarchy';

    // using hash
    public const REPEATED_ARGUMENT = '$2y$10$zPnmsan2nIDN7IJ6BTH4Deg3ZcUgDrxXzHjP315qxZgE53/aQ16Ca';
    public const MISSING_ARGUMENT = null;

    public static function allErrors(): array
    {
        return [
            self::BAD_ARGUMENT,
            self::BAD_RESUMPTION_TOKEN,
            self::BAD_VERB,
            self::CANNOT_DISSEMINATE_FORMAT,
            self::ID_DOES_NOT_EXIST,
            self::NO_RECORD_MATCH,
            self::NO_METADATA_FORMAT,
            self::NO_SET_HIERARCHY,
        ];
    }

    public static function check(Request $request): array|Verb
    {
        $errors = [];

        $queries = self::retrieveUrlQuery($request);

        if ($request->query(Verb::QUERY_VERB) === static::MISSING_ARGUMENT) {
            $errors[] = new OaiError(
                __('OaiMetadata::error.verb.missing'),
                static::BAD_VERB
            );
        } else {
            // jika verb illegal
            if (! Verb::tryFrom($request->query(Verb::QUERY_VERB)) && $request->query(Verb::QUERY_VERB) !== static::REPEATED_ARGUMENT) {
                $errors[] = new OaiError(
                    __('OaiMetadata::error.verb.illegal', ['hint' => $request->query(Verb::QUERY_VERB)]),
                    static::BAD_VERB
                );
            }
        }

        foreach ($queries as $query => $value) {
            if ($value === static::REPEATED_ARGUMENT) {
                $errors[] = new OaiError(
                    __('OaiMetadata::error.argument.repeated', ['hint' => $query]),
                    static::BAD_VERB
                );
            }
        }

        // Jika verb legal atau valid
        if ($verb = Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
            $requiredQueries = $verb->requiredQuery();
            foreach ($requiredQueries as $query) {
                if (! $request->query($query)) {
                    $errors[] = new OaiError(
                        __('OaiMetadata::error.argument.missing', ['hint' => $query]),
                        static::BAD_ARGUMENT,
                    );
                }
            }

            $allowedQueries = $verb->allowedQuery();
            foreach (array_keys($request->query()) as $query) {
                if (! in_array($query, $allowedQueries)) {
                    $errors[] = new OaiError(
                        __('OaiMetadata::error.argument.illegal', ['hint' => $query]),
                        static::BAD_ARGUMENT
                    );
                }
            }
        }

        return count($errors) >= 1 ? $errors : Verb::from($request->query('verb'));
    }

    // hanya untuk mengecek argument jika didefinisikan dua kali
    // karena kalau menggunakan $request saja hasilnya akan mengambil
    // query terakhir
    private static function retrieveUrlQuery(Request $request): array
    {
        $rawQuery = parse_url($request->server('REQUEST_URI'), PHP_URL_QUERY);

        if ($rawQuery === null) {
            return [];
        }

        $pairs = explode('&', $rawQuery);
        $result = [];

        foreach ($pairs as $query) {
            if (trim($query) === '') {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $query, 2), 2, null);
            $key = $key ?? '';
            $value = urldecode($value);

            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            } else {
                // Kalau ketemu key yang sama lebih dari sekali
                $result[$key] = static::REPEATED_ARGUMENT;
            }
        }

        return $result;
    }
}