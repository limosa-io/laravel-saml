<?php

namespace ArieTimmerman\Laravel\SAML\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class SAMLException extends Exception
{
    public function __construct($message, $e = null)
    {
        parent::__construct($message);
    }
    
    public function report()
    {
        Log::critical($this->message);
    }
    
    public function render($request)
    {
        return response()->view('saml::errors.default', [
            'exception' => $this
        ], 400);
    }
}
