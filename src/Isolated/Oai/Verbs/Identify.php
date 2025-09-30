<?php

namespace Leconfe\OaiMetadata\Isolated\Oai\Verbs;

use Leconfe\OaiMetadata\Isolated\Interface\ResponsableVerb;

class Identify implements ResponsableVerb
{
    public readonly string $name;

    public function __construct()
    {
        $this->name = 'Anyone';
    }

    public function response()
    {
        return $this;
    }
}