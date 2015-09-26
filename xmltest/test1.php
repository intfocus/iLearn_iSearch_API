<?php
$xml  =  '<example xmlns:foo="my.foo.urn">
  <foo:a>Apple</foo:a>
  <foo:b>Banana</foo:b>
  <c>Cherry</c>
</example>' ;

 $sxe  = new  SimpleXMLElement ( $xml );
 
 $kids  =  $sxe -> children ( 'foo:a' );
 var_dump ( count ( $kids ));

 $kids  =  $sxe -> children ( 'foo' );
 var_dump ( count ( $kids ));

 $kids  =  $sxe -> children ( 'foo' ,  TRUE );
 var_dump ( count ( $kids ));

 $kids  =  $sxe -> children ( 'my.foo.urn' );
 var_dump ( count ( $kids ));

 $kids  =  $sxe -> children ( 'my.foo.urn' ,  TRUE );
 var_dump ( count ( $kids ));

 $kids  =  $sxe -> children ();
 var_dump ( count ( $kids ));
 ?> 
 

<?php

$xml  = '<chap:book xmlns:chap="http://example.org/chapter-title">
    <chap:title>My Book</chap:title>
    <chap:chapter id="1">
        <chap:title>Chapter 11</chap:title>
        <para>Donec velit. Nullam eget tellus vitae tortor gravida scelerisque. 
            In orci lorem, cursus imperdiet, ultricies non, hendrerit et, orci. 
            Nulla facilisi. Nullam velit nisl, laoreet id, condimentum ut, 
            ultricies id, mauris.</para>
    </chap:chapter>
    <chap:chapter id="2">
        <chap:title>Chapter 22</chap:title>
        <chap:para>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin 
            gravida. Phasellus tincidunt massa vel urna. Proin adipiscing quam 
            vitae odio. Sed dictum. Ut tincidunt lorem ac lorem. Duis eros 
            tellus, pharetra id, faucibus eu, dapibus dictum, odio.</chap:para>
    </chap:chapter>
</chap:book>';

$xml = '<samlp:Response InResponseTo="s248735275d5542177e9a4fd021410177660b9c8be"
                IssueInstant="2015-05-26T08:36:27.858Z"
                ID="QFZ2__JbROWHHYvZu451rcjTMyP"
                Version="2.0"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                >
    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">Takeda_Websso</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" />
    </samlp:Status>
    <saml:Assertion Version="2.0"
                    IssueInstant="2015-05-26T08:36:27.905Z"
                    ID="KWQAlI26mGVXfSeQSxKjRELWTFo"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    >
        <saml:Issuer>Takeda_Websso</saml:Issuer>
        <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:SignedInfo>
                <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#" />
                <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1" />
                <ds:Reference URI="#KWQAlI26mGVXfSeQSxKjRELWTFo">
                    <ds:Transforms>
                        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />
                        <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#" />
                    </ds:Transforms>
                    <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1" />
                    <ds:DigestValue>MFVAp1biLkto6qy4i0aeox38izE=</ds:DigestValue>
                </ds:Reference>
            </ds:SignedInfo>
            <ds:SignatureValue>
EONCHqDyz2epJRndnsMZuS1s0bMmZgSQexUa9Aj13PMHD1cjvx5v6AShVHYGWTKABbjjLvfn6WDX
Z3e+NaqarB+gD4+FuEFmXPe9KEsx507O8TqWsdasdaEDckL49qtPPqCDdi2XXKVmrrc96O6B/N2M
+L0MT6ypK0yJfTdarn9vj1NIRyglaArfS6bf1NgYb3uWcZDzGEU3e4EmbkwElh0A8JVOYyurkAYR
ve1EpPKm1KCQOkqXxF4pTWeuDZiekvosadasfVoxX5SC1rMnRc6x/muJJi4PAQJmeLI4VfajluKA
35HqgwBHjuXg7fS3OXPozoT+hQb7tYj9em1ytQ==
</ds:SignatureValue>
        </ds:Signature>
        <saml:Subject>
            <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">jn3542</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData InResponseTo="s248735275d5542177e9a4fd021410177660b9c8be"
                                              NotOnOrAfter="2015-05-26T08:41:27.905Z"
                                              Recipient="https://takeda-dev-sso.onbmc.com:443/atriumsso/Consumer/metaAlias/BmcRealm/sp"
                                              />
            </saml:SubjectConfirmation>
        </saml:Subject>
        <saml:Conditions NotOnOrAfter="2015-05-26T08:41:27.905Z"
                         NotBefore="2015-05-26T08:31:27.905Z"
                         >
            <saml:AudienceRestriction>
                <saml:Audience>https://takeda-dev-sso.onbmc.com:443/atriumsso</saml:Audience>
            </saml:AudienceRestriction>
        </saml:Conditions>
        <saml:AuthnStatement AuthnInstant="2015-05-26T08:36:27.905Z"
                             SessionIndex="KWQAlI26mGVXfSeQSxKjRELWTFo"
                             >
            <saml:AuthnContext>
                <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified</saml:AuthnContextClassRef>
            </saml:AuthnContext>
        </saml:AuthnStatement>
    </saml:Assertion></samlp:Response>';

 $sxe  = new  SimpleXMLElement ( $xml );

 $sxe -> registerXPathNamespace ( 'c' ,  'urn:oasis:names:tc:SAML:2.0:assertion' );
 $result  =  $sxe -> xpath ( '//c :Issuer' );

foreach ( $result  as  $title ) {
  echo  $title  .  "<br />" ;
}
$sxe -> registerXPathNamespace ( 's' ,  'urn:oasis:names:tc:SAML:2.0:protocol');
 $result  =  $sxe -> xpath ( '//s:StatusCode' );
 echo  $result[0]["Value"]  .  "<br />" ;

foreach ( $result  as  $title ) {
  echo  $title["Value"]  .  "<br />" ;
}

 ?> 