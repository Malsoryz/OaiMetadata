<?php

namespace OaiMetadataFormat\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conference;

use OaiMetadataFormat\Classes\OAI;
use OaiMetadataFormat\Classes\OaiXml;

class OaiController extends Controller
{
    public function __invoke(Conference $conference)
    {
        $xml = new OaiXml(request(), $conference, now());

        return response($xml->getXml(), 200)
            ->header('Content-type', 'application/xml');
    }
}