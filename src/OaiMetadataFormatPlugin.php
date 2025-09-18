<?php

namespace OaiMetadataFormat;

use App\Classes\Plugin;

use Illuminate\Support\Facades\Route;

use OaiMetadataFormat\Controllers\OaiController;

use App\Models\Conference;

class OaiMetadataFormatPlugin extends Plugin
{
    public function boot()
    {
        $this->registerRoute();
    }

    protected function registerRoute(): void
    {
        Route::get('{conference:path}/lib/xsl/oai2.xsl', function (Conference $conference) {
            $path = base_path('plugins/OAIMetadataFormat/resources/xsl/oai2.xsl');

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