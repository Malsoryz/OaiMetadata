<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Conference;
use App\Models\Submission;
use App\Models\Enums\SubmissionStatus;
use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Identifier\Granularity;
use Leconfe\OaiMetadata\Oai\Element;

class Repository
{
    protected string $name;
    protected string $baseUrl;
    protected string $protocolVersion;
    protected string $earliestDatestamp;
    protected string $deletedRecordPolicy;
    protected Granularity $granularity;
    protected string $adminEmail;

    protected array $descriptionsElement = [];

    public const RECORD_PREFIX = 'paper';

    public function __construct(
        Request $request,
        Granularity|string $granularity = 'YYYY-MM-DDThh:mm:ssZ',
        string $protocolVersion = '2.0',
        string $deletedRecordPolicy = 'no',
        array $extraDescriptionsElement = []
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

        $this->descriptionsElement[] = Element::oaiIdentifier([
            'repositoryIdentifier' => parse_url($this->baseUrl, PHP_URL_HOST),
        ]);
        foreach ($extraDescriptionsElement as $descriptionsElement) {
            $this->descriptionsElement[] = $descriptionsElement;
        }
    }

    public function getEarlistDatestamp(Conference $conference): string
    {
        $date = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->orderBy('published_at')
            ->first()->published_at;
        return $this->granularity->format($date);
    }

    public function createIdentifier(Submission $paper, array $modify = []): string
    {
        $scheme = $modify['scheme'] ?? 'oai';
        $host = $modify['repositoryIdentifier'] ?? parse_url($this->baseUrl, PHP_URL_HOST);
        $delimiter = $modify['delimiter'] ?? ':';
        $recordPrefix = self::RECORD_PREFIX;

        return "{$scheme}{$delimiter}{$host}{$delimiter}{$recordPrefix}/{$paper->id}";
    }

    public function parseIdentifier(Conference $conference, string $set): ?Submission
    {
        $submissions = $conference->submission()
            ->where('status', SubmissionStatus::Published)
            ->get()
            ->keyBy(fn ($item) => $this->createIdentifier($item));

        return $submissions[$set] ?? null;
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
            'description' => $this->descriptionsElement,
        ];
    }

    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }
}