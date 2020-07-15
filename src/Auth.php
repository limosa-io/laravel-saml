<?php

namespace ArieTimmerman\Laravel\SAML;

use Auth as LaravelAuth;

class Auth
{

    public static function user()
    {
        return LaravelAuth::user();
    }

    public static function check()
    {
        return LaravelAuth::check();
    }
}
