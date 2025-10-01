<?php

namespace Leconfe\OaiMetadata\Isolated\Oai;

use Illuminate\Http\Request;

class Identifier
{
    public readonly string $scheme;
    public readonly string $repositoryIdentifier;
    public readonly string $delimiter;
    public readonly string $recordPrefix;
    public readonly string $sample;

    public function __construct(Request $request, string $scheme = 'oai', string $delimiter = ':', string $recordPrefix = 'paper')
    {
        $this->scheme = $scheme;
        $this->repositoryIdentifier = parse_url($request->url(), PHP_URL_HOST);
        $this->delimiter = $delimiter;
        $this->recordPrefix = $recordPrefix;
        $this->sample = $this->createIdentifier(12345);
    }

    public function createIdentifier(string|int $sample = 12345): string
    {
        return "{$this->scheme}{$this->delimiter}{$this->repositoryIdentifier}{$this->delimiter}{$this->recordPrefix}/{$sample}";
    }
}