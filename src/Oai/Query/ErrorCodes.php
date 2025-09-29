<?php

namespace Leconfe\OaiMetadata\Oai\Query;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Classes\ExceptionCollection;

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
        $errors = new ExceptionCollection(OaiError::class);

        $queries = self::retrieveUrlQuery($request);

        if ($request->query(Verb::QUERY_VERB) === static::MISSING_ARGUMENT) {
            $errors->throw(new OaiError(
                __('OaiMetadata::error.verb.missing'),
                static::BAD_VERB
            ));
        } else {
            // jika verb illegal
            if (! Verb::tryFrom($request->query(Verb::QUERY_VERB)) && $request->query(Verb::QUERY_VERB) !== static::REPEATED_ARGUMENT) {
                $errors->throw(new OaiError(
                    __('OaiMetadata::error.verb.illegal', ['hint' => $request->query(Verb::QUERY_VERB)]),
                    static::BAD_VERB
                ));
            }
        }

        foreach ($queries as $query => $value) {
            if ($value === static::REPEATED_ARGUMENT) {
                $errors->throw(new OaiError(
                    __('OaiMetadata::error.argument.repeated', ['hint' => $query]),
                    static::BAD_VERB
                ));
            }
        }

        // Jika verb legal atau valid
        if ($verb = Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
            $requiredQueries = $verb->requiredQuery();
            foreach ($requiredQueries as $query) {
                if (! $request->query($query)) {
                    $errors->throw(new OaiError(
                        __('OaiMetadata::error.argument.missing', ['hint' => $query]),
                        static::BAD_ARGUMENT,
                    ));
                }
            }

            $allowedQueries = $verb->allowedQuery();
            foreach (array_keys($request->query()) as $query) {
                if (! in_array($query, $allowedQueries)) {
                    $errors->throw(new OaiError(
                        __('OaiMetadata::error.argument.illegal', ['hint' => $query]),
                        static::BAD_ARGUMENT
                    ));
                }
            }
        }

        if ($errors->hasExceptions()) {
            throw $errors;
        }

        return Verb::from($request->query('verb'));
    }

    // hanya untuk mengecek argument jika didefinisikan dua kali
    // karena kalau menggunakan $request saja hasilnya akan mengambil
    // query terakhir
    private static function retrieveUrlQuery(Request $request): Collection
    {
        $rawQuery = parse_url($request->server('REQUEST_URI'), PHP_URL_QUERY);
        $data = Str::of($rawQuery)->explode('&');
        
        if ($data->isEmpty()) {
            return $data->filter();
        }

        return $data->filter()
            ->mapToGroups(function ($item) {
                [$key, $value] = explode('=', $item, 2);
                return [$key => $value];
            })
            ->map(function ($item) {
                return $item->count() > 1 
                    ? static::REPEATED_ARGUMENT 
                    : $item->first();
            });
    }
}