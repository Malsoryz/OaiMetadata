<?php

namespace Malsoryz\OaiXml;

use App\Classes\Plugin;
use App\Facades\Plugin as FacadesPlugin;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\Translator;
use Symfony\Component\Yaml\Yaml;

use Malsoryz\OaiXml\Controllers\OaiController;

use App\Models\Conference;

class OaiXml extends Plugin
{
    public function boot()
    {
        // $this->setPluginPath('plugins/'.$this->getInfo('folder'));

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
            Route::get('{conference:path}/oai', OaiController::class)->name('oai');
        });
    }
}