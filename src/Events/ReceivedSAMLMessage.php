<?php

namespace ArieTimmerman\Laravel\SAML\Events;

use Illuminate\Queue\SerializesModels;

class ReceivedSAMLMessage
{
    use SerializesModels;
    
    protected $message;
    
    public function __construct($message)
    {
        $this->message = $message;
    }
}
