<?php

/**
 * test
 */

namespace ArieTimmerman\Laravel\SAML\Http\Controllers;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\SAML\Providers\HostedIdentityProviderProcessor;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProvider;
use ArieTimmerman\Laravel\SAML\SAML2\Binding\HttpBindingContainer;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\DefaultServiceProviderRepository;
use ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface;
use ArieTimmerman\Laravel\SAML\Helper;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlStateHandler;
use ArieTimmerman\Laravel\SAML\Subject;
use ArieTimmerman\Laravel\SAML\Auth;

class SAMLController extends Controller
{
    public static function getIdpProcessor($request, $state = null)
    {

        /**
         *
         */
        $identityProviderConfig = resolve(HostedIdentityProviderConfigRepositoryInterface::class)->get();
        $identityProvider = new HostedIdentityProvider($identityProviderConfig->toSimpleSAMLArray());

        $serviceProviderRepository = new DefaultServiceProviderRepository($identityProvider);

        $bindingContainer = new HttpBindingContainer(
            new \ArieTimmerman\Laravel\SAML\SAML2\Binding\HttpRedirectBinding(),
            new \ArieTimmerman\Laravel\SAML\SAML2\Binding\HttpPostBinding()
        );

        $stateHandler = new SamlStateHandler($state);

        return new HostedIdentityProviderProcessor($serviceProviderRepository, $identityProvider, $bindingContainer, $stateHandler);
    }

    public function metadata(Request $request)
    {
        return \response(self::getIdpProcessor($request, Helper::getSAMLState())->getMetadataXmlResponse())->header('Content-Type', 'application/xml');
    }

    public function logout(Request $request)
    {
        return self::getIdpProcessor($request, Helper::getSAMLState())->processSingleLogoutService($request);
    }

    public function logoutContinue(Request $request)
    {
        return self::getIdpProcessor($request, Helper::getSAMLState())->continueSingleLogoutService();
    }

    public function idp(Request $request)
    {
        return self::getIdpProcessor($request, Helper::getSAMLState())->processSingleSignOn($request);
    }

    public function idpContinue(Request $request)
    {
        return self::getIdpProcessor($request, Helper::getSAMLState())->continueSingleSignOn(new Subject(Auth::user()));
    }

    public function notFound(Request $request)
    {
        return response(null, 404);
    }
}
