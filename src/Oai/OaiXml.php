<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Conference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Leconfe\OaiMetadata\Oai\Query\Verb\ListRecords;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;
use Leconfe\OaiMetadata\Oai\Metadata\DublinCore as DCEnum;
use Leconfe\OaiMetadata\Oai\Query\Verb;
use Leconfe\OaiMetadata\Oai\Metadata\Metadata;
use Leconfe\OaiMetadata\Oai\Repository;
use Spatie\ArrayToXml\ArrayToXml as Xml;

use DOMDocument;

class OaiXml
{
    protected Request $request;
    protected Conference $conference;
    protected Carbon $responseDate;

    protected Repository $repository;

    protected Verb $currentVerb;

    protected array $processingInstruction = [];
    protected array $rootElement = [];
    protected array $handledVerb = [];

    protected array $errors = [];
    protected array $requestAttributes = [];

    public function __construct(
        Request $request,
        Conference $conference,
        Repository $repository
    )
    {
        $this->conference = $conference;
        $this->request = $request;
        $this->repository = $repository;

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
        $dataForXml = [
            'responseDate' => $this->responseDate->format('Y-m-d\TH:i:s\Z'),
            'request' => [
                '_value' => $this->request->url(),
                '_attributes' => $this->requestAttributes
            ],
        ];

        $responseBody = count($this->errors) >= 1 
            ? ['error' => $this->errors] 
            : $this->handledVerb;

        $dataForXml = array_merge($dataForXml, $responseBody);

        $xml = new Xml(
            array: $dataForXml,
            rootElement: $this->rootElement,
            replaceSpacesByUnderScoresInKeyNames: $replaceSpacesByUnderScoresInKeyNames,
            xmlEncoding: 'UTF-8',
            xmlVersion: '1.0',
            domProperties: $domProperties,
            xmlStandalone: $xmlStandalone,
            addXmlDeclaration: $addXmlDeclaration,
            options: $options
        );

        foreach ($this->processingInstruction as $name => $attributes) {
            $xml->addProcessingInstruction($name, $attributes);
        }

        return $xml->toDom();
    }

    public function handle(Carbon $responseDate): static
    {
        $this->responseDate = $responseDate;

        $response = ErrorCodes::check($this->request);

        if ($response instanceof Verb) {
            $this->currentVerb = $response;
            $getResponse = $response->getClass()::handleVerb($this);
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

            $this->processingInstruction[$name] = implode(' ', array_map(
                fn($key, $value) => "{$key}=\"{$value}\"",
                array_keys($attributes),
                $attributes
            ));
        }

        return $this;
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

    public function getConference(): Conference
    {
        return $this->conference;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getCurrentVerb(): Verb
    {
        return $this->currentVerb;
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
}
