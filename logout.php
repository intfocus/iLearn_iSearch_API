<?php
/////////////////////////////////////
//
// #001 modified by Odie
//      A new feature is added due to Entie customization, that is, the 
//      system admin can switch to a normal user account, however he should 
//      be redirected to main_adm.php after loging out when visiting a normal 
//      user page. This modification fixes the bug. It check whether the 
//      session value "GUID_ADM" exists and if it is equal to the admin GUID, 
//      and determine which page to redirect.
//
/////////////////////////////////////

   session_start();
   if (!session_is_registered("GUID")){
       session_register("GUID");
   }
   //////////////////////
   // Set session=empty, redirect to main.php
   //////////////////////

   // #001 begin
   if (session_is_registered("GUID_ADM") && $_SESSION["GUID_ADM"] === "00000000_0000_0000_0000_000000000000"){
      $_SESSION["GUID_ADM"] = "";
      session_write_close();
      header("Location:main_adm.php");
      exit();
   }
   // #001 end

   $_SESSION["GUID"] = "";
   session_write_close();
   header("Location:main.php");
   exit();
?>
