<?php

namespace Malsoryz\OaiXml\Classes\OaiXml;

use App\Models\Submission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Malsoryz\OaiXml\Enums\Metadata as EnumMetadata;
use Malsoryz\OaiXml\Enums\Granularity;
use Malsoryz\OaiXml\Enums\Verb;

class GetRecord
{
    protected array $record = [];
    protected string $urlHost;
    protected ?Submission $paper;

    protected const IDENTIFIER_PREFIX = 'paper';

    public function __construct(
        Submission|string|int $submission, 
        Request $request
    ) {
        $metadata = $request->query(Verb::QUERY_METADATA_PREFIX);
        $this->urlHost = parse_url($request->url(), PHP_URL_HOST);

        if ($submission instanceof Submission) {
            $this->paper = $submission->load([
                'proceeding', 
                'track', 
                'media', 
                'meta', 
                'galleys.file.media', 
                'authors' => fn($query) => $query->with([
                    'role', 'meta'
                    ])
                ]);
        } else {
            $this->paper = Submission::query()
                ->where('id', $submission)
                ->with(['proceeding', 'track', 'media', 'meta', 'galleys.file.media', 'authors' => fn($query) => $query->with(['role', 'meta'])])
                ->first();
        }

        if (! $this->paper) {
            abort(404);
            return;
        }

        $this->record = [
            'header' => [
                'identifier' => $this->createIdentifier($this->paper->id),
                'datestamp' => Granularity::Second->format($this->paper->updated_at),
            ],
            'metadata' => EnumMetadata::from($metadata)->serialize([
                'title' => $this->paper->getLocalizedMeta('title'),
                'date' => Granularity::Second->format($this->paper->published_at),
                'creator' => $this->paper->authors->pluck('fullname')->toArray(),
                'identifier' => [
                    route('livewirePageGroup.conference.pages.paper', [
                        'conference' => $this->paper->conference,
                        'submission' => $this->paper->id,
                    ]),
                    $this->paper->doi?->doi,
                ],
                'subject' => $this->paper->getMeta('keywords'),
                'source' => $this->paper->proceeding->seriesTitle(),
                'description' => $this->paper->getLocalizedMeta('abstract'),
                'relation' => route('livewirePageGroup.conference.pages.paper', [
                    'conference' => $this->paper->conference,
                    'submission' => $this->paper->id,
                ]),
                'language' => 'eng',
            ]),
        ];
    }

    public function get(): array
    {
        return $this->record;
    }

    public function createIdentifier(int|string $id): string
    {
        return 'oai:'.$this->urlHost.':'.self::IDENTIFIER_PREFIX.'/'.$id;
    }
}