<?php

namespace OaiMetadataFormat\Classes;

use App\Models\Conference;
use OaiMetadataFormat\Classes\OaiXml;
use OaiMetadataFormat\Enums\Verb;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class OAI
{
    protected ArrayToXml $xml;

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
        $this->xml = new ArrayToXml(
            array: $this->getResponse($request, $responseDate),
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

    public function getResponse(Request $request, Carbon $responseDate): array
    {
        if (!Verb::check($request->query('verb'))) {
            $badVerb = Verb::badVerb();
            return [
                'responseDate' => $responseDate->toIso8601ZuluString(),
                'request' => $request->url(),
                'error' => [
                    '_value' => $badVerb['message'],
                    '_attributes' => [
                        'code' => $badVerb['code'],
                    ],
                ],
            ];
        }

        return [
            'responseDate' => $responseDate->toIso8601ZuluString(),
            'request' => [
                '_value' => $request->url(),
            ]
        ];
    }
}