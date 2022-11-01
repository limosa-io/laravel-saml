<?php

namespace ArieTimmerman\Laravel\SAML;

use ArieTimmerman\Laravel\SAML\Config\Config;
use ArieTimmerman\Laravel\SAML\Exceptions\InvalidSamlRequestException;
use SAML2\AuthnRequest;
use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use ArieTimmerman\Laravel\SAML\SAML2\Constants;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use Illuminate\Support\Facades\Auth;

class SAMLConfig
{
    public function nameIdFormat(AuthnRequest $authnRequest)
    {

        $result = null;

        if ($nameIdPolicy = $authnRequest->getNameIdPolicy()) {
            $result = isset($nameIdPolicy['Format']) ? $nameIdPolicy['Format'] : null;
        }

        if ($result == null) {
            $result = "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified";
        }

        if (empty($result)) {
            throw new SAMLException('No default nameId for the service provider.');
        }

        return $result;
    }

    public function nameIdValue($format, Subject $subject)
    {
        return $subject->getNameIdValue();
    }

    public function doLogoutResponse()
    {
        Auth::logout();

        return null;
    }

    public function doAuthenticationResponse(SamlState $state)
    {

        $isPassive = $state->getRequest()->getIsPassive();
        $isForce = $state->getRequest()->getForceAuthn();
        $requestedAuthnContext = $state->getRequest()->getRequestedAuthnContext() ?? [];

        $result = null;

        $isAuthenticated = Auth::check();

        if ($isAuthenticated) {
            Helper::getSAMLStateOrFail()->setAuthnContext(Constants::AC_PREVIOUS_SESSION);
        } else {
            if ($isPassive) {
                throw (
                    new InvalidSamlRequestException(
                        'Invalid Saml request: cannot authenticate passively',
                        \SAML2\Constants::STATUS_NO_PASSIVE
                    )
                    )->setAuthnRequest($state->getRequest());
            }

            $result = redirect(route('loginform', [
                "samlState" => Helper::getSAMLStateOrFail()->id,
                "useTestLogin" => Config::getInstance()->get('useTestLogin')
            ]));
        }

        return $result;
    }
}
