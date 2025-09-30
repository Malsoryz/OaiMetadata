<?php

namespace Leconfe\OaiMetadata\Concerns\Oai;

use App\Models\Enums\SubmissionStatus;
use App\Models\Submission;

use Leconfe\OaiMetadata\Oai\OaiXml;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Repository;

use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Sets;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait VerbHandler
{
    protected ExceptionCollection $errors;
    protected Collection $records;
    protected Submission $record;

    public function __construct()
    {
        $this->errors = new ExceptionCollection(OaiError::class);
    }

    public function getRecords()
    {
        return $this->records;
    }

    public function handleVerb(OaiXml $oaixml): OaiXml
    {
        $request = $oaixml->getRequest();
        $verb = $oaixml->getCurrentVerb();
        $repository = $oaixml->getRepository();

        // $allowedQuery = collect($verb->allowedQuery());

        $this->checkArguments($request, $verb);

        if ($verb === Verb::ListRecords || $verb === Verb::ListIdentifiers) {
            $this->records = $this->checkListRecords($request, $repository);
        }

        // handle this
        $response = $this->handle($request, $repository, $verb);

        if ($this->errors->hasExceptions()) {
            throw $this->errors;
        }
        
        $attributes = [];
        foreach ($verb->allowedQuery() as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb([$verb->value => $response->toArray()]);
    }

    public function checkArguments(Request $request, Verb $verb)
    {
        $requiredQueries = $verb->requiredQuery();
        foreach ($requiredQueries as $query) {
            if (! $request->query($query)) {
                $this->errors->throw(new OaiError(
                    __('OaiMetadata::error.argument.missing', ['hint' => $query]),
                    ErrorCodes::BAD_ARGUMENT
                ));
            }
        }

        $allowedQueries = $verb->allowedQuery();
        foreach (array_keys($request->query()) as $query) {
            if (! in_array($query, $allowedQueries)) {
                $this->errors->throw(new OaiError(
                    __('OaiMetadata::error.argument.illegal', ['hint' => $query]),
                    ErrorCodes::BAD_ARGUMENT
                ));
            }
        }
    }

    // check 'set', 'from' and 'until'
    public function checkListRecords(Request $request, Repository $repository): Collection
    {
        $conference = $request->route('conference');
        $granularity = $repository->getGranularity()->getFormat();
        
        $set = $request->query(Verb::QUERY_SET);
        $from = $request->query(Verb::QUERY_FROM);
        $until = $request->query(Verb::QUERY_UNTIL);
        
        $submissions = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->when($set, function ($query, $setQuery) use ($conference, $request) {
                $topics = Sets::parseSet($conference, $setQuery);
                // dd($topics);
                if (is_null($topics)) {
                    $this->errors->throw(new OaiError(
                        __('OaiMetadata::error.record.no-match.set', ['hint' => $request->query('set')]),
                        ErrorCodes::NO_RECORD_MATCH
                    ));
                    return $query;
                }

                if (is_string($topics)) return $query;

                return $query->whereHas('topics', fn ($topicQuery) => $topicQuery->whereKey($topics));
            })
            ->when($from, function ($query, $fromQuery) use ($granularity) {
                try {
                    $date = Carbon::createFromFormat($granularity, $fromQuery);
                    return $query->where('updated_at', '>=', $date);
                } catch (\Throwable $th) {
                    $this->errors->throw(new OaiError(
                        'Invalid argument',
                        ErrorCodes::BAD_ARGUMENT
                    ));
                }
            })
            ->when($until, function ($query, $untilQuery) use ($granularity) {
                try {
                    $date = Carbon::createFromFormat($granularity, $untilQuery);
                    return $query->where('updated_at', '<=', $date);
                } catch (\Throwable $th) {
                    $this->errors->throw(new OaiError(
                        'Invalid argument',
                        ErrorCodes::BAD_ARGUMENT
                    ));
                }
            });

        if ($submissions->count() === 0) {
            $this->errors->throw(new OaiError(
                'Kombinasi Query tidak menghasilkan record apapun',
                ErrorCodes::NO_RECORD_MATCH
            ));
        }

        return $submissions->get();
    }
}