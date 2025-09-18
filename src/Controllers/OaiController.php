<?php

namespace OaiMetadataFormat\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conference;

use DOMDocument;

use Spatie\ArrayToXml\ArrayToXml;

class OaiController extends Controller
{
    public function __invoke(Conference $conference)
    {
        $rootElement = [
            'rootElementName' => 'OAI-PMH',
            '_attributes' => [
                'xmlns' => 'http://www.openarchives.org/OAI/2.0/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd',
            ],
        ];

        $data = [
            'responseDate' => now()->toIso8601ZuluString(),
            'request' => [
                '_value' => request()->url(),
                ...(
                    request()->query('verb') || request()->query('metadataPrefix')
                        ? ['_attributes' => [
                            ...(request()->query('verb') ? ['verb' => request()->query('verb')] : []),
                            ...(request()->query('metadataPrefix') ? ['metadataPrefix' => request()->query('metadataPrefix')] : [])
                        ]]
                        : []
                )
            ]
        ];

        $xml = new ArrayToXml(
            array: $data, 
            rootElement: $rootElement,
            xmlEncoding: 'UTF-8',
            xmlVersion: '1.0',
            domProperties: ['formatOutput' => true],
        );

        $xml->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . route('oai2.xsl', ['conference' => $conference]) . '"');

        return response($xml->toXml(), 200)
            ->header('Content-type', 'application/xml');
    }
}