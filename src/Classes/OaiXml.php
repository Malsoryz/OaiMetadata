<?php

namespace OaiMetadataFormat\Classes;

use App\Models\Conference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use OaiMetadataFormat\Classes\OaiXml\ListRecords;
use OaiMetadataFormat\Enums\ErrorCodes;
use OaiMetadataFormat\Enums\Metadata\DublinCore as DCEnum;
use OaiMetadataFormat\Enums\Verb;
use OaiMetadataFormat\Metadata\DublinCore;
use Spatie\ArrayToXml\ArrayToXml as Xml;

class OaiXml extends Xml
{
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

        $this->xml->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . route('oai2.xsl', ['conference' => $conference]) . '"');
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
        return [
            'responseDate' => $responseDate->toIso8601ZuluString(),
            'request' => [
                '_value' => $request->url(),
                ...($isError ? [] : ['_attributes' => $this->requestAttributes])
            ],
            ...($isError ? $this->errors : $this->handledVerb),
        ];
    }

    public function checkQueryErrors(Request $request): bool
    {
        $queries = $this->retrieveUrlQuery($request);

        $errors = Collection::wrap($this->errors);

        // jika tidak ada verb
        if ($request->query(Verb::QUERY_VERB) === ErrorCodes::MISSING_ARGUMENT) {
            $errors->push([
                '_value' => 'The request does not provide any verb.',
                '_attributes' => [
                    'code' => ErrorCodes::BadVerb->value,
                ],
            ]);
        }

        // jika verb illegal
        if (
            $request->query(Verb::QUERY_VERB) !== ErrorCodes::MISSING_ARGUMENT  
            && (! Verb::tryFrom($queries->get(Verb::QUERY_VERB))) 
            && $queries->get(Verb::QUERY_VERB) !== ErrorCodes::REPEATED_ARGUMENT
        ) {
            $errors->push([
                '_value' => "{$queries->get(Verb::QUERY_VERB)} is a illegal verb.",
                '_attributes' => [
                    'code' => ErrorCodes::BadVerb->value,
                ],
            ]);
        }

        // jika argumen paramter berulang
        if ($queries->get(Verb::QUERY_VERB) === ErrorCodes::REPEATED_ARGUMENT) {
            $errors->push([
                '_value' => 'Do not use them same argument more than once.',
                '_attributes' => [
                    'code' => ErrorCodes::BadVerb->value,
                ],
            ]);
            
            if (! Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
                $errors->push([
                    '_value' => "{$request->query(Verb::QUERY_VERB)} is a illegal verb.",
                    '_attributes' => [
                        'code' => ErrorCodes::BadVerb->value,
                    ],
                ]);
            }

            if ($verb = Verb::tryFrom($request->query(Verb::QUERY_VERB))) {
                $requiredQueries = Collection::wrap($verb->requiredQuery());
                foreach ($requiredQueries as $query) {
                    if (! $queries->get($query)) {
                        $errors->push([
                            '_value' => "Missing {$query} parameter",
                            '_attributes' => [
                                'code' => ErrorCodes::BadArgument->value,
                            ],
                        ]);
                    }
                }

                $allowedQueries = Collection::wrap($verb->allowedQuery());
                foreach ($queries->keys() as $query) {
                    if (! $allowedQueries->contains($query)) {
                        $errors->push([
                            '_value' => "{$query} is a illegal parameter.",
                            '_attributes' => [
                                'code' => ErrorCodes::BadArgument->value,
                            ],
                        ]);
                    }
                }
            }
        }

        // Jika verb legal atau valid
        if ($verb = Verb::tryFrom($queries->get(Verb::QUERY_VERB))) {
            $requiredQueries = Collection::wrap($verb->requiredQuery());
            foreach ($requiredQueries as $query) {
                if (! $queries->get($query)) {
                    $errors->push([
                        '_value' => "Missing {$query} parameter",
                        '_attributes' => [
                            'code' => ErrorCodes::BadArgument->value,
                        ],
                    ]);
                }
            }

            $allowedQueries = Collection::wrap($verb->allowedQuery());
            foreach ($queries->keys() as $query) {
                if (! $allowedQueries->contains($query)) {
                    $errors->push([
                        '_value' => "{$query} is a illegal parameter.",
                        '_attributes' => [
                            'code' => ErrorCodes::BadArgument->value,
                        ],
                    ]);
                }
            }
        }

        $this->errors = $errors->isNotEmpty() ? ['error' => $errors->toArray()] : [];

        if ($errors->isEmpty()) {
            $this->handleVerb($request);
        }

        return $errors->isNotEmpty();
    }

    //----- Handle Verbs -----//
    public function handleVerb(Request $request): void
    {
        $verb = Verb::tryFrom($request->query(Verb::QUERY_VERB));

        if (! $verb) {
            return;
        }

        $getVerbRequiredQuery = Collection::wrap($verb->requiredQuery());

        $this->requestAttributes = $getVerbRequiredQuery
            ->mapWithKeys(fn ($item) => [$item => $request->query($item)])
            ->toArray();

        $this->handledVerb = method_exists($this, $verb->value) ? $this->{$verb->value}($verb) : [];
    }

    public function ListRecords(Verb $verb): array
    {
        $record = new ListRecords([
            'identifier' => 'oai:localhost:8000:record/12345',
            'datestamp' => now(),
        ]);
        return [
            $verb->value => $record->getRecord(),
        ];
    }

    // public function ListRecords(Verb $verb): array
    // {
    //     return [
    //         $verb->value => [
    //             'record' => [
    //                 ...DublinCore::makeRecordHeader(
    //                     'oai:localhost:8000:record/1',
    //                     now(),
    //                 ),
    //                 'metadata' => [
    //                     'oai_dc:dc' => [
    //                         '_attributes' => DublinCore::getMetadataAttributes(),
    //                         ...DCEnum::make('title', 'Ini Judul'),
    //                     ],
    //                 ],
    //             ],
    //         ],
    //     ];
    // }

    public function ListMetadataFormats(Verb $verb): array
    {
        $dublinCore = DublinCore::getMetadataFormat();
        return [
            $verb->value = [
                'metadataFormat' => $dublinCore,
            ]
        ];
    }
}