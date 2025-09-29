<?php

namespace Leconfe\OaiMetadata\Concerns\Oai;

use Leconfe\OaiMetadata\Oai\Metadata\Metadata;
use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\Verb;

use Illuminate\Http\Request;

trait MetadataPrefixChecker
{
    public static function checkMetadata(Request $request): OaiError|Metadata
    {
        return Metadata::checkMetadataFormat($request->query(Verb::QUERY_METADATA_PREFIX));
    }
}