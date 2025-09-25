<?php

namespace Malsoryz\OaiXml\Oai\Query\Verb;

use App\Models\Conference;
use App\Models\Enums\SubmissionStatus;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

use Malsoryz\OaiXml\Oai\Metadata\Metadata as EnumMetadata;
use Malsoryz\OaiXml\Oai\Query\Verb\GetRecord;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

use Malsoryz\OaiXml\Oai\Response as VerbResponse;

use Malsoryz\OaiXml\Oai\Query\Verb;

use Malsoryz\OaiXml\Oai\OaiXml;

class ListRecords implements HasVerbAction
{
    protected Request $request;
    protected Collection $submissions;

    protected const IDENTIFIER_RECORD_PREFIX = 'paper';

    public function __construct(
        Request $request,
        Conference $conference,
    ) {
        $this->request = $request;
        $this->submissions = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->get();
    }

    public function getRecords(): array
    {
        $records = [];

        foreach ($this->submissions as $submission) {
            $newRecord = new GetRecord($submission, $this->request);
            $records['record'][] = $newRecord->get();
        }

        return $records;
    }

    public static function handleVerb(Request $request, OaiXml $oaixml): OaiXml
    {
        $verb = Verb::ListRecords;
        $getAllowedQuery = $verb->allowedQuery();

        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        return new VerbResponse([
            $verb->value => [],
        ], $attributes);
    }
}