<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use Leconfe\OaiMetadata\Concerns\Oai\HasVerbAction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $response = [];

        if ($conference = $oaixml->getConference()) {
            $response['set'][] = [
                'setSpec' => $conference->path,
                'setName' => $conference->name,
            ];

            foreach ($conference->topics->pluck('name') as $topic) {
                $response['set'][] = [
                    'setSpec' => $conference->path.':'.Str::of($topic)->lower()->kebab()->toString(),
                    'setName' => $topic,
                ];
            }
        }

        if (array_key_exists('set', $response)) {
            if (! count($response['set']) >= 1) {
                unset($response['set']);
                $response['error'] = [
                    '_value' => __('OaiMetadata::error.set.not-supported'),
                    '_attributes' => [
                        'code' => ErrorCodes::NO_SET_HIERARCHY,
                    ],
                ];
            }
        } else {
            $response['error'] = [
                '_value' => __('OaiMetadata::error.set.not-supported'),
                '_attributes' => [
                    'code' => ErrorCodes::NO_SET_HIERARCHY,
                ],
            ];
        }

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb([$verb->value => $response]);
    }
}
