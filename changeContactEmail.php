<?php
/********************************************
 *changeContactEmail.php
 *1. Check session 
 *2. Get guid by session
 *3  Get contact 
 *4  check contact 
 *   2012/11/21 Phantom 
 ********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FAILED;
      
      return;
   }
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //----- Return value -----
   define(SUCCESS, 0);
   define(FAILED, -1);   
   //----- Check number -----
?>
<?php
   //----- Variable definition -----
   //get from client
   $guid;
   
   $contact_email;
   
   $str_query;
   $str_update;   
   $entry_update;
   $result;
   $row;

   //-----------------------------------------
   //----- 1. Check session and reportID -----
   //-----------------------------------------
   
   //----- session check -----
   session_start();
   if(!session_is_registered("GUID"))  //check session
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if($_SESSION["GUID"] == "")
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $guid = $_SESSION["GUID"];
   session_write_close(); 
   //----- get cmd oldpass newpass1 newpass2 -----
   $contact_email = $_GET["contact"];
   //-----------------------------------------
   //----- 2. Check contact_email        -----
   //-----------------------------------------   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo FAILED;
      
      return;
   }

   //////////////////////////////
   //value check ok => update DB
   /////////////////////////////
   $str_update = 
      "update customer 
      set contact_email = '$contact_email' 
      where GUID = '$guid'
      ";         
   if(!mysqli_query($link, $str_update))
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo FAILED;
     
      return;
   }
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   } 

   echo SUCCESS;
   return;
?>
