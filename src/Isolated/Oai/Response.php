<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Oai\Request as OaiRequest;
use Leconfe\OaiMetadata\Isolated\Classes\Exception as OaiException;
use Leconfe\OaiMetadata\Isolated\Classes\ExceptionBag;
use Leconfe\OaiMetadata\Isolated\Enums\Verb;
use Leconfe\OaiMetadata\Isolated\Interface\Responsable;
use Illuminate\Support\Arr;

use Leconfe\OaiMetadata\Isolated\Oai\Responses\Exception as ExceptionResponse;

class Response
{
    protected Oai $repository;
    protected array $query;
    protected ExceptionBag $exceptions;
    protected ?Verb $verb;
    protected Responsable|ExceptionResponse $response;

    public function __construct(OaiRequest $request)
    {
        $this->repository = $request->getRepository();
        $this->query = $request->getResponseQuery();
        
        $this->exceptions = $request->getExceptions();
        $this->verb = $request->getCurrentVerb();

        if ($this->exceptions->hasExceptions()) {
            $this->response = new ExceptionResponse($request->getExceptions());
        } else {
            $handlerClass = $request->getCurrentVerb()
                ->getHandlerClass()::handle($this->repository, $this->query);
            $this->response = $handlerClass;
        }
    }
}