<?php
   header('Content-Type: text/xml');
   $xml = ""; 
   $f = fopen('../Plist/iSearch.plist', 'r'); 
   while( $data = fread( $f, 4096 ) ) { 
      $xml .= $data; 
   }
   echo $xml;
?>