<?php

namespace Leconfe\OaiMetadata\Contracts\Oai;

use Leconfe\OaiMetadata\Oai\OaiXml;
use Leconfe\OaiMetadata\Oai\Wrapper\Response as OaiResponse;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Illuminate\Http\Request;

interface HasVerbAction
{
    public static function handle(Request $request, Repository $repository, Verb $verb): OaiResponse|OaiError|array;
}