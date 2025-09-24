<?php

namespace Malsoryz\OaiXml\Oai\Query\Verb;

use App\Models\Conference;
use App\Models\Enums\SubmissionStatus;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

use Malsoryz\OaiXml\Oai\Metadata\Metadata as EnumMetadata;
use Malsoryz\OaiXml\Oai\Query\Verb\GetRecord;

class ListRecords
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
}