<?php
$soap = new SoapClient("http://localhost/TsaSendEmail/EmailWebService.asmx?wsdl");
$result2 = $soap->TsaSendEmail(array(  
    'email'=>'eric_yue@intfocus.com,albert_li@intfocus.com',  
    'username'=>'eric_yue',
    'trainingname'=>'abcd'
));  
print_r($result2->TsaSendEmailResult);  
?>