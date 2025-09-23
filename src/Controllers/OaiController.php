<?php

namespace Malsoryz\OaiXml\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conference;

use Malsoryz\OaiXml\Classes\OaiXml;

class OaiController extends Controller
{
    public function __invoke(Conference $conference)
    {
        $xml = new OaiXml(request(), $conference, now());

        return response($xml->getXml(), 200)
            ->header('Content-type', 'application/xml');
    }
}