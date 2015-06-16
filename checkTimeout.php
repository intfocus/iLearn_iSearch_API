<?php
///////////////////////////////////////////////////////
//checkTimeout.php
//
//1.if session not exist, return -1 to PMarkFunction.js
///////////////////////////////////////////////////////

   header('Content-Type:text/html;charset=utf-8');

   define(SUCCESS, 0);
   define(TIMEOUT, -1);
   
   session_start();
   // if (!session_is_registered("GUID"))
   // {
      // echo TIMEOUT;
      // session_write_close();
      // exit();
   // }
   if ($_SESSION["GUID"] == "")
   {
      echo TIMEOUT;
      session_write_close();
      exit();
   }
   //if session exists
   echo SUCCESS;
   session_write_close();
   exit();

?>