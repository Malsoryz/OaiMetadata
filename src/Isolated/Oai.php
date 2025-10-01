<?php

namespace Leconfe\OaiMetadata\Isolated;

use App\Models\Conference;
use App\Models\Enums\SubmissionStatus;
use Leconfe\OaiMetadata\Isolated\Oai\Identifier as OaiIdentifier;
use Leconfe\OaiMetadata\Isolated\Oai\Request as OaiRequest;
use Leconfe\OaiMetadata\Isolated\Oai\Response as OaiResponse;
use Leconfe\OaiMetadata\Isolated\Classes\Error as OaiError;
use Leconfe\OaiMetadata\Isolated\Enums\Granularity;
use Illuminate\Http\Request;

class Oai
{
    protected Request $request;
    protected Conference $baseModel;
    protected string $protocolVersion;
    protected string $deletedRecordPolicy;
    protected Granularity $granularity;

    protected OaiIdentifier $oaiIdentifier;

    public function __construct(
        Request $request, 
        Granularity|string $granularity = Granularity::Second, 
        ?string $adminEmail = null,
        string $scheme = 'oai',
        string $delimiter = ':',
        string $recordPrefix = 'paper'
    ) {
        $this->request = $request;

        $this->baseModel = $request->route('conference');
        $this->protocolVersion = '2.0';
        $this->deletedRecordPolicy = 'no';
        $this->granularity = $granularity instanceof Granularity ? $granularity : Granularity::from($granularity);;
        $this->oaiIdentifier = new OaiIdentifier($request, $scheme, $delimiter, $recordPrefix);
    }

    public function handle()
    {
        return new OaiRequest($this);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getBaseModel(): Conference
    {
        return $this->baseModel;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function getDeletedRecordPolicy()
    {
        return $this->deletedRecordPolicy;
    }

    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }

    public function getOaiIdentifier(): OaiIdentifier
    {
        return $this->oaiIdentifier;
    }
}