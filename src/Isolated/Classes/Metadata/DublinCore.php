<?php

namespace Leconfe\OaiMetadata\Isolated\Classes\Metadata;

use App\Models\Submission;
use Leconfe\OaiMetadata\Isolated\Interface\Serializable;

class DublinCore implements Serializable
{
    public const METADATA_PREFIX = 'oai_dc';
    public const ELEMENT_PREFIX = 'dc';
    public const SCHEMA_LOCATION = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    public const METADATA_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

    public static function serialize(Submission $paper): static
    {
        return new static();
    }
}