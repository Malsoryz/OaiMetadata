<?php

namespace Malsoryz\OaiXml\Classes;

use App\Models\Conference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Malsoryz\OaiXml\Classes\OaiXml\ListRecords;
use Malsoryz\OaiXml\Enums\ErrorCodes;
use Malsoryz\OaiXml\Enums\Metadata\DublinCore as DCEnum;
use Malsoryz\OaiXml\Enums\Verb;
use Malsoryz\OaiXml\Metadata\DublinCore;
use Spatie\ArrayToXml\ArrayToXml as Xml;

class OaiXml extends Xml
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

        $this->xml->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . asset('plugin/oaixml/lib/xsl/oai2.xsl') . '"');
    }

    public function getXml()
    {
        return $this->xml->toXml();
    }

    public function retrieveUrlQuery(Request $request): Collection
    {
        $rawQuery = parse_url($request->server('REQUEST_URI'), PHP_URL_QUERY);
        return Collection::wrap(explode('&', $rawQuery))
            ->filter()
            ->map(function ($query) {
                [$key, $value] = array_pad(explode('=', $query, 2), 2, null);
                return [
                    'key' => $key,
                    'value' => urldecode($value),
                ];
            })
            ->groupBy('key')
            ->map(function ($items) {
                $values = $items->pluck('value')->all();
                return count($values) > 1 
                    ? ErrorCodes::REPEATED_ARGUMENT
                    : $values[0];
            });
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
        $isError = $this->checkQueryErrors($request);
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
            $response['error'] = $this->errors;
        }

        return $response;
    }

    public function checkQueryErrors(Request $request): bool
    {
        $queries = $this->retrieveUrlQuery($request);

        // jika tidak ada verb
        if ($request->query(Verb::QUERY_VERB) === ErrorCodes::MISSING_ARGUMENT) {
            $this->errors[] = [
                '_value' => __('OaiXml::error.verb.missing'),
                '_attributes' => [
                    'code' => ErrorCodes::BadVerb->value,
                ],
            ];
        } else {
            // jika verb illegal
            if (! Verb::tryFrom($queries->get(Verb::QUERY_VERB)) && $queries->get(Verb::QUERY_VERB) !== ErrorCodes::REPEATED_ARGUMENT) {
                $this->errors[] = [
                    '_value' => __('OaiXml::error.verb.illegal', ['hint' => $queries->get(Verb::QUERY_VERB)]),
                    '_attributes' => [
                        'code' => ErrorCodes::BadVerb->value,
                    ],
                ];
            }
        }

        foreach ($queries as $query => $value) {
            if ($value === ErrorCodes::REPEATED_ARGUMENT) {
                $this->errors[] = [
                    '_value' => __('OaiXml::error.argument.repeated', ['hint' => $query]),
                    '_attributes' => [
                        'code' => ErrorCodes::BadVerb->value,
                    ],
                ];
            }
        }

        // Jika verb legal atau valid
        if ($verb = Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
            $requiredQueries = Collection::wrap($verb->requiredQuery());
            foreach ($requiredQueries as $query) {
                if (! $request->query($query)) {
                    $this->errors[] = [
                        '_value' => __('OaiXml::error.argument.missing', ['hint' => $query]),
                        '_attributes' => [
                            'code' => ErrorCodes::BadArgument->value,
                        ],
                    ];
                }
            }

            $allowedQueries = Collection::wrap($verb->allowedQuery());
            foreach ($queries->keys() as $query) {
                if (! $allowedQueries->contains($query)) {
                    $this->errors[] = [
                        '_value' => __('OaiXml::error.argument.illegal', ['hint' => $query]),
                        '_attributes' => [
                            'code' => ErrorCodes::BadArgument->value,
                        ],
                    ];
                }
            }
        }

        return count($this->errors) >= 1;
    }

    public function ListRecords(Verb $verb, Request $request): array
    {
        $records = new ListRecords($request, $this->conference);
        return $records->getRecords();
    }

    public function ListMetadataFormats(Verb $verb, Request $request): array
    {
        $dublinCore = DublinCore::getMetadataFormat();

        $metadataFormat = [];

        $metadataFormat[] = [
            'metadataFormat' => $dublinCore,
        ];

        return $metadataFormat;
    }
}