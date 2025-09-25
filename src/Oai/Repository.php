<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Conference;
use App\Models\Enums\SubmissionStatus;
use Leconfe\OaiMetadata\Oai\Identifier\Granularity;

class Repository
{
    protected string $name;
    protected string $baseUrl;
    protected string $protocolVersion;
    protected string $earliestDatestamp;
    protected string $deletedRecordPolicy;
    protected Granularity $granularity;
    protected string $adminEmail;

    public function __construct(
        string $name,
        Conference $conference,
        string $baseUrl,
        Granularity $granularity,
        string $protocolVersion = '2.0',
        string $deletedRecordPolicy = 'persistent',
        string $adminEmail = '',
    )
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->protocolVersion = $protocolVersion;
        $this->deletedRecordPolicy = $deletedRecordPolicy;
        $this->granularity = $granularity;
        $this->adminEmail = $adminEmail;

        $this->earliestDatestamp = $this->getEarlistDatestamp($conference);
    }

    public function getEarlistDatestamp(Conference $conference): string
    {
        $date = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->orderBy('published_at')
            ->first()->published_at;
        return $this->granularity->format($date);
    }

    public function getRepositoryInfo(): array
    {
        return [
            'repositoryName' => $this->name,
            'baseURL' => $this->baseUrl,
            'protocolVersion' => $this->protocolVersion,
            'adminEmail' => $this->adminEmail,
            'earliestDatestamp' => $this->earliestDatestamp,
            'deletedRecord' => $this->deletedRecordPolicy,
            'granularity' => $this->granularity->value,
        ];
    }
}