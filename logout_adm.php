<?php
   session_start();
   if (!session_is_registered("GUID")){
       session_register("GUID");
   }
   if (!session_is_registered("GUID_ADM")){
       session_register("GUID_ADM");
   }

   //////////////////////
   // Set session=empty, redirect to main.php
   //////////////////////
   $_SESSION["GUID"] = "";
   $_SESSION["GUID_ADM"] = "";
   session_write_close();
   header("Location:main_adm.php");
   exit();
?>
