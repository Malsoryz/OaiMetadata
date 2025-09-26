<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Conference;
use App\Models\Enums\SubmissionStatus;
use Illuminate\Http\Request;
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
        Request $request,
        Granularity|string $granularity = 'YYYY-MM-DDThh:mm:ssZ',
        string $protocolVersion = '2.0',
        string $deletedRecordPolicy = 'no',
    )
    {
        $conference = $request->route('conference');

        $this->name = $conference->name;
        $this->baseUrl = $request->url();
        $this->protocolVersion = $protocolVersion;
        $this->deletedRecordPolicy = $deletedRecordPolicy;
        $this->granularity = $granularity instanceof Granularity ? $granularity : Granularity::from($granularity);
        $this->adminEmail = $conference->conferenceUsers()->role('Admin')->first()->email;

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