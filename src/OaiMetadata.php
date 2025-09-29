<?php

namespace Leconfe\OaiMetadata;

use App\Models\Conference;
use App\Classes\Plugin;
use App\Facades\Plugin as FacadesPlugin;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\Translator;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

use Leconfe\OaiMetadata\Oai\OaiXml as Xml;
use Leconfe\OaiMetadata\Oai\Repository;

use Leconfe\OaiMetadata\Oai\Identifier\Granularity;

class OaiMetadata extends Plugin
{
    public function boot()
    {
        $this->enablePublicAsset();
        $this->registerRoute();
    }

    protected function loadTranslation(): void
    {
        $this->info = $this->loadInformation();

        $langPath = $this->getPluginPath('lang');
        $translator = app()->make(Translator::class);

        $translator->addNamespace($this->getInfo('folder'), $langPath);
    }

    protected function registerRoute(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('{conference:path}/oai', function (Conference $conference, Request $request) {
                $xml = new Xml($request);
                
                $xml->handle()->addPI([
                    'xml-stylesheet' => [
                        'type' => 'text/xsl',
                        'href' => asset($this->getAssetsPath('lib/xsl/oai2.xsl')),
                    ],
                ]);

                return response($xml->convert()->saveXML(), 200)
                    ->header('Content-type', 'application/xml');
            })->name('oai');
        });
    }
}