<?php

namespace Leconfe\OaiMetadata\Classes;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Query\Verb as VerbEnum;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Verb
{
    public static function getFromRequest(Request $request): VerbEnum
    {
        $errors = new ExceptionCollection(OaiError::class);
        $queries = self::retrieveUrlQuery($request);

        if ($request->query(VerbEnum::QUERY_VERB) === ErrorCodes::MISSING_ARGUMENT) {
            $errors->throw(new OaiError(
                __('OaiMetadata::error.verb.missing'),
                ErrorCodes::BAD_VERB
            ));
        } else {
            // jika verb illegal
            if (! VerbEnum::tryFrom($request->query(VerbEnum::QUERY_VERB)) && $request->query(VerbEnum::QUERY_VERB) !== ErrorCodes::REPEATED_ARGUMENT) {
                $errors->throw(new OaiError(
                    __('OaiMetadata::error.verb.illegal', ['hint' => $request->query(VerbEnum::QUERY_VERB)]),
                    ErrorCodes::BAD_VERB
                ));
            }
        }

        if ($request->query(VerbEnum::QUERY_VERB)) {
            if (is_array($queries['verb'])) {
                $errors->throw(new OaiError(
                    __('OaiMetadata::error.argument.repeated', ['hint' => 'verb']),
                    ErrorCodes::BAD_VERB
                ));
            }
        }

        // // Jika verb legal atau valid
        // if ($verb = VerbEnum::tryFrom($request->query(VerbEnum::QUERY_VERB))) {
        //     $requiredQueries = $verb->requiredQuery();
        //     foreach ($requiredQueries as $query) {
        //         if (! $request->query($query)) {
        //             $errors->throw(new OaiError(
        //                 __('OaiMetadata::error.argument.missing', ['hint' => $query]),
        //                 ErrorCodes::BAD_ARGUMENT,
        //             ));
        //         }
        //     }

        //     $allowedQueries = $verb->allowedQuery();
        //     foreach (array_keys($request->query()) as $query) {
        //         if (! in_array($query, $allowedQueries)) {
        //             $errors->throw(new OaiError(
        //                 __('OaiMetadata::error.argument.illegal', ['hint' => $query]),
        //                 ErrorCodes::BAD_ARGUMENT
        //             ));
        //         }
        //     }
        // }

        if ($errors->hasExceptions()) {
            throw $errors;
        }

        return VerbEnum::from($request->query('verb'));
    }

    private static function retrieveUrlQuery(Request $request): Collection
    {
        $rawQuery = parse_url($request->server('REQUEST_URI'), PHP_URL_QUERY);
        $data = Str::of($rawQuery)->explode('&');
        
        if ($data->isEmpty()) {
            return $data->filter();
        }

        return $data->filter()
            ->mapToGroups(function ($item) {
                $exploded = explode('=', $item, 2);
                if (count($exploded) <= 1) {
                    $name = $exploded[0];
                    return [$name => $name];
                }
                [$key, $value] = $exploded;
                return [$key => $value];
            })
            ->map(function ($item) {
                return $item->count() > 1 
                    ? $item->toArray()
                    : $item->first();
            });
    }
}