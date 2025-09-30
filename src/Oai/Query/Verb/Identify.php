<?php

namespace Leconfe\OaiMetadata\Oai\Query\Verb;

use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Contracts\Oai\HasVerbAction;
use Leconfe\OaiMetadata\Concerns\Oai\VerbHandler;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Http\Request;

class Identify implements HasVerbAction
{
    use VerbHandler;

    public function handle(Request $request, Repository $repository, Verb $verb): OaiResponse
    {
        // throw new ExceptionCollection(OaiError::class, [
        //     new OaiError('pesan', 'badVerb'),
        // ]);

        return new OaiResponse($repository->getRepositoryInfo());
    }
}
