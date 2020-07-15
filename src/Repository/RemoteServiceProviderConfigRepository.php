<?php

namespace ArieTimmerman\Laravel\SAML\Repository;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfigInterface;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfig;
use ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException;
use ArieTimmerman\Laravel\SAML\Config\Config;

class RemoteServiceProviderConfigRepository implements RemoteServiceProviderConfigRepositoryInterface
{


    /**
     * @return RemoteServiceProviderConfigInterface[]
     */
    public function all()
    {
        throw new BadRequestHttpException('Not supported');
    }

    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function get($entityId)
    {
        $serviceProviderConfigs = Config::getInstance()->get('saml_sp');

        new RemoteServiceProviderConfig($serviceProviderConfigs[$entityId]);
    }

    public function getById($id)
    {
        throw new BadRequestHttpException('Not supported');
    }

    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function add(array $remoteServiceProviderConfigArray)
    {
        throw new BadRequestHttpException('Not supported');
    }

    public function patch(string $entityId, array $remoteServiceProviderConfigArray)
    {
        throw new BadRequestHttpException('Not supported');
    }

    public function deleteById(string $id)
    {
        throw new BadRequestHttpException('Not supported');
    }
}
