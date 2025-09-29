<?php

namespace Leconfe\OaiMetadata\Oai;

use Leconfe\OaiMetadata\Oai\OaiXml;
use Leconfe\OaiMetadata\Oai\Repository;

class Element
{
    public static function rootElement(): array
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

    public static function mainElement(OaiXml $oaixml): array
    {
        $granularity = $oaixml->getRepository()->getGranularity();

        return [
            'responseDate' => $granularity->format(now()),
            'request' => [
                '_value' => $oaixml->getRequest()->url(),
                '_attributes' => $oaixml->getRequestAttributes(),
            ],
        ];
    }

    // pending
    public static function oaiIdentifier(array $description): array
    {
        $scheme = $description['scheme'] ?? 'oai';
        $repositoryIdentifier = $description['repositoryIdentifier'];
        $delimiter = $description['delimiter'] ?? ':';

        $recordPrefix = Repository::RECORD_PREFIX;

        $sampleIdentifier = "{$scheme}{$delimiter}{$repositoryIdentifier}{$delimiter}{$recordPrefix}/12345";

        return [
            'oai-identifier' => [
                '_attributes' => [
                    'xmlns' => 'http://www.openarchives.org/OAI/2.0/oai-identifier',
                    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd',
                ],
                'scheme' => $scheme,
                'repositoryIdentifier' => $repositoryIdentifier,
                'delimiter' => $delimiter,
                'sampleIdentifier' => $sampleIdentifier,
            ],
        ];
    }
}