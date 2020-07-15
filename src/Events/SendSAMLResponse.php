<?php

namespace ArieTimmerman\Laravel\SAML\Events;

use Illuminate\Queue\SerializesModels;

class SendSAMLResponse
{
    use SerializesModels;
    
    protected $message;
    
    public function __construct($message)
    {
        $this->message = $message;
    }
}
