<?php

namespace Malsoryz\OaiXml\Oai\Query\verb;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Response as VerbResponse;
use Malsoryz\OaiXml\Oai\Query\Verb;
use Malsoryz\OaiXml\Oai\OaiXml;

use Malsoryz\OaiXml\Oai\Sets;

use Malsoryz\OaiXml\Oai\Query\ErrorCodes;

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
                    '_value' => __('OaiXml::error.sets.missing'),
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
