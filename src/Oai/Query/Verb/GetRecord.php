<?php

namespace Malsoryz\OaiXml\Oai\Query\Verb;

use App\Models\Submission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Metadata\Metadata as EnumMetadata;
use Malsoryz\OaiXml\Oai\Identifier\Granularity;
use Malsoryz\OaiXml\Oai\Query\Verb;

use Malsoryz\OaiXml\Oai\Response as VerbResponse;

use Malsoryz\OaiXml\Concerns\Oai\HasVerbAction;

class GetRecord implements HasVerbAction
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
            'metadata' => EnumMetadata::from($metadata)->serialize($this->paper),
        ];
    }

    public static function handleVerb(Request $request): VerbResponse
    {
        $verb = Verb::GetRecord;
        $getAllowedQuery = $verb->allowedQuery();

        $urlHost = parse_url($request->url(), PHP_URL_HOST);

        $getSubmissionId = self::matchTemplate('oai:'.$urlHost.':'.self::IDENTIFIER_PREFIX.'/{id}', $request->query(verb::QUERY_IDENTIFIER));

        // dd($getSubmissionId, 'oai:'.$urlHost.':'.self::IDENTIFIER_PREFIX.'/{id}', $request->query(verb::QUERY_IDENTIFIER));

        $getID = (int) $getSubmissionId['id'];
        
        $attributes = [];
        foreach ($getAllowedQuery as $query) {
            if (array_key_exists($query, $request->query())) {
                $attributes[$query] = $request->query($query);
            }
        }

        if (! is_int($getID)) {
            throw new \Exception("ID is not valid");
        }

        $getRecord = new GetRecord((int) $getID, $request);

        $response = [
            $verb->value => $getRecord->getRecord(),
        ];

        return new VerbResponse($response, $attributes);
    }

    public function getRecord(): array
    {
        return ['record' => $this->record];
    }

    public function createIdentifier(int|string $id): string
    {
        return 'oai:'.$this->urlHost.':'.self::IDENTIFIER_PREFIX.'/'.$id;
    }

    private static function matchTemplate(string $template, string $input): ?array
    {
        // cari semua placeholder {name}
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $template, $placeholders);

        // ganti placeholder dengan named capture group
        $pattern = preg_replace(
            '/\{([a-zA-Z0-9_]+)\}/',
            '(?P<$1>.+?)',
            preg_quote($template, '#')
        );

        // balikkan escaped \{name\} ke regex capture
        $pattern = preg_replace(
            '/\\\\\{([a-zA-Z0-9_]+)\\\\\}/',
            '(?P<$1>.+?)',
            $pattern
        );

        // bungkus jadi regex utuh
        $pattern = '#^' . $pattern . '$#';

        // cocokkan
        if (preg_match($pattern, $input, $matches)) {
            $result = [];
            foreach ($placeholders[1] as $name) {
                $result[$name] = $matches[$name] ?? null;
            }
            return $result;
        }

        return null;
    }
}