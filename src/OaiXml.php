<?php

namespace Malsoryz\OaiXml;

use App\Models\Conference;
use App\Classes\Plugin;
use App\Facades\Plugin as FacadesPlugin;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\Translator;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

use Malsoryz\OaiXml\Oai\OaiXml as Xml;
use Malsoryz\OaiXml\Oai\Repository;

use Malsoryz\OaiXml\Oai\Identifier\Granularity;

class OaiXml extends Plugin
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
                $repository = new Repository(
                    'Leconfe',
                    $conference,
                    $request->url(),
                    Granularity::Second
                );

                $xml = new Xml($request, $conference, $repository);
                $xml->handle(now())
                    ->addPI([
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