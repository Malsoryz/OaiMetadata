<?php

namespace Leconfe\OaiMetadata\Oai\Query\Verb;

use App\Models\Submission;

use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Record;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class GetRecord implements HasVerbAction
{
    use VerbHandler;

    public function handle(Request $request, Repository $repository, Verb $verb): OaiResponse
    {
        $paper = $repository->parseIdentifier($request->route('conference'), $request->query(Verb::QUERY_IDENTIFIER));

        if (is_null($paper)) {
            return new OaiError(
                __('OaiMetadata::error.record.id-doesnt-exist', ['hint' => $request->query('identifier')]),
                ErrorCodes::ID_DOES_NOT_EXIST,
            );
        }

        $newRecord = null;

        try {
            $newRecord = new Record($paper, $this->getMetadataFormat(), $repository);
        } catch (ExceptionCollection $exceptions) {
            foreach ($exceptions->getAllExceptions() as $exception) {
                $this->errors->throw($exception);
            }
        }
        
        $record = $newRecord?->getRecord();
        return new OaiResponse(['record' => $record]);
    }
}