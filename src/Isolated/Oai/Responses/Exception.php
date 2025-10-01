<?php

namespace Leconfe\OaiMetadata\Isolated\Oai\Responses;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Oai\Request as OaiRequest;
use Leconfe\OaiMetadata\Isolated\Classes\ExceptionBag;

class Exception
{
    protected ExceptionBag $errors;

    public function __construct(ExceptionBag $errors)
    {
        $this->errors = $errors;
    }
}