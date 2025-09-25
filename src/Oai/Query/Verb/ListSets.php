<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use Leconfe\OaiMetadata\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Response as VerbResponse;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\OaiXml;

use Leconfe\OaiMetadata\Oai\Sets;

use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;

class ListSets implements HasVerbAction
{
    public static function handleVerb(OaiXml $oaixml): OaiXml
    {
        $request = $oaixml->getRequest();
        $verb = $oaixml->getCurrentVerb();
        $getAllowedQuery = $verb->allowedQuery();

        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        $listSets = Sets::getListSets();

        $response = [];

        if (! count($listSets) >= 1) {
            $response = [
                'error' => [
                    '_value' => __('OaiMetadata::error.sets.missing'),
                    '_attributes' => [
                        'code' => ErrorCodes::NO_SET_HIERARCHY,
                    ],
                ],
            ];
        } else {
            foreach ($listSets as $set) {
                $response['set'][] = $set;
            }
        }

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb([$verb->value => $response]);
    }
}
