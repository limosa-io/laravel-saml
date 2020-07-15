<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProvider;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProvider;
use ArieTimmerman\Laravel\SAML\Config\Config;
use ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepositoryInterface;
use ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException;

class DefaultServiceProviderRepository implements \ArieTimmerman\Laravel\SAML\SAML2\Entity\ServiceProviderRepository
{
    protected $identityProvider;

    public function __construct(HostedIdentityProvider $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    protected function getIdentiyProvider()
    {
        return $this->identityProvider;
    }

    public function getServiceProvider($entityId)
    {
        $serviceProviderconfig = resolve(RemoteServiceProviderConfigRepositoryInterface::class)->get($entityId);

        if ($serviceProviderconfig == null) {
            throw new BadRequestHttpException('Unknown entityid: ' . $entityId);
        }

        return new RemoteServiceProvider($serviceProviderconfig->toSimpleSAMLArray(), $this->getIdentiyProvider());
    }

    /**
     * @deprecated
     */
    public function hasServiceProvider($entityId)
    {
        $serviceProviderConfigs = Config::getInstance()->get('saml_sp');

        return isset($serviceProviderConfigs[$entityId]);
    }
}
