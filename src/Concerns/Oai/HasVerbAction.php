<?php

namespace Leconfe\OaiMetadata\Concerns\Oai;

use Leconfe\OaiMetadata\Oai\Response as VerbResponse;
use Leconfe\OaiMetadata\Oai\OaiXml;

use Illuminate\Http\Request;

interface HasVerbAction
{
    public static function handleVerb(OaiXml $origin): OaiXml;
}