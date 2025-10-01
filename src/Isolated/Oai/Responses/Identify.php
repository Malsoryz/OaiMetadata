<?php

namespace Leconfe\OaiMetadata\Isolated\Oai\Responses;

use App\Models\Enums\SubmissionStatus;
use Leconfe\OaiMetadata\Isolated\Oai;
use Leconfe\OaiMetadata\Isolated\Interface\Responsable;
use Leconfe\OaiMetadata\Isolated\Oai\Identifier as OaiIdentifier;

class Identify implements Responsable
{
    public readonly string $repositoryName;
    public readonly string $baseUrl;
    public readonly string $protocolVersion;
    public readonly string $earliestDatestamp;
    public readonly string $deletedRecordPolicy;
    public readonly string $granularity;
    public readonly string $adminEmail;

    public readonly OaiIdentifier $oaiIdentifier;

    public function __construct(Oai $repository, array $query)
    {
        $baseModel = $repository->getBaseModel();
        $request = $repository->getRequest();

        $this->repositoryName = $baseModel->name;
        $this->baseUrl = $request->url();
        $this->protocolVersion = $repository->getProtocolVersion();
        $this->earliestDatestamp = $this->getEarliestDatestamp($repository);
        $this->deletedRecordPolicy = $repository->getDeletedRecordPolicy();
        $this->granularity = $repository->getGranularity()->value;
        $this->adminEmail = $this->getAdminEmail($repository);

        $this->oaiIdentifier = $repository->getOaiIdentifier();
    }

    public static function handle(Oai $repository, array $query): static
    {
        return new static($repository, $query);
    }

    public function getEarliestDatestamp(Oai $repository): string
    {
        $earliestRecord = $repository->getBaseModel()->submission()
            ->where('status', SubmissionStatus::Published)
            ->orderBy('updated_at')
            ->first();
        return $repository->getGranularity()->format($earliestRecord->updated_at);
    }

    public function getAdminEmail(Oai $repository): string
    {
        return $repository->getBaseModel()->conferenceUsers()->role('admin')->first()->email;
    }
}