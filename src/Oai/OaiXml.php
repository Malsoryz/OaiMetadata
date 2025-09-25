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

use DOMDocument;

class OaiXml
{
    protected Request $request;
    protected Conference $conference;
    protected Carbon $responseDate;

    protected array $processingInstruction = [];
    protected array $rootElement = [];
    protected array $handledVerb = [];

    protected Xml $xml;
    protected array $errors = [];
    protected array $requestAttributes = [];

    public function __construct(
        Request $request,
        Conference $conference,
    )
    {
        $this->conference = $conference;
        $this->request = $request;

        $this->rootElement = $this->rootElement();
    }

    public function convert(
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        array $domProperties = ['formatOutput' => true],
        bool | null $xmlStandalone = null,
        bool $addXmlDeclaration = true,
        array | null $options = ['convertNullToXsiNil' => false, 'convertBoolToString' => false]
    ): DOMDocument
    {
        $xml = new Xml(
            array: [
                'responseDate' => $this->responseDate->format('Y-m-d\TH:i:s\Z'),
                'request' => [
                    '_value' => $this->request->url(),
                    '_attributes' => $this->requestAttributes
                ],
                ...(count($this->errors) >= 1 ? ['error' => $this->errors] : $this->handledVerb),
            ],
            rootElement: $this->rootElement,
            replaceSpacesByUnderScoresInKeyNames: $replaceSpacesByUnderScoresInKeyNames,
            xmlEncoding: 'UTF-8',
            xmlVersion: '1.0',
            domProperties: $domProperties,
            xmlStandalone: $xmlStandalone,
            addXmlDeclaration: $addXmlDeclaration,
            options: $options
        );

        $this->addPI($this->processingInstruction);

        foreach ($this->processingInstruction as $name => $attrs) {
            $xml->addProcessingInstruction($name, $attrs);
        }

        return $xml->toDom();
    }

    public function handle(Carbon $responseDate): static
    {
        $this->responseDate = $responseDate;

        $response = ErrorCodes::check($this->request);

        if ($response instanceof Verb) {
            $getResponse = $response->getClass()::handleVerb($this->request, $this);
            return $getResponse;
        } else {
            $this->errors = $response;
            return $this;
        }

    }

    public function addPI(array $instruction): static
    {
        foreach ($instruction as $name => $attributes) {
            if (!is_array($attributes)) {
                continue;
            }

            $pi = [];
            foreach ($attributes as $attr => $value) {
                $pi[] = "{$attr}=\"{$value}\"";
            }

            $this->processingInstruction[$name] = implode(' ', $pi);
        }

        return $this;
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

    public function setRequestAttributes(array $attributes): static
    {
        $this->requestAttributes = $attributes;
        return $this;
    }

    public function pushError(array $error): static
    {
        $this->errors[] = $error;
        return $this;
    }

    public function setHandledVerb(array $handled): static
    {
        $this->handledVerb = $handled;
        return $this;
    }

    ////////////////////////////////////////////////////////////
    ////////////////////////////// unused //////////////////////

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