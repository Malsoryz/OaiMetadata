<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Enums\Verb;
use Leconfe\OaiMetadata\Isolated\Classes\ExceptionBag;
use Leconfe\OaiMetadata\Isolated\Classes\Exception as OaiException;
use Illuminate\Http\Request as SymfonyRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Request
{
    protected Oai $repository;
    protected ?Verb $verb;
    protected Collection $query;
    protected ExceptionBag $exceptions;

    public function __construct(Oai $repository)
    {
        $this->repository = $repository;
        $this->exceptions = new ExceptionBag();

        $filteredQuery = $this->filterRequestQuery($repository->getRequest());
        $this->query = $filteredQuery;
        
        $verb = $this->checkVerb($repository->getRequest());
        $this->verb = $verb;

        if ($verb && (! $this->exceptions->hasExceptions())) {
            $this->checkArgument($verb);
        }
    }

    public function handleRequest()
    {

    }

    public function checkVerb(SymfonyRequest $request): ?Verb
    {
        $strVerb = $request->query('verb');
        $verbs = $this->query->get('verb');

        if (is_null($strVerb)) {
            $this->exceptions->throw(new OaiException('Missing Verb', 'badVerb'));
        }

        if (is_array($verbs)) {
            foreach ($verbs as $verb) {
                if (is_null(Verb::tryFrom($verb)) && !  is_null($verb)) {
                    $this->exceptions->throw(new OaiException("Invalid Verb {$verb}", 'badVerb'));
                }
            }
        } elseif (is_string($verbs)) {
            if (is_null(Verb::tryFrom($verbs))) {
                $this->exceptions->throw(new OaiException("Invalid Verb {$verbs}", 'badVerb'));
            }
        }

        if (is_array($verbs)) {
            $this->exceptions->throw(new OaiException('Repeated Verb', 'badVerb'));
        }

        return Verb::tryFrom($strVerb);
    }

    public function checkArgument(Verb $verb)
    {
        $query = clone $this->query;

        $requiredArgs = $verb->requiredArguments();
        $allowedArgs = $verb->allowedArguments();

        foreach ($requiredArgs as $arg) {
            if (! $query->has($arg)) {
                $this->exceptions->throw(new OaiException("Missing argument {$arg}", 'badArgument'));
            }
        }

        foreach ($query->keys() as $arg) {
            if (! collect($allowedArgs)->contains($arg)) {
                $this->exceptions->throw(new OaiException("Illegal argument {$arg}", 'badArgument'));
            }
        }

        $repeatedArgs = $query->filter(fn ($item) => is_array($item));

        if ($repeatedArgs->isNotEmpty()) {
            foreach ($repeatedArgs->keys() as $hint) {
                $this->exceptions->throw(new OaiException("repeated {$hint}", 'badArgument'));
            }
        }
    }

    public function filterRequestQuery(SymfonyRequest $request)
    {
        $rawUrlQuery = parse_url($request->server('REQUEST_URI'), PHP_URL_QUERY);
        $queries = Str::of($rawUrlQuery)->explode('&');

        if ($queries->isEmpty()) {
            return $queries->filter();
        }

        return $queries->filter()
            ->mapToGroups(function ($item) {
                $exploded = collect(explode('=', $item, 2));
                if (count($exploded) === 1) return [$exploded[0] => null];

                $key = $exploded[0];
                $value = empty($exploded[1]) ? null : $exploded[1];
                return [$key => $value];
            })
            ->map(fn ($item) => $item->count() > 1 ? $item->toArray() : $item->first());
    }

    public function getRepository(): Oai
    {
        return $this->repository;
    }

    public function getCurrentVerb(): ?Verb
    {
        return $this->verb;
    }

    public function getQuery(): Collection
    {
        return $this->query;
    }

    public function getResponseQuery(): array
    {
        $allowedArgs = $this->getCurrentVerb()?->allowedArguments();
        $queries = $this->getQuery();

        if (is_null($allowedArgs)) {
            return [];
        }

        return $queries->filter(function ($value, $key) use ($allowedArgs) {
            return collect($allowedArgs)->contains($key) && ! is_array($value);
        })->toArray();
    }

    public function getExceptions(): ExceptionBag
    {
        return $this->exceptions;
    }
}