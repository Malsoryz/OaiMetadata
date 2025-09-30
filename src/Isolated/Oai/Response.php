<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Classes\Error as OaiError;
use Leconfe\OaiMetadata\Isolated\Interface\ResponsableVerb;
use Illuminate\Support\Arr;

class Response
{
    protected Oai $request;
    protected array $errors;
    protected ResponsableVerb $response;

    public function __construct(OaiRequest $request)
    {
        $this->request = $request;
        $this->errors = [];
        $this->response = (new \Leconfe\OaiMetadata\Isolated\Oai\Verbs\Identify())->response();
    }

    public function withErrors(OaiError ...$errors): static
    {
        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
        return $this;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}