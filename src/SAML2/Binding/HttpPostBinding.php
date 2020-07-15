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
use ArieTimmerman\Laravel\SAML\Exceptions\LogicException;
use ArieTimmerman\Laravel\SAML\Exceptions\UnsupportedBindingException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class HttpPostBinding extends AbstractHttpBinding
{

   
    /**
     * HttpPostBinding constructor.
     * @param EngineInterface $templateEngine
     */
    public function __construct()
    {
    }

    /**
     * @param \SAML2\StatusResponse $response
     * @return Response
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \RuntimeException
     */
    public function getSignedResponse(\SAML2\StatusResponse $response)
    {
        return $this->getSignedResponseForm($response);
    }

    /**
     * @param \SAML2\StatusResponse $response
     * @return Response
     * @throws \RuntimeException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function getUnsignedResponse(\SAML2\StatusResponse $response)
    {
        return $this->getUnsignedResponseForm($response);
    }

    /**
     * @param \SAML2\Request $request
     * @return Response
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\UnsupportedBindingException
     */
    public function getUnsignedRequest(\SAML2\Request $request)
    {
        throw new UnsupportedBindingException("Unsupported binding: unsigned POST Request is not supported at the moment");
    }

    /**
     * @param Request $request
     * @return ReceivedData
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\InvalidReceivedMessageQueryStringException
     * @throws \ArieTimmerman\Laravel\SAML\Exceptions\BadRequestHttpException
     */
    protected function getReceivedData(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            throw new BadRequestHttpException(sprintf(
                'Could not receive Message from HTTP Request: expected a POST method, got %s',
                $request->getMethod()
            ));
        }

        $requestParams = $request->all();

        return ReceivedData::fromReceivedProviderData($requestParams);
    }

    /**
     * @param \SAML2\StatusResponse $response
     * @return \Symfony\Component\Form\FormInterface
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function getSignedResponseForm(\SAML2\StatusResponse $response)
    {
        return $this->getResponseForm($response, true);
    }

    /**
     * @param \SAML2\StatusResponse $response
     * @return \Symfony\Component\Form\FormInterface
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function getUnsignedResponseForm(\SAML2\StatusResponse $response)
    {
        return $this->getResponseForm($response, false);
    }

    /**
     * @param \SAML2\StatusResponse $response
     * @param $isSign
     * @return \Symfony\Component\Form\FormInterface
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function getResponseForm(\SAML2\StatusResponse $response, $isSign)
    {
        if ($response->getDestination() === null) {
            throw new LogicException('Invalid destination');
        }

        $xmlDom = $isSign ? $response->toSignedXML() : $response->toUnsignedXML();

        $data = [
            'samlResponse' => base64_encode($xmlDom->ownerDocument->saveXML()),
        ];

        $hasRelayState = !empty($response->getRelayState());
        if ($hasRelayState) {
            $data["relayState"] = $response->getRelayState();
        }

        $data['destination'] = $response->getDestination();
        
        return view('saml::dopost', $data);
    }

    /**
     * @param string $destination
     * @param string $encodedRequest
     * @param string $relayState
     * @param XMLSecurityKey $signatureKey
     * @return Response
     */
    protected function buildRequest($destination, $encodedRequest, $relayState, XMLSecurityKey $signatureKey)
    {
        throw new UnsupportedBindingException("Unsupported binding: build POST Request is not supported at the moment");
    }
}
