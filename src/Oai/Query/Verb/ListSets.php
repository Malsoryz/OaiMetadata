<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;

use Illuminate\Support\Str;
use Leconfe\OaiMetadata\Oai\OaiXml;

use Leconfe\OaiMetadata\Oai\Sets;

use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;

use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;

class ListSets implements HasVerbAction
{
    use VerbHandler;

    public function handle(Request $request, Repository $repository, Verb $verb): OaiResponse|OaiError|array
    {
        $response = Sets::makeListSets($request->route('conference'));

        if ($response instanceof OaiError) {
            return $response;
        }

        return new OaiResponse(['set' => $response]);
    }
}
