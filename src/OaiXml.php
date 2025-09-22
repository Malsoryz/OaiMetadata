<?php

namespace Malsoryz\OaiXml;

use App\Classes\Plugin;
use Illuminate\Support\Facades\Route;

use Malsoryz\OaiXml\Controllers\OaiController;

use App\Models\Conference;

class OaiXml extends Plugin
{
    public function boot()
    {
        $this->registerRoute();
    }

    protected function registerRoute(): void
    {
        Route::get('{conference:path}/lib/xsl/oai2.xsl', function (Conference $conference) {
            $path = $this->getAssetsPath('public/lib/xsl/oai2.xsl');

            if (!file_exists($path)) {
                abort(404, 'Stylesheet not found.');
            }

            return response()->file($path);
        })->name('oai2.xsl');

        Route::middleware('web')->group(function () {
            Route::get('{conference:path}/oai', OaiController::class);
        });
    }
}