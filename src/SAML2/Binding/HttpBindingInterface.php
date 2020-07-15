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

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface HttpBindingInterface
{
    /**
     * @param \SAML2\StatusResponse $response
     * @return Response
     */
    public function getSignedResponse(\SAML2\StatusResponse $response);

    /**
     * @param \SAML2\StatusResponse $response
     * @return Response
     */
    public function getUnsignedResponse(\SAML2\StatusResponse $response);

    /**
     * @param \SAML2\Request $request
     * @return Response
     */
    public function getSignedRequest(\SAML2\Request $request);

    /**
     * @param \SAML2\Request $request
     * @return Response
     */
    public function getUnsignedRequest(\SAML2\Request $request);
    
    /**
     * @param Request $request
     * @return \SAML2\LogoutRequest
     */
    public function receiveLogoutRequest(Request $request);

    /**
     * @param Request $request
     * @return \SAML2\LogoutResponse
     */
    public function receiveLogoutResponse(Request $request);

    /**
     * @param Request $request
     * @return \SAML2\AuthnRequest
     */
    public function receiveAuthnRequest(Request $request);

    /**
     * @param Request $request
     * @return Message
     */
    public function receiveMessage(Request $request);
    

}
