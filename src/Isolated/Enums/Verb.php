<?php

namespace Leconfe\OaiMetadata\Isolated\Enums;

enum Verb: string 
{
    case Identify = 'Identify';
    case GetRecord = 'GetRecord';
    case ListRecords = 'ListRecords';
    case ListSets = 'ListSets';
    case ListMetadataFormats = 'ListMetadataFormats';
    case ListIdentifiers = 'ListIdentifiers';
}