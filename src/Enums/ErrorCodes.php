<?php

namespace Malsoryz\OaiXml\Enums;

enum ErrorCodes: string {
    case BadArgument = 'badArgument';
    case BadResumptionToken = 'badResumptionToken';
    case BadVerb = 'badVerb';
    case CannotDisseminateFormat = 'cannotDisseminateFormat';
    case IdDoesNotExist = 'idDoesNotExist';
    case NoRecordsMatch = 'noRecordsMatch';
    case NoMetadataFormats = 'noMetadataFormats';
    case NoSetHierarchy = 'noSetHierarchy';

    // using hash
    public const REPEATED_ARGUMENT = '$2y$10$zPnmsan2nIDN7IJ6BTH4Deg3ZcUgDrxXzHjP315qxZgE53/aQ16Ca';
    public const MISSING_ARGUMENT = null;
}