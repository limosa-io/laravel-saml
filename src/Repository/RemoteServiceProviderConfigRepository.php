<?php

namespace ArieTimmerman\Laravel\SAML\Repository;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfigInterface;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfig;
use ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException;
use ArieTimmerman\Laravel\SAML\Config\Config;
use Illuminate\Http\Request;

class RemoteServiceProviderConfigRepository implements RemoteServiceProviderConfigRepositoryInterface
{
    protected $rules = [
        'entityid' => 'required',
        'AssertionConsumerService' => 'nullable|array',
        'AssertionConsumerService.*.Binding' => 'required',
        'AssertionConsumerService.*.Location' => 'required|url',
        'AssertionConsumerService.*.index' => 'required|integer',
        'SingleLogoutService' => 'nullable',
        'SingleLogoutService.*.Binding' => 'nullable',
        'SingleLogoutService.*.Location' => 'url',
        'keys' => 'nullable',
        'keys.*.encryption' => 'required|boolean',
        'keys.*.signing' => 'required|boolean',
        'keys.*.type' => 'required',
        'keys.*.X509Certificate' => 'required',
        'wantSignedAuthnResponse' => 'nullable|boolean',

        // TODO: should probably be something like this: saml20.sign.assertion, validate.authnrequest etc
        'wantSignedAssertions' => 'nullable|boolean',
        'wantSignedLogoutResponse' => 'nullable|boolean',
        'wantSignedLogoutRequest' => 'nullable|boolean'
    ];

    public function validate(Request $request){
        return $request->validate($this->rules);
    }

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
