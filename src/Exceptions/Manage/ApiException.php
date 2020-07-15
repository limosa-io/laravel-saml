<?php

namespace ArieTimmerman\Laravel\SAML\Exceptions\Manage;

use Exception;
use Illuminate\Support\Facades\Log;

class ApiException extends Exception
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
        return response()->json(['error'=>$this->message], 400);
    }
}
