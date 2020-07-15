<?php
/**
 * Created by PhpStorm.
 * User: moroine
 * Date: 14/08/17
 * Time: 16:28
 */

namespace ArieTimmerman\Laravel\SAML\SAML2;

class Constants extends \SAML2\Constants
{

    /**
     * Password protected transport authentication context.
     */
    const AC_USERNAME_PASSWORD = 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password';
    const AC_PASSWORD_PROTECTED_TRANSPORT = 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport';
    const AC_TLS_CLIENT = 'urn:oasis:names:tc:SAML:2.0:ac:classes:TLSClient';
    const AC_X509 = 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509';
    const AC_WINDOWS = 'urn:federation:authentication:windows';
    const AC_KERBEROS = 'urn:oasis:names:tc:SAML:2.0:ac:classes:Kerberos';
    const AC_PREVIOUS_SESSION = 'urn:oasis:names:tc:SAML:2.0:ac:classes:PreviousSession';
}
