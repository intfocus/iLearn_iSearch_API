<?php
   function gen_vcode($input)
   {
      $hash_key1 = "pmarker";
      $hash_key2 = "open";      
      $vcode = "$hash_key1$input$hash_key2";
         
      //$vcode = hash('md5', $vcode);
      $vcode = md5($vcode);
      $vcode = substr($vcode, 0, 8) . substr($vcode, 28, 4);
      return $vcode;
   }
?>