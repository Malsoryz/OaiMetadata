<?php

namespace Malsoryz\OaiXml\Oai\Query\verb;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Response as VerbResponse;
use Malsoryz\OaiXml\Oai\Query\Verb;
use Malsoryz\OaiXml\Oai\OaiXml;

use Malsoryz\OaiXml\Oai\Metadata\Metadata;

class ListMetadataFormats implements HasVerbAction
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

        $lists = [$verb->value => Metadata::getListMetadata()];

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb($lists);
    }
}
