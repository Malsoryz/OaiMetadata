<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use App\Models\Enums\SubmissionStatus;
use Leconfe\OaiMetadata\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Response as VerbResponse;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Query\Verb\GetRecord;
use Leconfe\OaiMetadata\Oai\OaiXml;

class ListIdentifiers implements HasVerbAction
{
    public static function handleVerb(OaiXml $oaixml): OaiXml
    {
        $submissions = $oaixml->getConference()
            ->submission()
            ->where('status', SubmissionStatus::Published)->get();

        $request = $oaixml->getRequest();
        $verb = $oaixml->getCurrentVerb();
        $getAllowedQuery = $verb->allowedQuery();

        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        $records = [];

        foreach ($submissions as $paper) {
            $newRecord = new GetRecord($paper, $request);
            $records[] = $newRecord->getRecord()['header'];
        }

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb([$verb->value => [
                'header' => $records,
            ]]);
    }
}
