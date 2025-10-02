<?php

namespace Leconfe\OaiMetadata\Isolated\Classes;

use App\Models\Submission;
use Leconfe\OaiMetadata\Isolated\Oai\Identifier as OaiIdentifier;
use Leconfe\OaiMetadata\Isolated\Oai\Enums\Granularity;

class Identifier
{
    protected string $identifier;
    protected string $datestamp;
    protected array $setSpecs = [];

    public function __construct(
        Submission $paper, 
        OaiIdentifier $oaiIdentifier, 
        Granularity $granularity = Granularity::Second
    ) {
        // add validator of identifier
        $this->identifier = $oaiIdentifier->createIdentifier($paper->id);

        $this->datestamp = $granularity->format($paper->updated_at);
        $this->setSpecs = $setSpecs;
    }
}