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
use Leconfe\OaiMetadata\Concerns\Oai\MetadataPrefixChecker;

use Illuminate\Http\Request;

class ListRecords implements HasVerbAction
{
    use VerbHandler, MetadataPrefixChecker;

    public function handle(Request $request, Repository $repository, Verb $verb): OaiResponse|OaiError|array
    {
        $conference = $request->route('conference');

        if ($request->query(Verb::QUERY_SET)) {
            $set = Sets::parseSet($conference, $request->query('set'));
    
            if (! $set) {
                return new OaiError(
                    __('OaiMetadata::error.record.no-match.set', ['hint' => $request->query('set')]),
                    ErrorCodes::NO_RECORD_MATCH
                );
            }
        }

        $submissions = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->when($request->query(Verb::QUERY_SET), function ($query) use ($conference, $request) {
                $topic = Sets::parseSet($conference, $request->query('set'));
                return $query->whereHas('topics', fn ($topicQuery) => $topicQuery->whereKey($topic));
            })
            ->get();

        $records = [];
        foreach ($submissions as $paper) {
            $newRecord = new Record($paper, $request, $repository);
            $record = $newRecord->getRecord();

            if ($record instanceof OaiError) {
                return $record;
            }

            $records[] = $record;
        }

        return new OaiResponse(['record' => $records]);
    }
}