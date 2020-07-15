<?php

namespace ArieTimmerman\Laravel\SAML\Repository;

use ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException;
use ArieTimmerman\Laravel\SAML\Config\Config;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProviderConfig;

class HostedIdentityProviderConfigRepository implements HostedIdentityProviderConfigRepositoryInterface
{
    public function get()
    {
        return new HostedIdentityProviderConfig(Config::getInstance()->get('saml.idp'));
    }

    public function patch(array $remoteIdentityProviderConfigArray)
    {
        throw new BadRequestHttpException('Not supported');
    }
}
