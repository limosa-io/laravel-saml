

![](https://github.com/arietimmerman/laravel-saml/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/arietimmerman/laravel-saml/v/stable)](https://packagist.org/packages/arietimmerman/laravel-saml)
[![Total Downloads](https://poser.pugx.org/arietimmerman/laravel-saml/downloads)](https://packagist.org/packages/arietimmerman/laravel-saml)

# SAML for Laravel

This is an SAML Identity Provider written in PHP with Laravel, built on top of [simplesamlphp/simplesamlphp](https://github.com/simplesamlphp/simplesamlphp) and some pieces of `adactive-sas/saml2-bridge-bundle`.

It is used by [idaas.nl](https://www.idaas.nl/): (not) yet another identity as a service platform.

__This library - especially the documentation - is work in progress__

## Installation

~~~
composer require arietimmerman/laravel-saml
~~~

Generate a keypair.

~~~
openssl req -new -x509 -days 3652 -nodes -out public.key -keyout private.key
~~~

Exclude url from csrf protection

~~~
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/saml/v2/*'
    ];
}
~~~

In your `routes/web.php` include the following.

~~~.php
ArieTimmerman\Laravel\SAML\RouteProvider::routes();
~~~

On login, do something like the following
~~~
Helper::getSAMLStateOrFail()->setAuthnContext(Constants::AC_KERBEROS);
Helper::saveSAMLState();
~~~

Redirect to the following
~~~~
'http://www.ice.test/saml/v2/continue/' . Helper::getSAMLStateOrFail()->id;
~~~~

Example request:

~~~
http://samlidp.dev/saml/v2/login?SAMLRequest=...
~~~
