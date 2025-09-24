<?php

namespace Malsoryz\OaiXml\Oai;

use App\Models\Conference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Malsoryz\OaiXml\Oai\Query\Verb\ListRecords;
use Malsoryz\OaiXml\Oai\Query\ErrorCodes;
use Malsoryz\OaiXml\Oai\Metadata\DublinCore as DCEnum;
use Malsoryz\OaiXml\Oai\Query\Verb;
use Malsoryz\OaiXml\Oai\Metadata\Metadata;
use Spatie\ArrayToXml\ArrayToXml as Xml;

class OaiXml
{
    protected Conference $conference;
    protected Xml $xml;
    protected array $errors = [];
    protected array $requestAttributes = [];
    protected array $handledVerb = [];

    public function __construct(
        Request $request,
        Conference $conference,
        Carbon $responseDate,
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        array $domProperties = ['formatOutput' => true],
        bool | null $xmlStandalone = null,
        bool $addXmlDeclaration = true,
        array | null $options = ['convertNullToXsiNil' => false, 'convertBoolToString' => false]
    )
    {
        $this->conference = $conference;

        $this->xml = new Xml(
            array: $this->handleResponse($request, $responseDate),
            rootElement: $this->rootElement(),
            replaceSpacesByUnderScoresInKeyNames: $replaceSpacesByUnderScoresInKeyNames,
            xmlEncoding: 'UTF-8',
            xmlVersion: '1.0',
            domProperties: $domProperties,
            xmlStandalone: $xmlStandalone,
            addXmlDeclaration: $addXmlDeclaration,
            options: $options
        );

        $this->addPI($this->getPI());
    }

    public function addPI(array $instruction): void
    {
        foreach ($instruction as $name => $attributes) {
            $pi = array_map(function ($attr, $value) {
                return "{$attr}=\"{$value}\"";
            }, array_keys($attributes), $attributes);

            $this->xml->addProcessingInstruction($name, implode(' ', $pi));
        }
    }

    public function getPI(): array
    {
        return [
            'xml-stylesheet' => [
                'type' => 'text/xsl',
                'href' => asset('plugin/oaixml/lib/xsl/oai2.xsl'),
            ],
        ];
    }

    public function getXml()
    {
        return $this->xml->toXml();
    }

    public function rootElement(): array
    {
        return [
            'rootElementName' => 'OAI-PMH',
            '_attributes' => [
                'xmlns' => 'http://www.openarchives.org/OAI/2.0/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd',
            ],
        ];
    }

    public function handleResponse(Request $request, Carbon $responseDate): array
    {
        $isError = ErrorCodes::check($request);
        $response = [
            'responseDate' => $responseDate->toIso8601ZuluString(),
            'request' => [
                '_value' => $request->url(),
            ],
        ];

        if (! $isError) {
            if ($verb = Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
                $response[$verb->value] = [];

                // request attributes
                $allowedQueries = $verb->allowedQuery();
                foreach ($allowedQueries as $query) {
                    if ($getQuery = $request->query($query)) {
                        $this->requestAttributes[$query] = $getQuery;
                    }
                }
                $response['request']['_attributes'] = $this->requestAttributes;

                // handle verb
                $response[$verb->value] = method_exists($this, $verb->value) ? $this->{$verb->value}($verb, $request) : [];
            }
        } else {
            $response['error'] = $isError;
        }

        return $response;
    }

    public function ListRecords(Verb $verb, Request $request): array
    {
        $records = new ListRecords($request, $this->conference);
        return $records->getRecords();
    }

    public function ListMetadataFormats(Verb $verb, Request $request): array
    {
        return Metadata::getListMetadata();
    }
}