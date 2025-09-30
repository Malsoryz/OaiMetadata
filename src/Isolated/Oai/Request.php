<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Enums\Verb;
use Illuminate\Http\Request as SymfonyRequest;

class Request
{
    protected Oai $oai;
    protected ?Verb $verb;
    protected array $query = [];

    public function __construct(Oai $oai)
    {
        $this->oai = $oai;
        $this->verb = Verb::tryFrom($oai->getRequest()->query('verb'));
        $queries = $oai->getRequest()->query();
        unset($queries['verb']);
        $this->query = $queries;
    }
}