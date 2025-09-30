<?php

namespace Leconfe\OaiMetadata\Isolated\Enums;

enum ErrorCodes: string
{
    case BadArgument = 'badArgument';
    case BadResumptionToken = 'badResumptionToken';
    case BadVerb = 'badVerb';
    case CannotDisseminateFormat = 'cannotDisseminateFormat';
    case IdDoesNotExist = 'idDoesNotExist';
    case NoRecordMatch = 'noRecordsMatch';
    case NoMetadataFormats = 'noMetadataFormats';
    case NoSetHierarchy = 'noSetHierarchy';
}