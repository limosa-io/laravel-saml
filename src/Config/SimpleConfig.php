<?php

namespace ArieTimmerman\Laravel\SAML\Config;

class SimpleConfig extends Config
{
    public function __construct()
    {
        // optionally implement this
    }

    public function get($key)
    {
        return \config($key);
    }
}
