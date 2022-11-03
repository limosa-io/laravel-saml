<?php

namespace ArieTimmerman\Laravel\SAML\Repository;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfigInterface;
use Illuminate\Http\Request;

interface RemoteServiceProviderConfigRepositoryInterface
{
    public function validate(Request $request);

    /**
     * @return RemoteServiceProviderConfigInterface[]
     */
    public function all();

    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function get($entityId);

    /**
     * Get by database id
     */
    public function getById($entityId);


    public function patch(string $entityId, array $remoteServiceProviderConfigArray);

    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function add(array $remoteServiceProviderConfigArray);

    /**
     * Get by database id
     */
    public function deleteById(string $entityId);
}
