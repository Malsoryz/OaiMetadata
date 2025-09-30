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

use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;

use Leconfe\OaiMetadata\Oai\Element;
use Leconfe\OaiMetadata\Classes\ExceptionCollection;

use Leconfe\OaiMetadata\Classes\Verb as VerbClass;

use DOMDocument;

class OaiXml
{
    protected Request $request;
    protected Repository $repository;

    protected Verb $currentVerb;

    protected array $processingInstruction = [];
    protected array $rootElement = [];
    protected array $handledVerb = [];

    protected array $errors = [];
    protected array $requestAttributes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->makeRepository($request);
        $this->rootElement = Element::rootElement();
    }

    public function makeRepository(Request $request): void
    {
        $this->repository = new Repository($request);
    }

    public function convert(
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        array $domProperties = ['formatOutput' => true],
        bool | null $xmlStandalone = null,
        bool $addXmlDeclaration = true,
        array | null $options = ['convertNullToXsiNil' => false, 'convertBoolToString' => false]
    ): DOMDocument
    {
        $dataForXml = Element::mainElement($this);

        $responseBody = count($this->errors) > 0
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

    public function handle(): static
    {
        try {
            $response = VerbClass::getFromRequest($this->request);
            $this->currentVerb = $response;
            $getClass = new ($response->getClass());
            $getResponse = $getClass->handleVerb($this);
            return $getResponse;
        } catch (ExceptionCollection $exceptions) {
            foreach ($exceptions->getAllExceptions() as $exception) {
                $this->errors[] = $exception->toArray();
            }
        }

        return $this;
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

    public function getRequestAttributes(): array
    {
        return $this->requestAttributes;
    }

    public function setRequestAttributes(array $attributes): static
    {
        $this->requestAttributes = $attributes;
        return $this;
    }

    public function setHandledVerb(array $handled): static
    {
        $this->handledVerb = $handled;
        return $this;
    }
}
