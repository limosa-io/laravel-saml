<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use SimpleSAML\XML\Shib13\AuthnRequest;

class RemoteServiceProviderConfig implements RemoteServiceProviderConfigInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     *
     */
    public function toSimpleSAMLArray()
    {
        return $this->config;
    }

    public function fromSimpleSAMLArray(array $array)
    {
        $this->config = $array;
    }
}
