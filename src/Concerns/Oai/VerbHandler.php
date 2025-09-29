<?php

namespace Leconfe\OaiMetadata\Concerns\Oai;

use Leconfe\OaiMetadata\Oai\OaiXml;
use Leconfe\OaiMetadata\Oai\ErrorCodes;

use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;

use Illuminate\Http\Request;

trait VerbHandler
{
    public static function handleVerb(OaiXml $oaixml): OaiXml
    {
        $request = $oaixml->getRequest();
        $verb = $oaixml->getCurrentVerb();
        $repository = $oaixml->getRepository();

        // handle this
        $response = self::handle($request, $repository, $verb);

        $result = match (true) {
            $response instanceof OaiResponse => [
                $verb->value => $response->toArray()
            ],
            $response instanceof OaiError => [
                'error' => $response->toArray()
            ],
            is_array($response) => array_map(function ($item) {
                if (! $item instanceof OaiError) {
                    throw new \UnexpectedValueException("Return array items expected to be instance of ".OaiError::class);
                }

                return $item->toArray();
            }, $response),
        };

        $attributes = [];
        foreach ($verb->allowedQuery() as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        if (method_exists(self::class, 'checkMetadata')) {
            $isError = self::checkMetadata($request);
            if ($isError instanceof OaiError) {
                $result = ['error' => $isError->toArray()];
            }
        }

        return $oaixml
            ->setRequestAttributes($attributes)
            ->setHandledVerb($result);
    }
}