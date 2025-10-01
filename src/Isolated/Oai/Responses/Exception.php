<?php

namespace Leconfe\OaiMetadata\Isolated\Oai\Responses;

use Leconfe\OaiMetadata\Isolated\Interface\ResponsableVerb;
use Leconfe\OaiMetadata\Isolated\Oai\Request as OaiRequest;
use Leconfe\OaiMetadata\Isolated\Classes\ExceptionBag;

class Exception implements ResponsableVerb
{
    protected ExceptionBag $errors;

    public function __construct(OaiRequest $source)
    {
        $this->errors = $source->getExceptions();
    }

    public function response()
    {
        return $this;
    }
}