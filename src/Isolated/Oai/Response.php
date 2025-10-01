<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Oai\Request as OaiRequest;
use Leconfe\OaiMetadata\Isolated\Classes\Exception as OaiException;
use Leconfe\OaiMetadata\Isolated\Classes\ExceptionBag;
use Leconfe\OaiMetadata\Isolated\Interface\ResponsableVerb;
use Illuminate\Support\Arr;

use Leconfe\OaiMetadata\Isolated\Oai\Responses\Exception as ExceptionResponse;

class Response
{
    protected OaiRequest $requestSource;
    protected array $query;
    protected ExceptionBag $exceptions;
    protected ResponsableVerb $response;

    public function __construct(OaiRequest $request)
    {
        $this->requestSource = $request;
        $this->query = $request->getResponseQuery();
        
        $this->exceptions = $request->getExceptions();

        if ($this->exceptions->hasExceptions()) {
            $this->response = new ExceptionResponse($request);
        }
    }
}