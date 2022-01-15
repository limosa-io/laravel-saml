<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

interface HostedIdentityProviderConfigInterface
{
    public function toSimpleSAMLArray();

    public function fromSimpleSAMLArray(array $array);
}
