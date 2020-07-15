<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use SimpleSAML\XML\Shib13\AuthnRequest;

interface HostedIdentityProviderConfigInterface
{
    public function toSimpleSAMLArray();

    public function fromSimpleSAMLArray(array $array);
}
