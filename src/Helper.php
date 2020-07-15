<?php

namespace ArieTimmerman\Laravel\SAML;

use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use ArieTimmerman\Laravel\SAML\Config\Config;
use SimpleSAML\Configuration;
use SimpleSAML\Metadata\SAMLParser;

class Helper
{
    private static $state = null;

    public static function getSAMLStateOrFail()
    {
        return self::getSAMLState();
    }
    public static function getSAMLState()
    {
        if (self::$state == null) {
            $states = request()->session()->get('samlState');

            if ($states != null && count($states) > 0) {
                self::$state = unserialize(end($states));
            } else {
                self::$state = new SamlState();
            }
        }

        return self::$state;
    }

    public static function saveSAMLState($request = null)
    {
        if (self::$state) {
            if ($request == null) {
                $request = \request();
            }

            $request->session()->put('samlState.' . self::$state->id, serialize(self::$state));
        }
    }

    public static function parseMetaData($file)
    {
        Configuration::loadFromArray([], '[ARRAY]', 'simplesaml');

        $entities = SAMLParser::parseDescriptorsFile($file);

        // get all metadata for the entities
        foreach ($entities as &$entity) {
            $entity = array(
                'shib13-sp-remote' => $entity->getMetadata1xSP(),
                'shib13-idp-remote' => $entity->getMetadata1xIdP(),
                'saml20-sp-remote' => $entity->getMetadata20SP(),
                'saml20-idp-remote' => $entity->getMetadata20IdP()
            );
        }

        // transpose from $entities[entityid][type] to $output[type][entityid]
        $output = \SimpleSAML\Utils\Arrays::transpose($entities);

        // merge all metadata of each type to a single string which should be added to the corresponding file
        if (isset($output['saml20-sp-remote'])) {
            foreach ($output['saml20-sp-remote'] as $entityId => &$entityMetadata) {
                if ($entityMetadata === null) {
                    continue;
                }

                // remove the entityDescriptor element because it is unused, and only makes the output harder to read
                unset($entityMetadata['entityDescriptor']);

                $entities[$entityId] = $entityMetadata;
            }
        }

        return $entities;
    }

    public static function cleanPrivateKey($key)
    {
        $key = str_replace("-----BEGIN PRIVATE KEY-----", "", $key);
        $key = str_replace("-----END PRIVATE KEY-----", "", $key);
        $key = str_replace("\n", "", $key);
        $key = str_replace("\r", "", $key);

        return "-----BEGIN PRIVATE KEY-----\n" . $key . "\n-----END PRIVATE KEY-----\n";
    }

    public static function cleanCertificateKey($key)
    {
        $x509cert = $key;

        $x509cert = str_replace(array("-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\r", "\n", " ", "\t"), "", $x509cert);
        $x509cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($x509cert, 64, "\n") . "-----END CERTIFICATE-----\n";

        return $x509cert;
    }

    public static function getCertificateContents($certificate)
    {
        $result = null;

        preg_match('/-----BEGIN CERTIFICATE-----(.*?)-----END CERTIFICATE-----/ms', $certificate, $matches);

        if (!empty($matches)) {
            $result = $matches[1];
        } else {
            $result = $certificate;
        }

        return $result;
    }

    public static function signMetadata($xml)
    {
        $algoritm = XMLSecurityKey::RSA_SHA256;
        $digest = XMLSecurityDSig::SHA256;

        // load the private key
        $objKey = new XMLSecurityKey($algoritm, array('type' => 'private'));
        //         if (array_key_exists('privatekey_pass', $keyCertFiles)) {
        //             $objKey->passphrase = $keyCertFiles['privatekey_pass'];
        //         }
        $objKey->loadKey(Helper::cleanPrivateKey(Config::getInstance()->get('saml.metadata_key_private'), false));

        // get the EntityDescriptor node we should sign
        $rootNode = $xml->firstChild;

        //         if ($type == 'ADFS IdP') $objXMLSecDSig = new sspmod_adfs_XMLSecurityDSig($metadataString);

        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        $objXMLSecDSig->addReferenceList(
            [$rootNode],
            $digest,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N],
            ['id_name' => 'ID']
        );

        $objXMLSecDSig->sign($objKey);

        $objXMLSecDSig->add509Cert("-----BEGIN CERTIFICATE-----\n" . self::getCertificateContents(Config::getInstance()->get('saml.metadata_key_public')) . "\n-----END CERTIFICATE-----\n", true);

        // add the signature to the metadata
        $objXMLSecDSig->insertSignature($rootNode, $rootNode->firstChild);

        // return the DOM tree as a string
        return $xml->saveXML();
    }
}
