<?php

namespace Malsoryz\OaiXml\Oai\Query\verb;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Response as VerbResponse;
use Malsoryz\OaiXml\Oai\Query\Verb;
use Malsoryz\OaiXml\Oai\OaiXml;

class ListIdentifiers implements HasVerbAction
{
    public static function handleVerb(Request $request, OaiXml $oaixml): OaiXml
    {
        $verb = Verb::ListIdentifiers;
        $getAllowedQuery = $verb->allowedQuery();

        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        return new VerbResponse([
            $verb->value => [],
        ], $attributes);
    }
}
