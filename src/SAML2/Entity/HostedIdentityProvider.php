<?php

/**
 * Copyright (C) 2017 Adactive SAS
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ArieTimmerman\Laravel\SAML\SAML2\Entity;

use SAML2\Configuration\IdentityProvider;
use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use ArieTimmerman\Laravel\SAML\SAML2\Container;
use ArieTimmerman\Laravel\SAML\SAMLConfig;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use SAML2\Compat\ContainerSingleton;

class HostedIdentityProvider
{
    protected IdentityProvider $identityProvider;

    public function __construct($configuration)
    {

        ContainerSingleton::setContainer(new Container());

        $this->identityProvider = new IdentityProvider($configuration);
    }
    
    /**
     *
     * TODO: Not in use?
     * @return string
     */
    public function getSsoUrl()
    {
        return $this->identityProvider->get('ssoUrl');
    }
    
    public function getPreviousSessionAuthnContextClassRef()
    {
        return $this->identityProvider->get('PreviousSession');
    }
    
    /**
     * @return string
     */
    public function getSlsUrl()
    {
        return $this->identityProvider->get('slsUrl');
    }
    
    /**
     * Returns the response for authenticating the user
     * @return string
     */
    public function getStartAuthenticateResponse(SamlState $samlState)
    {

        //$isPassive, $isForce, $authenticationContext
        //  $authnRequest->getIsPassive(), $authnRequest->getForceAuthn(), $authnRequest->getRequestedAuthnContext()
        //  $isPassive, $isForce, $authenticationContext
        return  resolve(SAMLConfig::class)->doAuthenticationResponse($samlState);
    }

    /**
     * Whether authentication requests must be signed
     * @return bool
     */
    public function wantSignedAuthnRequest()
    {
        return $this->identityProvider->get("sign.authnrequest");
    }

    /**
     * Whether logout requests must be signed
     * @return bool
     */
    public function wantSignedLogoutRequest()
    {
        return $this->identityProvider->get("wantSignedLogoutRequest");
    }
    
    /*
     *
     */
    public function isEnabled()
    {
        return true;
    }
    
    public function getPrivateKey($name, $required = false)
    {
        
        //TODO: do something with name
        $keys = $this->identityProvider->get('keys');
        
        $result = @$keys[0]['private'];
        
        if (empty($result)) {
            throw new SAMLException("No private key found with name " . $name);
        }
        
        return $result;
    }
    
    //TODO: do something with name??
    public function getCertificateData()
    {
        $keys = $this->identityProvider->get('keys');
        
        $result = @$keys[0]['X509Certificate'];
        
        return $result;
    }

    public function getEntityId()
    {
        return $this->identityProvider->getEntityId();
    }
}
