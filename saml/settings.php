<?php

$settings = array (
    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => false,

    // Enable debug mode (to print errors)
    'debug' => false,

    // Service Provider Data that we are deploying
    'sp' => array (
        // Identifier of the SP entity  (must be a URI)
        'entityId' => 'IntFocus_tsa-china',
        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array (
            // URL Location where the <Response> from the IdP will be returned
            'url' => 'https://tsa-china.takeda.com.cn/uat/saml/sp/index.php',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        'singleLogoutService' => array (
            // URL Location where the <Response> from the IdP will be returned
            'url' => 'https://tsa-china.takeda.com.cn/uat/saml/sp/slo.php',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => '',
        'privateKey' => '',
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array (
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => 'Takeda_Webssodev',
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array (
            // URL Target of the IdP where the SP will send the Authentication Request Message
            'url' => 'https://webssodev.secureaccess.takeda.com/idp/SSO.saml2?PartnerSpId=IntFocus_tsa-china',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-POST binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array (
            // URL Location of the IdP where the SP will send the SLO Request
            'url' => 'https://webssodev.secureaccess.takeda.com/idp/SLO.saml2',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Public x509 certificate of the IdP
        'x509cert' => 'MIICazCCAdSgAwIBAgIGAUemhEfOMA0GCSqGSIb3DQEBBQUAMHkxCzAJBgNVBAYTAkNIMQ8wDQYDVQQHEwZadXJpY2gxLTArBgNVBAoTJFRha2VkYSBQaGFybWFjZXV0aWNhbHMgSW50ZXJuYXRpb25hbDEMMAoGA1UECxMDQ0NPMRwwGgYDVQQDDBNzaWduYXR1cmVfd2Vic3NvZGV2MB4XDTE0MDgwNTE0MTMxMloXDTE3MDgwNDE0MTMxMloweTELMAkGA1UEBhMCQ0gxDzANBgNVBAcTBlp1cmljaDEtMCsGA1UEChMkVGFrZWRhIFBoYXJtYWNldXRpY2FscyBJbnRlcm5hdGlvbmFsMQwwCgYDVQQLEwNDQ08xHDAaBgNVBAMME3NpZ25hdHVyZV93ZWJzc29kZXYwgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAJNBrA3GX/gDv6/YTOV1hN5zVGETzaAgXO8YJeCTAnQ+4bYdeFAjZNC6nvUILPChKGmlEZd88ZOzHSA3hpFD77QXGAAT9S06DVm4Oa664/JLjmmh7XQQXkeplla+4cDcBm+sPG9pZoEDeKwAHS7JKVGaX7uQRIONKSMf69Qp19g9AgMBAAEwDQYJKoZIhvcNAQEFBQADgYEAU7mpmuaLZYQ8UWJ7ZtwX3QPGWHugjFO5Zc+XCZIDGA8VCE+iWj2wE9WfGs8c9EheZ5KFu3kStBb3xhZPqV3sXLsQGR/0lvte6fDZpc/pBOg7z0qWKF4TRH/pQHiuIitLaVPT2Xv7kkACHjvIl8OgsfALK40QH10oD1EOJbH3H3s=',
        /*
         *  Instead of use the whole x509cert you can use a fingerprint
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
         *   or add for example the -sha256 , -sha384 or -sha512 parameter)
         *
         *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
         *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
         *  'sha1' is the default value.
         */
        // 'certFingerprint' => '',
        // 'certFingerprintAlgorithm' => 'sha1',
    ),
);
