<?php

namespace ArieTimmerman\Laravel\SAML\Exceptions;

class InvalidSamlRequestException extends LogicException
{
    /**
     * @var string
     */
    protected $samlStatusCode;

    /**
     * InvalidSamlRequestException constructor.
     * @param string $msg
     * @param string $samlStatusCode
     * @param \Exception $previous
     */
    public function __construct($msg, $samlStatusCode, \Exception $previous  = null)
    {
        parent::__construct($msg, 0, $previous);
        $this->samlStatusCode = $samlStatusCode;
    }

    /**
     * @return string
     */
    public function getSamlStatusCode()
    {
        return $this->samlStatusCode;
    }
    
    public function setIdp($idp)
    {
        return $this;
    }
    
    public function setAuthnRequest($authnRequest)
    {
        // do something
        
        return $this;
    }
    
    public function render()
    {
        die("render error!");
        
        $sp = $this->getServiceProvider($authRequest->getIssuer());
        $outBinding = $this->bindingContainer->get($sp->getAssertionConsumerBinding());
        
        $authnResponse = $this->buildAuthnFailedResponse($authRequest, $e->getSamlStatusCode());
        
        if ($sp->wantSignedAuthnResponse()) {
            return $outBinding->getSignedResponse($authnResponse);
        }
        
        return $outBinding->getUnsignedResponse($authnResponse);
    }
}
