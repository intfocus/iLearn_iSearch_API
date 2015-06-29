<?php

    $spBaseUrl = 'https://tsa-china.takeda.com.cn/uat/saml'; //or http://<your_domain>

    $settingsInfo = array (
        'sp' => array (
            //'entityId' => $spBaseUrl.'/sp/metadata.php',
            'entityId' => 'IntFocus_tsa-china',
            'assertionConsumerService' => array (
                'url' => $spBaseUrl.'/sp/index.php?acs',
            ),
            'singleLogoutService' => array (
                'url' => $spBaseUrl.'/sp/index.php?sls',
            ),
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified',
		
        ),
        'idp' => array (
            'entityId' => 'Takeda_Webssodev',
            'singleSignOnService' => array (
                'url' => 'https://webssodev.secureaccess.takeda.com/idp/SSO.saml2?PartnerSpId=IntFocus_tsa-china',
            ),
            'singleLogoutService' => array (
                'url' => 'https://webssodev.secureaccess.takeda.com/idp/SLO.saml2',
            ),
            'x509cert' => 'MIICazCCAdSgAwIBAgIGAUemhEfOMA0GCSqGSIb3DQEBBQUAMHkxCzAJBgNVBAYTAkNIMQ8wDQYDVQQHEwZadXJpY2gxLTArBgNVBAoTJFRha2VkYSBQaGFybWFjZXV0aWNhbHMgSW50ZXJuYXRpb25hbDEMMAoGA1UECxMDQ0NPMRwwGgYDVQQDDBNzaWduYXR1cmVfd2Vic3NvZGV2MB4XDTE0MDgwNTE0MTMxMloXDTE3MDgwNDE0MTMxMloweTELMAkGA1UEBhMCQ0gxDzANBgNVBAcTBlp1cmljaDEtMCsGA1UEChMkVGFrZWRhIFBoYXJtYWNldXRpY2FscyBJbnRlcm5hdGlvbmFsMQwwCgYDVQQLEwNDQ08xHDAaBgNVBAMME3NpZ25hdHVyZV93ZWJzc29kZXYwgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAJNBrA3GX/gDv6/YTOV1hN5zVGETzaAgXO8YJeCTAnQ+4bYdeFAjZNC6nvUILPChKGmlEZd88ZOzHSA3hpFD77QXGAAT9S06DVm4Oa664/JLjmmh7XQQXkeplla+4cDcBm+sPG9pZoEDeKwAHS7JKVGaX7uQRIONKSMf69Qp19g9AgMBAAEwDQYJKoZIhvcNAQEFBQADgYEAU7mpmuaLZYQ8UWJ7ZtwX3QPGWHugjFO5Zc+XCZIDGA8VCE+iWj2wE9WfGs8c9EheZ5KFu3kStBb3xhZPqV3sXLsQGR/0lvte6fDZpc/pBOg7z0qWKF4TRH/pQHiuIitLaVPT2Xv7kkACHjvIl8OgsfALK40QH10oD1EOJbH3H3s=',
        ),
    );
