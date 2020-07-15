<?php

namespace ArieTimmerman\Laravel\SAML\Exceptions;

class BadRequestHttpException extends SAMLException implements Exception
{
    public function render($request)
    {
        //return parent::render($request)->status(400);
        return view('errors.default')->status(400);
    }
}
