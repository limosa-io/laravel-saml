<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['bindings','web']], function () {
    //ArieTimmerman\Laravel\SAML\RouteProvider::routes();
});
