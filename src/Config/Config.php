<?php

namespace ArieTimmerman\Laravel\SAML\Config;

abstract class Config
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            $class = config('saml.config');
            self::$instance = new $class();
        }

        return self::$instance;
    }

    abstract public function get($key);
}
