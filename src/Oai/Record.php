<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Submission;

use Leconfe\OaiMetadata\Oai\Metadata\Metadata as EnumMetadata;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Repository;
use Leconfe\OaiMetadata\Oai\Sets;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;

use Illuminate\Http\Request;

class Record
{
    protected ?Submission $paper;
    protected Repository $repository;

    public function __construct(
        Submission|string|int $paper, 
        Request $request,
        Repository $repository
    ) {
        $this->paper = $paper instanceof Submission ? $paper : Submission::find($paper);
        $this->request = $request;
        $this->repository = $repository;
    }

    public function getRecord(): OaiError|array
    {
        $metadata = $this->getMetadata();

        if ($metadata instanceof OaiError) {
            return $metadata;
        }

        return [
            'header' => $this->getHeader(),
            'metadata' => $metadata,
        ];
    }

    public function getHeader(): OaiError|array
    {
        $isError = EnumMetadata::checkMetadataFormat($this->request->query(Verb::QUERY_METADATA_PREFIX));

        if ($isError instanceof OaiError) {
            return $isError;
        }

        return [
            'identifier' => $this->repository->createIdentifier($this->paper),
            'datestamp' => $this->repository->getGranularity()->format($this->paper->updated_at),
            'setSpec' => Sets::makeSet($this->paper),
        ];
    }

    public function getMetadata(): OaiError|array
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

        $result = EnumMetadata::checkMetadataFormat($this->request->query(Verb::QUERY_METADATA_PREFIX));

        if ($result instanceof OaiError) {
            return $result;
        }

        return $result->getClass()::serialize($this->paper);
    }
}