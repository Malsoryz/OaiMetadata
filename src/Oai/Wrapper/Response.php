<?php

namespace Leconfe\OaiMetadata\Oai\Wrapper;

class Response
{
    protected array $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function __toArray()
    {
        return $this->response;
    }

    public function toArray(): array
    {
        return $this->response;
    }
}