<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use App\Models\Topic;
use App\Models\Enums\SubmissionStatus;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Record;
use Leconfe\OaiMetadata\Oai\Sets;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Http\Request;

class ListIdentifiers implements HasVerbAction
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
                $record = $newRecord->getHeader();
                $records[] = $record;
            } catch (ExceptionCollection $exceptions) {
                foreach ($exceptions->getAllExceptions() as $exception) {
                    $this->errors->throw($exception);
                }
                break;
            }
        }

        return new OaiResponse([
            'header' => $records,
            ...($this->getResumptionToken() ? ['resumptionToken' => $this->getResumptionToken()] : []),
        ]);
    }
}
