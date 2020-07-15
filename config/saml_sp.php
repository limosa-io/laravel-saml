<?php

use ArieTimmerman\Laravel\SAML\SAML2\Constants;

return [

  '  https://example-sp.test/simplesaml/module.php/saml/sp/metadata.php/default-sp' => array(

    'entityid' => '  https://example-sp.test/simplesaml/module.php/saml/sp/metadata.php/default-sp',
    'contacts' =>
      array(),
    'metadata-set' => 'saml20-sp-remote',
    'AssertionConsumerService' =>
      array(
      0 =>
        array(
        'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        'Location' => '  https://example-sp.test/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
        'index' => 0,
      ),
      1 =>
        array(
        'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
        'Location' => '  https://example-sp.test/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp',
        'index' => 1,
      ),
      2 =>
        array(
        'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
        'Location' => '  https://example-sp.test/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
        'index' => 2,
      ),
      3 =>
        array(
        'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
        'Location' => '  https://example-sp.test/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp/artifact',
        'index' => 3,
      ),
    ),
    'SingleLogoutService' =>
      array(
      0 =>
        array(
        'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        'Location' => '  https://example-sp.test/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
      ),
    ),

    'keys' =>
      array(
      0 =>
        array(
        'encryption' => false,
        'signing' => true,
        'type' => 'X509Certificate',
        'X509Certificate' => 'MIICOjCCAaOgAwIBAgIBADANBgkqhkiG9w0BAQ0FADA6MQswCQYDVQQGEwJ1czENMAsGA1UECAwEdGVzdDENMAsGA1UECgwEdGVzdDENMAsGA1UEAwwEdGVzdDAeFw0xODAxMjMxNzMwMDNaFw0xOTAxMjMxNzMwMDNaMDoxCzAJBgNVBAYTAnVzMQ0wCwYDVQQIDAR0ZXN0MQ0wCwYDVQQKDAR0ZXN0MQ0wCwYDVQQDDAR0ZXN0MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDYDf0usuLbdQpggdqv/NUndbf1RoZRU2/aiVNS2F6M8kVs2ueGKltJfSk98nljMT7XyN1hfDbWOzPx32Zqlkaq1hae11gYiDKIL8I5XUMw6aXju6e4VvC53PVilwfvuMiF0PvlL0T+FhTDNBN87FhKM5MTjPuvwLjld07OfrifEQIDAQABo1AwTjAdBgNVHQ4EFgQUw+slkgyuCw+nFYi3W3AL2JGw8wwwHwYDVR0jBBgwFoAUw+slkgyuCw+nFYi3W3AL2JGw8wwwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQ0FAAOBgQAxTIKPF4HLnq+ZRj0m0E85E4Vr6tHcEgOgqjeYomJIamGtJ1FdwrCqZNheMaO9By76aUtu7MerSwta3RUdyULPGClVQapPQ8GdcBKYAyqknwnxBF7U3F2VClwjR9CfyHIoDvtvZJkfIBtRFbyeQaCGnY3GGYky5WJAat/HMBYnKA==',
      ),
      1 =>
        array(
        'encryption' => true,
        'signing' => false,
        'type' => 'X509Certificate',
        'X509Certificate' => 'MIICOjCCAaOgAwIBAgIBADANBgkqhkiG9w0BAQ0FADA6MQswCQYDVQQGEwJ1czENMAsGA1UECAwEdGVzdDENMAsGA1UECgwEdGVzdDENMAsGA1UEAwwEdGVzdDAeFw0xODAxMjMxNzMwMDNaFw0xOTAxMjMxNzMwMDNaMDoxCzAJBgNVBAYTAnVzMQ0wCwYDVQQIDAR0ZXN0MQ0wCwYDVQQKDAR0ZXN0MQ0wCwYDVQQDDAR0ZXN0MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDYDf0usuLbdQpggdqv/NUndbf1RoZRU2/aiVNS2F6M8kVs2ueGKltJfSk98nljMT7XyN1hfDbWOzPx32Zqlkaq1hae11gYiDKIL8I5XUMw6aXju6e4VvC53PVilwfvuMiF0PvlL0T+FhTDNBN87FhKM5MTjPuvwLjld07OfrifEQIDAQABo1AwTjAdBgNVHQ4EFgQUw+slkgyuCw+nFYi3W3AL2JGw8wwwHwYDVR0jBBgwFoAUw+slkgyuCw+nFYi3W3AL2JGw8wwwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQ0FAAOBgQAxTIKPF4HLnq+ZRj0m0E85E4Vr6tHcEgOgqjeYomJIamGtJ1FdwrCqZNheMaO9By76aUtu7MerSwta3RUdyULPGClVQapPQ8GdcBKYAyqknwnxBF7U3F2VClwjR9CfyHIoDvtvZJkfIBtRFbyeQaCGnY3GGYky5WJAat/HMBYnKA==',
      ),
    ),

  )

];