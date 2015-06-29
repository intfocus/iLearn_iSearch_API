<?php

/////////////////
// IP white list
/////////////////

$IP_LIST = <<<EOF
127.0.0.1
10.0.4.108
10.0.6.253
10.0.88.107
10.0.88.108
10.0.55.34
EOF;

function checkIP($ip){

   global $IP_LIST;

   if(empty($ip)){
      return FALSE;
   }

   $ip = trim($ip);

   $ip_list = explode("\n",$IP_LIST);

   foreach($ip_list as $checkIP){
      $checkIP = trim($checkIP);
      if($ip === $checkIP){
         return TRUE;
      }
   }

   return FALSE;
     
}

?>
