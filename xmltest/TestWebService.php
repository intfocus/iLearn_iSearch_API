<?php
   $soap = new SoapClient(null, array('location'=>'http://localhost/phptest/xmltest/Service.php','uri' =>'http://soap/'));      
   echo $soap->show();   
?>