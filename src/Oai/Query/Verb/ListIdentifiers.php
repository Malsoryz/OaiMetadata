<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use App\Models\Enums\SubmissionStatus;

use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Record;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;
use Leconfe\OaiMetadata\Concerns\Oai\MetadataPrefixChecker;

use Illuminate\Http\Request;

class ListIdentifiers implements HasVerbAction
{
    use VerbHandler, MetadataPrefixChecker;

    public static function handle(Request $request, Repository $repository, Verb $verb): OaiResponse|OaiError|array
    {
        $conference = $request->route('conference');
        $submissions = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->get();

        $records = [];

        foreach ($submissions as $paper) {
            $newRecord = new Record($paper, $request, $repository);
            $header = $newRecord->getHeader();

            if ($header instanceof OaiError) {
                return $header;
            }

            $records[] = $header;
        }

        return new OaiResponse(['header' => $records]);
    }
}
