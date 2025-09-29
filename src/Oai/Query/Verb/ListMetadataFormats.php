<?php

namespace Leconfe\OaiMetadata\Oai\Query\verb;

use Leconfe\OaiMetadata\Oai\Metadata\Metadata;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;

use Illuminate\Http\Request;

class ListMetadataFormats implements HasVerbAction
{
    use VerbHandler;

    public static function handle(Request $request, Repository $repository, Verb $verb): OaiResponse|OaiError|array
    {
        return new OaiResponse(Metadata::getListMetadata());
    }
}
