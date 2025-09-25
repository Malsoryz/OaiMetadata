<?php

namespace Malsoryz\OaiXml\Concerns\Oai;

use Malsoryz\OaiXml\Oai\Response as VerbResponse;
use Malsoryz\OaiXml\Oai\OaiXml;

use Illuminate\Http\Request;

interface HasVerbAction
{
    public static function handleVerb(Request $request, OaiXml $oaixml): OaiXml;
}