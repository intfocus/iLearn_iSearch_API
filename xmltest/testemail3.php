<?php
$soap = new SoapClient("http://localhost/TsaSendMail/EmailWebService.asmx?wsdl");  
$result2 = $soap->TsaSendEmail(array(  
    'email'=>'yy_lfy@163.com',  
    'username'=>'yy_lfy',
    'trainingname'=>'abcd'
));  
print_r($result2->TsaSendEmailResult);  
?>