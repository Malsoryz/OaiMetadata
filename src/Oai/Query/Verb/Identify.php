<?php

namespace Malsoryz\OaiXml\Oai\Query\Verb;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Query\Verb;
use Malsoryz\OaiXml\Oai\Response as VerbResponse;
use Malsoryz\OaiXml\Oai\OaiXml;

class Identify implements HasVerbAction
{
    public static function handleVerb(OaiXml $oaixml): OaiXml
    {
        $request = $oaixml->getRequest();
        $verb = $oaixml->getCurrentVerb();
        $getAllowedQuery = $verb->allowedQuery();

        $repository = $oaixml->getRepository();

        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        // dd($repository);

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb([$verb->value => $repository->getRepositoryInfo()]);
    }
}
