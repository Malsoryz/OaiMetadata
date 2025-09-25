<?php

namespace Malsoryz\OaiXml\Concerns\Oai;

use Malsoryz\OaiXml\Oai\Response as VerbResponse;

use Illuminate\Http\Request;

interface HasVerbAction
{
    public static function handleVerb(Request $request): VerbResponse;
}