<?php

use ArieTimmerman\Laravel\SAML\SAML2\Constants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

return [

  //automatically try to connect with service providers
  'autoLearning' => true,

  'config' => 'ArieTimmerman\Laravel\SAML\Config\SimpleConfig',

  'useTestLogin' => true,

  'metadata.sign.algorithm' => XMLSecurityKey::RSA_SHA256,
  'metadata_key_private' => file_get_contents(dirname(dirname(__FILE__)) . '/private.key'),
  'metadata_key_public' => file_get_contents(dirname(dirname(__FILE__)) . '/public.key'),

  'idp' => [

    'PreviousSession' => Constants::AC_PREVIOUS_SESSION,

    //
    'entityId' => 'test123',
    'expire' => time() + 3600,
    'cacheDuration' => 3600, // in seconds
    'sign.authnrequest' => true,
    'metadata.sign.enable' => true,
    'redirect.sign' => true,

    //single sign on
    'ssoHttpPostEnabled' => true,
    'ssoHttpRedirectEnabled' => true,

    //single log out
    'sloHttpPostEnabled' => true,
    'sloHttpRedirectEnabled' => true,

    // key pairs
    'keys' => [
      [
        'type' => 'X509Certificate',
        'signing' => true,
        'encryption' => false,

        // 'X509Certificate' MUST contain the certificate without the "-----BEGIN CERTIFICATE-----" and "-----END CERTIFICATE-----" parts.
        'X509Certificate' => file_get_contents(dirname(dirname(__FILE__)) . '/public.key'),
        'private' => file_get_contents(dirname(dirname(__FILE__)) . '/private.key'),
      ]
    ],

    // The list of
    'supportedNameIDFormat' => [
      Constants::NAMEID_PERSISTENT,
      Constants::NAMEID_TRANSIENT
    ],

    'contacts' => [
      [
        'emailAddress' => 'someonase@example.com',
        'name' => 'John Doe',
        'contactType' => 'technical'
      ]
    ],

    'organization' => [
      'OrganizationName' => 'Example',
      'OrganizationDisplayName' => 'Example Organization',
      'OrganizationURL' => 'https://www.example.com'
    ]

  ]

];
