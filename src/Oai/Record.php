<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Submission;

use Leconfe\OaiMetadata\Oai\Metadata\Metadata as EnumMetadata;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Sets;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;

use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Illuminate\Http\Request;

class Record
{
    protected ?Submission $paper;
    protected Repository $repository;
    protected EnumMetadata|string $metadataFormat;

    public function __construct(
        Submission|string|int $paper, 
        Request $request,
        Repository $repository
    ) {
        $this->paper = $paper instanceof Submission ? $paper : Submission::find($paper);
        $this->request = $request;
        $this->repository = $repository;
        
        if ($metadata = EnumMetadata::tryFrom($request->query(Verb::QUERY_METADATA_PREFIX))) {
            $this->metadataFormat = $metadata;
        } else {
            throw new ExceptionCollection(OaiError::class, [new OaiError(
                __('OaiMetadata::error.metadata.cannot-disseminate', ['hint' => $request->query(Verb::QUERY_METADATA_PREFIX)]),
                ErrorCodes::CANNOT_DISSEMINATE_FORMAT
            )]);
        }
    }

    public function getRecord(): array
    {
        return [
            'header' => $this->getHeader(),
            'metadata' => $this->getMetadata(),
        ];
    }

    public function getHeader(): array
    {
        return [
            'identifier' => $this->repository->createIdentifier($this->paper),
            'datestamp' => $this->repository->getGranularity()->format($this->paper->updated_at),
            'setSpec' => Sets::makeSet($this->paper),
        ];
    }

    public function getMetadata(): array
    {
        $this->paper->load([
            'proceeding', 
            'track', 
            'media', 
            'meta', 
            'galleys.file.media', 
            'authors' => fn($query) => $query->with([
                'role', 'meta'
            ])
        ]);

        $result = $this->metadataFormat;

        return $result->getClass()::serialize($this->paper);
    }
}