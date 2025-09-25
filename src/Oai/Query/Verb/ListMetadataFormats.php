<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use Leconfe\OaiMetadata\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Response as VerbResponse;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\OaiXml;

use Leconfe\OaiMetadata\Oai\Metadata\Metadata;

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
