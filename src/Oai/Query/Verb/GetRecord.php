<?php

namespace Leconfe\OaiMetadata\Oai\Query\Verb;

use App\Models\Submission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Metadata\Metadata as EnumMetadata;
use Leconfe\OaiMetadata\Oai\Identifier\Granularity;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Leconfe\OaiMetadata\Oai\Response as VerbResponse;
use Leconfe\OaiMetadata\Oai\OaiXml;

use Leconfe\OaiMetadata\Concerns\Oai\HasVerbAction;

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
            'metadata' => EnumMetadata::from($metadata)->getClass()::serialize($this->paper),
        ];
    }

        public function getRecord(): array
    {
        return $this->record;
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

    //////////////////////////////////////////////////////////

    public static function handleVerb(OaiXml $origin): OaiXml
    {
        $verb = $origin->getCurrentVerb();
        $request = $origin->getRequest();

        $getAllowedQuery = $verb->allowedQuery();

        $urlHost = parse_url($request->url(), PHP_URL_HOST);

        $getSubmissionId = self::matchTemplate('oai:'.$urlHost.':'.self::IDENTIFIER_PREFIX.'/{id}', $request->query(verb::QUERY_IDENTIFIER));

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

        $getRecord = new GetRecord($getID, $request);

        $newOai = $origin;
        $newOai->setRequestAttributes($attributes)
            ->setHandledVerb([
                $verb->value => [
                    'record' => $getRecord->getRecord()
                ],
            ]);

        return $newOai;
    }
}