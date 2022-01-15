<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Modifications copyright (C) 2017 Adactive SAS
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

use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use ArieTimmerman\Laravel\SAML\SAMLConfig;
use ArieTimmerman\Laravel\SAML\Subject;
use SAML2\AuthnRequest;

class RemoteServiceProvider extends \SAML2\Configuration\ServiceProvider
{
    protected $identityProvider;

    /**
     *
     * @param array $configuration {
     *     A description of this array.
     *
     *     @type string $entityid Whether this element is required
     *     @type array  $contacts
     *     @type AssertionConsumerService
     *     @type SingleLogoutService
     * }
     *
     */
    public function __construct($configuration, $identityProvider)
    {
        parent::__construct($configuration);

        $this->identityProvider = $identityProvider;
    }

    public function getAssertionConsumerUrl(\SAML2\AuthnRequest $authnRequest)
    {
        
        return $this->getAssertionConsumerService($authnRequest)['Location'];
    }

    public function getAssertionConsumerBinding(\SAML2\AuthnRequest $authnRequest)
    {
        return $this->getAssertionConsumerService($authnRequest)['Binding'];
    }

    /**
     * @return string|null
     */
    public function getSingleLogoutUrl()
    {
        $result = null;
        $list = $this->get('SingleLogoutService');

        if (empty($list)) {
            throw new SAMLException('No SingleLogoutService defined');
        }

        foreach ($list as $singleLogoutService) {
            $result = $singleLogoutService['Location'];
            break;
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getSingleLogoutBinding()
    {
        $binding = null;

        foreach ($this->get('SingleLogoutService') as $singleLogoutService) {
            if ($singleLogoutService['Binding'] == \SAML2\Constants::BINDING_HTTP_REDIRECT || $singleLogoutService['Binding'] == \SAML2\Constants::BINDING_HTTP_POST) {
                $binding = $singleLogoutService['Binding'];
                break;
            }
        }

        return $binding;
    }

    public function getNameIdValueForUser($format, Subject $user)
    {
        return resolve(SAMLConfig::class)->nameIdValue($format, $user);
    }

    /**
     * @return bool
     */
    public function wantSignedAuthnRequest()
    {
        return $this->get('wantSignedAuthnRequest', false);
    }

    /**
     * @return bool
     */
    public function wantSignedAuthnResponse()
    {
        return $this->get('wantSignedAuthnResponse', true);
    }

    /**
     * @return bool
     */
    public function wantSignedAssertions()
    {
        return $this->get('saml20.sign.assertion', true);
    }

    /**
     * @return bool
     */
    public function wantSignedLogoutResponse()
    {
        return $this->get('wantSignedLogoutResponse', true);
    }

    /**
     * @return bool
     */
    public function wantSignedLogoutRequest()
    {
        return $this->get('wantSignedLogoutRequest', true);
    }

    /**
     * @return string|null
     */
    public function getNameIdFormat(AuthnRequest $authnRequest)
    {
        return resolve(SAMLConfig::class)->nameIdFormat($authnRequest);
    }

    /**
     * @return string|null
     */
    public function getNameQualifier()
    {
        return $this->get('NameQualifier');
    }

    /**
     * @return int
     */
    public function getMaxRetryLogin()
    {
        return $this->get('maxRetryLogin', 0);
    }

    //TODO: consider something else?
    public function getEntityId() : ?string
    {
        return $this->get('entityid');
    }

    public function getAssertionConsumerService(\SAML2\AuthnRequest $authnRequest)
    {
        $result = null;

        $options = collect($this->get('AssertionConsumerService'));

        if (($index = $authnRequest->getAssertionConsumerServiceIndex()) !== null) {
            $matches = $options->filter(function ($value, $key) use ($index) {
                return $value['index'] == $index;
            });

            $result = count($matches) == 1 ? $matches[0] : null;
        } elseif (($location = $authnRequest->getAssertionConsumerServiceURL()) && ($binding = $authnRequest->getProtocolBinding())) {
            $matches = $options->filter(function ($value, $key) use ($location, $binding) {
                return $value['Binding'] == $binding && $value['Location'] == $location;
            });

            $result = count($matches) == 1 ? $matches[0] : null;
        }


        if ($result == null) {
            throw new SAMLException('Unknown assertion url');
        }

        return $result;
    }
}
