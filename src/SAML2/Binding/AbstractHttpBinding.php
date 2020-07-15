<?php

/**
 * Copyright 2017 Adactive SAS
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

namespace ArieTimmerman\Laravel\SAML\SAML2\Binding;

use ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException;
use ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException;
use ArieTimmerman\Laravel\SAML\Exceptions\LogicException;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ArieTimmerman\Laravel\SAML\Exceptions\SAMLException;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Message;

abstract class AbstractHttpBinding implements HttpBindingInterface
{
    /**
     * Validate the signature.
     *
     * Throws an exception if we are unable to validate the signature.
     *
     * @param ReceivedData $query g.
     * @param XMLSecurityKey $key The key we should validate the query against.
     * @throws BadRequestHttpException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\LogicException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\RuntimeException
     */
    public static function validateSignature(ReceivedData $query, XMLSecurityKey $key)
    {
        $algo = urldecode($query->getSignatureAlgorithm());

        if ($key->getAlgorithm() !== $algo) {
            $key = \SAML2\Utils::castKey($key, $algo);
        }

        if (!$key->verifySignature($query->getSignedQueryString(), $query->getDecodedSignature())) {
            throw new BadRequestHttpException(
                'The SAMLRequest has been signed, but the signature could not be validated'
            );
        }
    }

    /**
     * @param \SAML2\Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\LogicException
     */
    public function getSignedRequest(\SAML2\Request $request)
    {
        $destination = $request->getDestination();
        if($destination === null){
            throw new LogicException('Invalid destination');
        }

        $securityKey = $request->getSignatureKey();
        if($securityKey === null){
            throw new LogicException('Signature key is required');
        }

        $requestAsXml = $request->toUnsignedXML()->ownerDocument->saveXML();
        $encodedRequest = base64_encode(gzdeflate($requestAsXml));
        $relayState = $request->getRelayState();

        return $this->buildRequest($destination, $encodedRequest, $relayState, $request->getSignatureKey());
    }

    /**
     * @param string $destination
     * @param string $encodedRequest
     * @param string $relayState
     * @param XMLSecurityKey $signatureKey
     * @return Response
     */
    abstract protected function buildRequest($destination, $encodedRequest, $relayState, XMLSecurityKey $signatureKey);

    

    /**
     * @param Request $request
     * @return \SAML2\LogoutRequest
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException
     */
    public function receiveLogoutRequest(Request $request){
        $message = $this->receiveMessage($request);

        if (!$message instanceof \SAML2\LogoutRequest) {
            throw new InvalidArgumentException(sprintf(
                'The received request is not an LogoutRequest, "%s" received instead',
                substr(get_class($message), strrpos($message, '_') + 1)
            ));
        }

        return $message;
    }

    /**
     * @param Request $request
     * @return \SAML2\LogoutResponse
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException
     */
    public function receiveLogoutResponse(Request $request){
        $message = $this->receiveMessage($request);

        if (!$message instanceof \SAML2\LogoutResponse) {
            throw new InvalidArgumentException(sprintf(
                'The received request is not an LogoutRequest, "%s" received instead',
                substr(get_class($message), strrpos($message, '_') + 1)
            ));
        }

        return $message;
    }

    /**
     * @param Request $request
     * @return \SAML2\AuthnRequest
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException
     */
    public function receiveAuthnRequest(Request $request){
    	
        $message = $this->receiveMessage($request);

        if (!$message instanceof \SAML2\AuthnRequest) {
            throw new SAMLException(sprintf(
                'The received request is not an AuthnRequest, "%s" received instead',
                substr(get_class($message), strrpos($message, '_') + 1)
            ));
        }

        return $message;
    }    

    /**
     * @param Request $request
     * @return Message
     */
    public function receiveMessage(Request $request)
    {
    	$data = $this->getReceivedData($request);
        $message = $this->getReceivedSamlMessageFromRecivedData($data, $request);
    	
    	if ($data->isSigned()) {
    		$message->addValidator(array(get_class($this), 'validateSignature'), $data);
    	}
    	
    	return $message;
    }

    /**
     * @param Request $request
     * @return ReceivedData
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidReceivedMessageQueryStringException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException
     */
    abstract protected function getReceivedData(Request $request);


    /**
     * @param ReceivedData $query
     * @param Request $request
     * @return Message
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidArgumentException
     */
    protected function getReceivedSamlMessageFromRecivedData(ReceivedData $query, Request $request)
    {
        $decodedSamlRequest = $query->getDecodedSamlRequest();

        if (!is_string($decodedSamlRequest) || empty($decodedSamlRequest)) {
            throw new InvalidArgumentException(sprintf(
                'Could not create ReceivedMessage: expected a non-empty string, received %s',
                is_object($decodedSamlRequest) ? get_class($decodedSamlRequest) : ($decodedSamlRequest)
            ));
        }
        
        // additional security against XXE Processing vulnerability
        $document = \SAML2\DOMDocumentFactory::fromString($decodedSamlRequest);
        
        $message = Message::fromXML($document->firstChild);

        if (null === $message->getRelayState()) {
            $message->setRelayState($query->getRelayState());
        }

        $currentUri = $this->getFullRequestUri($request);
        
        if (!$message->getDestination() === $currentUri) {
            throw new BadRequestHttpException(sprintf(
                'Actual Destination "%s" does not match the Request Destination "%s"',
                $currentUri,
                $message->getDestination()
            ));
        }

        return $message;
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getFullRequestUri(Request $request)
    {
        return $request->getSchemeAndHttpHost() . $request->getBasePath() . $request->getRequestUri();
    }
}
