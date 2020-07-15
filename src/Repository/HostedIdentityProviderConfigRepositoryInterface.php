<?php

namespace ArieTimmerman\Laravel\SAML\Repository;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProviderConfigInterface;

interface HostedIdentityProviderConfigRepositoryInterface
{
    /**
     * @return HostedIdentityProviderConfigInterface
     */
    public function get();

    public function patch(array $remoteIdentityProviderConfigArray);
}
