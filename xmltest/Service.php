<?php   
   class test   
   {   
       function show()   
       {   
           return 'the data you request!';   
       }   
   }   
   function getUserInfo($name)   
   {   
       return 'fbbin';   
   }   
   //实例化的参数手册上面有，这个是没有使用wsdl的，所以第一个参数为null，如果有使用wsdl，那么第一个参数就是这个wsdl文件的地址。   
   $server = new SoapServer(null, array('uri'=>'http://soap/','location'=>'http://localhost/test/server.php'));   
   $server->setClass('test');   
   //$server->addFunction('getUserInfo');   
   $server->handle();   
?>