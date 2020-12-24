<?php

namespace ArieTimmerman\Laravel\SAML;

use ArieTimmerman\Laravel\SAML\Middleware\SAMLState;
use Illuminate\Support\Facades\Route;

/**
 * Helper class for the URL shortener
 */
class RouteProvider
{

    protected static $prefix = 'saml';

    public static function routesManage(array $options = [])
    {

        $prefix = self::$prefix;

        Route::prefix($prefix)->group(function () use ($options, $prefix) {

            Route::middleware(['api'])->group(function () use ($options) {

                Route::get('/manage/serviceproviders/{id}', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@get');
                Route::get('/manage/serviceproviders', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@index');
                Route::post('/manage/serviceproviders', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@create');
                Route::put('/manage/serviceproviders/{id}', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@put');
                Route::delete('/manage/serviceproviders/{id}', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@delete');

                Route::get('/manage/identityprovider', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@getIdentityProvider');
                Route::put('/manage/identityprovider', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@putIdentityProvider');

                Route::post('/manage/importMetadata', '\ArieTimmerman\Laravel\SAML\Http\Controllers\ManageController@importMetadata');
            });
        });
    }

    public static function routes(array $options = [])
    {
        $prefix = self::$prefix;

        Route::prefix($prefix)->group(function () use ($options, $prefix) {

            Route::prefix('v2')->middleware([
                'bindings', 'web', SAMLState::class
                // SAMLState::class
            ])
                ->group(function () use ($options) {
                    self::allRoutes($options);
                });
        });
    }

    private static function allRoutes(array $options = [])
    {

        // Route::bind('samlState', function ($name, $route) {
        // 	return Helper::getSAMLStateOrFail();
        // });

        // Helper::parseMetaData("ae");
        Route::match(['get', 'post'], '/login', '\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@idp')->name('saml.sso');

        // Route::get('/authenticate/{samlState}',function(){
        // 	return "Log in now ...";
        // })->name('loginform');

        Route::get('/continue', '\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@idpContinue')->name('ssourl.continue');

        Route::get('/logout', '\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@logout')->name('saml.slo');
        Route::get('/logout/continue', '\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@logoutContinue')->name('saml.slo.continue');

        Route::get('/metadata.xml', '\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@metadata')->name('metadata');

        Route::fallback('\ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController@notFound');
    }
}
