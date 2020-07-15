<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

interface RemoteServiceProviderConfigInterface
{

    public function toSimpleSAMLArray();

    public function fromSimpleSAMLArray(array $array);

}