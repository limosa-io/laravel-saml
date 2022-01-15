<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

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
