<?php

    $spBaseUrl = 'https://tsa-china.takeda.com.cn/uat/saml'; //or http://<your_domain>

    $settingsInfo = array (
        'sp' => array (
            //'entityId' => $spBaseUrl.'/sp/metadata.php',
            'entityId' => 'IntFocus_tsa-china_app',
            'assertionConsumerService' => array (
                'url' => $spBaseUrl.'/spapp/index.php?acs',
            ),
            'singleLogoutService' => array (
                'url' => $spBaseUrl.'/spapp/index.php?sls',
            ),
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
		
        ),
        'idp' => array (
            'entityId' => 'Takeda_Websso',
            'singleSignOnService' => array (
                'url' => 'https://websso.secureaccess.takeda.com/idp/startSSO.ping?PartnerSpId=IntFocus_tsa-china_app',
            ),
            'singleLogoutService' => array (
                'url' => 'https://websso.secureaccess.takeda.com/idp/SLO.saml2',
            ),
            'x509cert' => 'MIIClzCCAgCgAwIBAgIGAVNgAmT0MA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJDSDEPMA0GA1UEBxMGWnVyaWNoMS0wKwYDVQQKEyRUYWtlZGEgUGhhcm1hY2V1dGljYWxzIEludGVybmF0aW9uYWwxDDAKBgNVBAsTA0NDTzExMC8GA1UEAwwoc2lnbmF0dXJlX3dlYnNzby5zZWN1cmVhY2Nlc3MudGFrZWRhLmNvbTAeFw0xNjAzMTAxMDExMjJaFw0xOTAzMTAxMDExMjJaMIGOMQswCQYDVQQGEwJDSDEPMA0GA1UEBxMGWnVyaWNoMS0wKwYDVQQKEyRUYWtlZGEgUGhhcm1hY2V1dGljYWxzIEludGVybmF0aW9uYWwxDDAKBgNVBAsTA0NDTzExMC8GA1UEAwwoc2lnbmF0dXJlX3dlYnNzby5zZWN1cmVhY2Nlc3MudGFrZWRhLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAyYufjCOl5OzjGxoADLAvzW65KfI+kqwYlg29UK2yaozxJsXEV8fNaSDQK40UtasoAqltBQzMuly8jQOUV/nsuCVpud/n1tlhEsyMXoPBOZ8qiqDbgIkkrEuPr1pZkhRmXC4cGQWPrqTocnA6fJpL9FnIzXATS7x38ssJp0YvAqECAwEAATANBgkqhkiG9w0BAQUFAAOBgQCZr+wro0CjZDMvPnDIQa2b9MomyRD1hlTZDc2k69rh2mUsmRSwe886dOh7T1AXwYoOLUH+oz+qY+4ELvId/G5IPhq1Y5t+c8rL0PpISH60RSBl7t/Yn/ueOX8YdBMBAXAitqSn/tE9qztW4KkY3cBMR+yJ1orrO1m3Ghr4Zzv6+Q==',
        ),
    );
