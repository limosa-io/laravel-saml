<?php

/**
 * Copyright 2017 Adactive SAS
 * Copyright 2018 Arie Timmerman
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
namespace ArieTimmerman\Laravel\SAML\Providers;

use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProvider;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\ServiceProviderRepository;
use ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException;
use ArieTimmerman\Laravel\SAML\Exceptions\InvalidSamlRequestException;
use ArieTimmerman\Laravel\SAML\Exceptions\RuntimeException;
use ArieTimmerman\Laravel\SAML\SAML2\Binding\HttpBindingContainer;
use ArieTimmerman\Laravel\SAML\SAML2\Builder\AssertionBuilder;
use ArieTimmerman\Laravel\SAML\SAML2\Builder\AuthnResponseBuilder;
use ArieTimmerman\Laravel\SAML\SAML2\Builder\LogoutRequestBuilder;
use ArieTimmerman\Laravel\SAML\SAML2\Builder\LogoutResponseBuilder;
use ArieTimmerman\Laravel\SAML\SAML2\Event\LogoutEvent;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlStateHandler;
use Illuminate\Support\Facades\Log;
use ArieTimmerman\Laravel\SAML\SAML2\Constants;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use ArieTimmerman\Laravel\SAML\Helper;
use SAML2\AuthnRequest;
use SAML2\LogoutRequest;
use ArieTimmerman\Laravel\SAML\Auth;
use ArieTimmerman\Laravel\SAML\Events\ReceivedSAMLMessage;
use ArieTimmerman\Laravel\SAML\Events\SendSAMLResponse;
use ArieTimmerman\Laravel\SAML\SAMLConfig;
use ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface;
use ArieTimmerman\Laravel\SAML\Subject;
use Illuminate\Http\Response;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class HostedIdentityProviderProcessor
{

    /**
     *
     * @var ServiceProviderRepository
     */
    protected $serviceProviderRepository;

    /**
     *
     * @var HostedIdentityProvider
     */
    protected $identityProvider;

    /**
     *
     * @var \ArieTimmerman\Laravel\SAML\SAML2\Binding\HttpBindingContainer
     */
    protected $bindingContainer;

    /**
     *
     * @var SamlStateHandler
     */
    protected $stateHandler;

    /**
     * HostedIdentityProvider constructor.
     *
     * @param ServiceProviderRepository $serviceProviderRepository
     * @param HostedIdentityProvider $identityProvider
     * @param HttpBindingContainer $bindingContainer
     * @param SamlStateHandler $stateHandler
     *
     */
    public function __construct(ServiceProviderRepository $serviceProviderRepository, HostedIdentityProvider $identityProvider, HttpBindingContainer $bindingContainer, SamlStateHandler $stateHandler)
    {
        $this->serviceProviderRepository = $serviceProviderRepository;
        $this->identityProvider = $identityProvider;
        $this->bindingContainer = $bindingContainer;
        $this->stateHandler = $stateHandler;
    }

    /**
     *
     * @param LogoutEvent $event
     */
    public function onLogoutSuccess()
    {
        if (!$this->stateHandler->can(SamlStateHandler::TRANSITION_SLS_END_DISPATCH)) {
            Log::notice("Logout initiated by IDP");
            $this->stateHandler->resume(true);

            $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_START_BY_IDP);

            return;
        }

        Log::notice('Logout success');

        $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_END_DISPATCH);
    }

    /**
     *
     * @return \Illuminate\Http\Response;
     */
    public function getMetadataXmlResponse()
    {
        // Set the metadata id, the maxcache, and max duration
        $metaArray = resolve(HostedIdentityProviderConfigRepositoryInterface::class)->get()->toSimpleSAMLArray();

        //TODO: For now, disable asserts due to errors.
        assert_options(ASSERT_ACTIVE, 0);
        $metaBuilder = new \SimpleSAML_Metadata_SAMLBuilder($metaArray['entityId'], $metaArray['cacheDuration'], $metaArray['expire']);

        $metaArray['metadata-set'] = 'saml20-idp-remote';
        $metaArray['entityid'] = $metaArray['entityId'];

        $metaArray['SingleSignOnService'] = [];
        $metaArray['SingleLogoutService'] = [];

        if (isset($metaArray['ssoHttpPostEnabled'])) {
            $metaArray['SingleSignOnService'][] = [
                'Location' => route('saml.sso'),
                'Binding' => Constants::BINDING_HTTP_POST
            ];
        }

        if (isset($metaArray['ssoHttpRedirectEnabled'])) {
            $metaArray['SingleSignOnService'][] = [
                'Location' => route('saml.sso'),
                'Binding' => Constants::BINDING_HTTP_REDIRECT
            ];
        }

        if (isset($metaArray['sloHttpPostEnabled'])) {
            $metaArray['SingleLogoutService'][] = [
                'Location' => route('saml.slo'),
                'Binding' => Constants::BINDING_HTTP_POST,
                'ResponseLocation' => route('saml.slo')
            ];
        }

        if (isset($metaArray['sloHttpRedirectEnabled'])) {
            $metaArray['SingleLogoutService'][] = [
                'Location' => route('saml.slo'),
                'Binding' => Constants::BINDING_HTTP_REDIRECT,
                'ResponseLocation' => route('saml.slo')
            ];
        }

        // Helper::getCertificateContents(
        foreach ($metaArray['keys'] as &$key) {
            $key['X509Certificate'] = Helper::getCertificateContents($key['X509Certificate']);
        }

        $metaBuilder->addMetadata('saml20-idp-remote', $metaArray);

        if (isset($metaArray['organization'])) {
            $metaBuilder->addOrganizationInfo($metaArray['organization']);
        }

        $xml = $metaBuilder->getEntityDescriptor();

        return \ArieTimmerman\Laravel\SAML\Helper::signMetadata($xml->ownerDocument);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function processSingleSignOn(Request $request)
    {
        Log::notice('Received AuthnRequest, started processing');
        // event(new Authenticated());

        $this->stateHandler->resume(true)->apply(SamlStateHandler::TRANSITION_SSO_START);

        $inputBinding = $this->bindingContainer->getByRequestMethod($request->getMethod());

        try {
            $authnRequest = $inputBinding->receiveAuthnRequest($request);

            event(new ReceivedSAMLMessage($authnRequest));

            $this->validateMessage($authnRequest);

            if ($authnRequest->getIsPassive() && $authnRequest->getForceAuthn()) {
                throw (new InvalidSamlRequestException('Invalid Saml request: cannot be passive and force', \SAML2\Constants::STATUS_REQUESTER))->setAuthnRequest($authnRequest);
            }

            $sp = $this->getServiceProvider($authnRequest->getIssuer());

            if ($sp->wantSignedAuthnRequest()) {
                die("check of authnrequest signed is!");
            }

            $this->stateHandler->get()->setRequest($authnRequest);
        } catch (\Throwable $e) {
            throw new SAMLException(sprintf('Could not process Request, error: "%s"', $e->getMessage()), $e);
        }

        //if we start, we should finish ...
        //TODO: create event listener. Write in readme to emit event upon authentication completion
        // 	$this->stateHandler->apply ( SamlStateHandler::TRANSITION_SSO_START_AUTHENTICATE );
        
        $response = $this->identityProvider->getStartAuthenticateResponse($this->stateHandler->get());

        if ($response == null) {
            $this->stateHandler->get()->setAuthnContext($this->identityProvider->getPreviousSessionAuthnContextClassRef());

            return $this->continueSingleSignOn();
        } else {
            return $response;
        }
    }


    public function continueSingleSignOnNow(SamlState $state, $subject)
    {
    }

    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function continueSingleSignOn(Subject $subject)
    {
        Log::notice("Continue SSO process");
        
        if (!$this->stateHandler->has() || $this->stateHandler->get()->getRequest() == null) {
            throw new SAMLException("We can't continue. No saved SAML state.");
        }

        if ($this->stateHandler->get()->getAuthnContext() == null) {
            $this->stateHandler->get()->setAuthnContext(Constants::AC_PASSWORD_PROTECTED_TRANSPORT);
        }

        /** @var \SAML2\AuthnRequest $authnRequest */
        $authnRequest = $this->stateHandler->get()->getRequest();
        
        //TODO: check if retrieved authncontext is sufficient
        //$authnContext = $authnRequest->getRequestedAuthnContext()

        $loggedIn = $subject != null;

        $sp = $this->getServiceProvider($authnRequest->getIssuer());
        $outBinding = $this->bindingContainer->get($sp->getAssertionConsumerBinding($authnRequest));
        
        // ensure user is logged in
        if (!$loggedIn) {
            $authnResponse = $this->buildAuthnFailedResponse($authnRequest, Constants::STATUS_AUTHN_FAILED);
        } elseif ($this->stateHandler->get()->getState() === SamlState::STATE_SSO_AUTHENTICATING_FAILED) {
            $authnResponse = $this->buildAuthnFailedResponse($authnRequest, Constants::STATUS_AUTHN_FAILED);
        } else {
            $authnResponse = $this->buildAuthnResponse($authnRequest, $subject);

            $this->stateHandler->get()->addServiceProviderId($sp->getEntityId());
        }

        $this->stateHandler->apply(SamlStateHandler::TRANSITION_SSO_RESPOND);

        event(new SendSAMLResponse($authnResponse));

        // if ($sp->wantSignedAuthnResponse()) {
        // 	$response = $outBinding->getSignedResponse($authnResponse);
        // } else {
        $response = $outBinding->getUnsignedResponse($authnResponse);
        // }
        
        // in any case, reset the state!
        $this->stateHandler->resume(true);

        return $response;
    }

    /**
     *
     * @param Request $httpRequest
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException
     */
    public function processSingleLogoutService(Request $httpRequest)
    {
        $inputBinding = $this->bindingContainer->getByRequestMethod($httpRequest->getMethod());

        try {
            $logoutMessage = $inputBinding->receiveMessage($httpRequest);
        } catch (\Throwable $e) {
            // handle error, apparently the request cannot be processed :(
            $msg = sprintf('Could not process Request, error: "%s"', $e->getMessage());
            Log::critical($msg);

            throw new RuntimeException($msg, 0, $e);
        }

        if ($logoutMessage instanceof \SAML2\LogoutRequest) {
            $this->validateMessage($logoutMessage);

            Log::notice('Received LogoutRequest, started processing');

            $this->stateHandler->resume(true)->apply(SamlStateHandler::TRANSITION_SLS_START);

            $this->stateHandler->get()->setRequest($logoutMessage);

            $this->stateHandler->get()->removeServiceProviderId($logoutMessage->getIssuer());

            return $this->continueSingleLogoutService();
        }

        if ($logoutMessage instanceof \SAML2\LogoutResponse) {
            $this->validateMessage($logoutMessage);

            Log::notice('Received LogoutResponse, continue processing');

            $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_END_PROPAGATE);

            return $this->continueSingleLogoutService();
        }

        throw new InvalidArgumentException(sprintf('The received request is neither a LogoutRequest nor a LogoutResponse, "%s" received instead', substr(get_class($logoutMessage), strrpos($logoutMessage, '_') + 1)));
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    public function continueSingleLogoutService()
    {
        Log::notice('Continue SLS process');

        // TODO: get name id from samle state?
        $user = Auth::user();

        if ($this->stateHandler->can(SamlStateHandler::TRANSITION_SLS_START_DISPATCH)) {
            $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_START_DISPATCH);

            $response = resolve(SAMLConfig::class)->doLogoutResponse();

            if ($response != null) {
                return $response;
            } else {
                //logout is succesful!
                $this->onLogoutSuccess();
            }
        }

        $state = $this->stateHandler->get();

        if ($state->hasServiceProviderIds()) {
            $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_START_PROPAGATE);
            
            // Dispatch logout to service providers
            $sp = $this->serviceProviderRepository->getServiceProvider($state->popServiceProviderIds());
            $logoutRequest = $this->buildLogoutRequest($sp, $user);

            $outBinding = $this->bindingContainer->get($sp->getSingleLogoutBinding());

            Log::notice(sprintf('Propagate logout to sp %s', $sp->getSingleLogoutUrl()));

            if ($sp->wantSignedLogoutRequest()) {
                $response = $outBinding->getSignedRequest($logoutRequest);
            } else {
                $response = $outBinding->getUnsignedRequest($logoutRequest);
            }

            return $response;
        }

        $this->stateHandler->apply(SamlStateHandler::TRANSITION_SLS_RESPOND);

        /** @var \SAML2\LogoutRequest $logoutRequest */
        $logoutRequest = $this->stateHandler->get()->getRequest();

        if ($logoutRequest !== null) {
            $logoutResponse = $this->buildLogoutResponse($logoutRequest);

            $sp = $this->getServiceProvider($logoutRequest->getIssuer());
            $outBinding = $this->bindingContainer->get($sp->getSingleLogoutBinding($logoutRequest));

            Log::notice(sprintf('Logout: Respond to sp initiator %s', $sp->getEntityId()));

            if ($sp->wantSignedLogoutResponse()) {
                $response = $outBinding->getSignedResponse($logoutResponse);
            } else {
                $response = $outBinding->getUnsignedResponse($logoutResponse);
            }
        } else {
            throw new SAMLException("No logoutrequest received ...");
        }

        $this->stateHandler->resume(true);

        Log::notice('Saml: Logout terminated');

        //TODO: return laravel response!

        //new Response
        //$response->headers

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }

    /**
     *
     * @param \SAML2\AuthnRequest $authnRequest
     * @return \SAML2\Response
     * @throws \Exception
     */
    protected function buildAuthnResponse(\SAML2\AuthnRequest $authnRequest, Subject $subject)
    {

        /** @var \ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProvider */
        $serviceProvider = $this->getServiceProvider($authnRequest->getIssuer());

        $nameIdFormat = $serviceProvider->getNameIdFormat($authnRequest);

        $state = $this->stateHandler->get();

        $nameIdValue = $serviceProvider->getNameIdValueForUser($nameIdFormat, $subject);

        $assertionBuilder = new AssertionBuilder();
        $assertionBuilder->setNotOnOrAfter(new \DateInterval('PT5M'))->setSessionNotOnOrAfter(new \DateInterval('P1D'))->setIssuer($this->identityProvider->getEntityId())->setNameId($nameIdValue, $nameIdFormat, $serviceProvider->getNameQualifier(), $authnRequest->getIssuer())->setConfirmationMethod(Constants::CM_BEARER)->setInResponseTo($authnRequest->getId())->setRecipient($serviceProvider->getAssertionConsumerUrl($authnRequest))->setAuthnContext($state->getAuthnContext());

        foreach ($subject->getAttributes($authnRequest) as $attribute=>$value) {
            $assertionBuilder->setAttribute($attribute, $value);
        }

        $assertionBuilder->setAttributesNameFormat(\SAML2\Constants::NAMEFORMAT_UNSPECIFIED);

        
        if ($serviceProvider->wantSignedAssertions()) {
            $assertionBuilder->sign($this->getIdentityProviderXmlPrivateKey(), $this->getIdentityProviderXmlPublicKey());
        }

        $authnResponseBuilder = (new AuthnResponseBuilder())->setStatus(\SAML2\Constants::STATUS_SUCCESS)->setIssuer($this->identityProvider->getEntityId())->setRelayState($authnRequest->getRelayState())->setDestination($serviceProvider->getAssertionConsumerUrl($authnRequest))->addAssertionBuilder($assertionBuilder)->setInResponseTo($authnRequest->getId())->setWantSignedAssertions($serviceProvider->wantSignedAssertions())->setSignatureKey($this->getIdentityProviderXmlPrivateKey());

        $response = $authnResponseBuilder->getResponse();

        $response->setCertificates(
            [
                stripslashes($this->identityProvider->getCertificateData())
            ]
        );

        return $response;
    }

    /**
     *
     * @param \SAML2\AuthnRequest $authnRequest
     * @return \SAML2\Response
     */
    protected function buildAuthnFailedResponse(\SAML2\AuthnRequest $authnRequest, $samlStatus)
    {
        $serviceProvider = $this->getServiceProvider($authnRequest->getIssuer());

        $authnResponseBuilder = new AuthnResponseBuilder();

        return $authnResponseBuilder->setStatus($samlStatus)->setIssuer($this->identityProvider->getEntityId())->setRelayState($authnRequest->getRelayState())->setDestination($serviceProvider->getAssertionConsumerUrl($authnRequest))->setInResponseTo($authnRequest->getId())->setSignatureKey($this->getIdentityProviderXmlPrivateKey())->getResponse();
    }

    /**
     *
     * @param ServiceProvider $serviceProvider
     * @return \SAML2\LogoutRequest
     */
    protected function buildLogoutRequest(ServiceProvider $serviceProvider, $user)
    {
        $logoutRequestBuilder = new LogoutRequestBuilder();
        
        // TODO: get the nameid format for getNameIdValueForUser
        return $logoutRequestBuilder->setNameId($serviceProvider->getNameIdValueForUser(null, $user), \SAML2\Constants::NAMEFORMAT_BASIC)->setIssuer($this->identityProvider->getEntityId())->setDestination($serviceProvider->getSingleLogoutUrl())->setSignatureKey($this->getIdentityProviderXmlPrivateKey())->getRequest();
    }

    /**
     *
     * @param \SAML2\LogoutRequest $logoutRequest
     * @return \SAML2\LogoutResponse
     */
    protected function buildLogoutResponse(\SAML2\LogoutRequest $logoutRequest)
    {
        $serviceProvider = $this->getServiceProvider($logoutRequest->getIssuer());

        $logoutResponseBuilder = new LogoutResponseBuilder();

        return $logoutResponseBuilder->setInResponseTo($logoutRequest->getId())->setDestination($serviceProvider->getSingleLogoutUrl())->setIssuer($this->identityProvider->getEntityId())->setSignatureKey($this->getIdentityProviderXmlPrivateKey())->setStatus(\SAML2\Constants::STATUS_SUCCESS)->setRelayState($logoutRequest->getRelayState())->getResponse();
    }

    /**
     *
     * @param
     *        	$entityId
     * @return ServiceProvider
     */
    protected function getServiceProvider($entityId)
    {
        $result = $this->serviceProviderRepository->getServiceProvider($entityId);

        if ($result == null) {
            throw new SAMLException('Unknown service provider: ' . $entityId);
        }

        return $result;
    }

    /**
     *
     * @return \XMLSecurityKey
     */
    protected function getIdentityProviderXmlPrivateKey()
    {
        /** @var \SAML2\Certificate\PrivateKey $privateKey */
        $privateKey = $this->identityProvider->getPrivateKey(\SAML2\Configuration\PrivateKey::NAME_DEFAULT);

        $xmlPrivateKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, [
            'type' => 'private'
        ]);

        $xmlPrivateKey->loadKey(Helper::cleanPrivateKey($privateKey));

        return $xmlPrivateKey;
    }

    /**
     *
     * @return \XMLSecurityKey
     */
    protected function getIdentityProviderXmlPublicKey()
    {
        $publicFileCert = stripslashes($this->identityProvider->getCertificateData());

        // die($publicFileCert);
        $xmlPublicKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, [
            'type' => 'public'
        ]);
        
        $publicFileCert = Helper::cleanCertificateKey($publicFileCert);
        
        $xmlPublicKey->loadKey($publicFileCert, false, true);

        return $xmlPublicKey;
    }

    /**
     *
     * @param \ArieTimmerman\Laravel\SAML\SAML2\Binding\Message $message
     */
    protected function validateMessage(\SAML2\Message $message)
    {
        $serviceProvider = $this->getServiceProvider($message->getIssuer());
        
        Log::debug(sprintf('Extracting public keys for ServiceProvider "%s"', $serviceProvider->getEntityId()));
        
        $keys = (new \SAML2\Certificate\KeyLoader())->extractPublicKeys($serviceProvider);

        Log::debug(sprintf('Found "%d" keys, filtering the keys to get X509 keys', $keys->count()));

        //Select the keys
        $x509Keys = $keys->filter(function (\SAML2\Certificate\Key $key) use ($message) {
            return $key instanceof \SAML2\Certificate\X509 && $key['signing'] && (empty($message->getCertificates()) || in_array($key['X509Certificate'], $message->getCertificates()));
        });

        if ($message instanceof AuthnRequest) {
            if ($serviceProvider->wantSignedAuthnRequest() && count($x509Keys) == 0) {
                throw new SAMLException('The serviceprovider should sign authentication requests but no certificates have been found.');
            }

            if ($serviceProvider->wantSignedAuthnRequest() && !$message->isMessageConstructedWithSignature()) {
                throw new SAMLException('Received an unsigned AuthnRequest while the serviceprovider wants signed authnentication requests.');
            }
        } elseif ($message instanceof LogoutRequest) {
            if ($serviceProvider->wantSignedLogoutRequest() && count($x509Keys) == 0) {
                throw new SAMLException('The serviceprovider should sign logout requests but no certificates have been found.');
            }

            if ($serviceProvider->wantSignedLogoutRequest() && !$message->isMessageConstructedWithSignature()) {
                throw new SAMLException('Received an unsigned logout request while the serviceprovider wants signed logout requests.');
            }
        } elseif ($message instanceof \SAML2\LogoutResponse) {
            if ($serviceProvider->wantSignedLogoutResponse() && count($x509Keys) == 0) {
                throw new SAMLException('The serviceprovider should sign logout response but no certificates have been found.');
            }

            if ($serviceProvider->wantSignedLogoutResponse() && !$message->isMessageConstructedWithSignature()) {
                throw new SAMLException('Received an unsigned logout response while the serviceprovider wants signed logout response.');
            }
        }

        Log::debug(sprintf('Found "%d" X509 keys, attempting to use each for signature verification', $x509Keys->count()));
        
        /** @var \SAML2\Certificate\X509[] $x509Keys */
        foreach ($x509Keys as $x509Key) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array(
                'type' => 'public'
            ));

            $key->loadKey($x509Key->getCertificate());
            $message->validate($key);
        }
    }
}
