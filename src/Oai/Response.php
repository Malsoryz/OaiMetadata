<?php

namespace Malsoryz\OaiXml\Oai;

class Response
{
    public array $handledVerb;
    public array $requestAttributes;

    public function __construct(array $handledVerb, array $requestAttributes)
    {
        $this->handledVerb = $handledVerb;
        $this->requestAttributes = $requestAttributes;
    }
}