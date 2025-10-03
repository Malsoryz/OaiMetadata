<?php

namespace Leconfe\OaiMetadata\Oai\Query\Verb;

use App\Models\Conference;
use App\Models\Topic;
use App\Models\Enums\SubmissionStatus;

use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Record;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Sets;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListRecords implements HasVerbAction
{
    use VerbHandler;

    public function handle(Request $request, Repository $repository, Verb $verb): OaiResponse
    {
        $conference = $request->route('conference');

        $submissions = $this->getRecords();

        $records = [];
        foreach ($submissions as $paper) {
            try {
                $newRecord = new Record($paper, $this->getMetadataFormat(), $repository);
                $record = $newRecord->getRecord();
                $records[] = $record;
            } catch (ExceptionCollection $exceptions) {
                foreach ($exceptions->getAllExceptions() as $exception) {
                    $this->errors->throw($exception);
                }
                break;
            }
        }

        // dd(Cache::get($this->getResumptionToken()));

        return new OaiResponse([
            'record' => $records,
            ...($this->getResumptionToken() ? ['resumptionToken' => $this->getResumptionToken()] : []),
        ]);
    }
}