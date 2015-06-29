<?php
/********************************************
 *modifyPassword.php
 *1. Check session 
 *2. Get guid by session
 *3  Get cmd, oldpass, newpass1, newpass2
 *4  check if oldpass match 
 *5  check if newpass is legal
 *6  if is admin, encrypt newpass and update the oldpass by newpass
 *   2012/03/07 Dylan
 *
 *   #001 2014/09/15 Phantom        如果是 system admin 的話, 不 check old password
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
   define(VALUE_MIN_CLIENT, 6);
   define(VALUE_MAX_CLIENT, 12);
   define(VALUE_MIN_ADMIN, 8);
   define(VALUE_MAX_ADMIN, 30);   
?>
<?php
   //----- Variable definition -----
   //get from client
   $guid;
   
   $cmd;
   $oldpass;
   $newpass1;
   $newpass2;
   $validcode;
   $password;
   
   $str_query;
   $str_update;   
   $entry_update;
   $result;
   $row;

   $retval = FAILED;
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
   $GUID_ADM = $_SESSION["GUID_ADM"];
   session_write_close(); 
   //////////////////////////////
   // #001 Check GUID_ADM and set systemAdm flag
   //////////////////////////////
   if ($GUID_ADM == "")
      $systemAdminFlag = 0;
   else if ($GUID_ADM == "00000000_0000_0000_0000_000000000000")
      $systemAdminFlag = 1;
   else
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }

   //----- get cmd oldpass newpass1 newpass2 -----
   $cmd = $_GET["cmd"];
   $oldpass = $_GET["oldpass"];
   $newpass1 = $_GET["newpass1"];
   $newpass2 = $_GET["newpass2"];
   ////////////////////////////////////////
   //if cmd can not be recognized
   //echo FAILED
   //return
   ///////////////////////////////////////
   if(strcmp($cmd, "user") && strcmp($cmd, "admin"))
   {   
      echo FAILED;
      
      return;
   }
   //-----------------------------------------
   //----- 2. Check information from DB  -----
   //-----------------------------------------   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo FAILED;
      
      return;
   }

   //----- query validcode and password -----
   $str_query = "
      select *
      from customer
      where GUID = '$guid'
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))//there is no GUID matched 
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
      $row = mysqli_fetch_assoc($result);   
      $validcode = $row["validcode"];      
      $password = $row["password"];      
      mysqli_free_result($result);
      unset($row);
      ////////////////////////////////////////
      //if cmd == user
      //check if oldpass == validcode
      //check if newpass1 == newpass2
      //check the length of newpass1(must be >= 6 && <=12)
      //check there is any ' or - in newpass1
      //retval = SUCCESS   
      ///////////////////////////////////////
      if(!strcmp($cmd, "user"))
      {
         if(!strcmp($oldpass, $validcode))
         {      
            if(!strcmp($newpass1, $newpass2))
            {            
               if(strlen($newpass1) >= VALUE_MIN_CLIENT && strlen($newpass1) <= VALUE_MAX_CLIENT 
                  && !strpos($newpass1, "-")  && !strpos($newpass1, "'"))
               {
                  $retval = SUCCESS;   
               }
            }
         }
      }
      ////////////////////////////////////////
      //if cmd == admin
      //encrypt oldpass
      //check if oldpass == password   
      //check if newpass1 == newpass2
      //check the length of newpass1(must be >= 8 && <=12)
      //encrypt newpass1
      //retval = SUCCESS   
      ///////////////////////////////////////
      else if(!strcmp($cmd, "admin"))
      {      
         $oldpass = hash('md5', $oldpass);
         // #001 begin
         if($systemAdminFlag == 1)
         {
            $newpass1 = hash('md5', $newpass1);
            $retval = SUCCESS;
         }
         else if(!strcmp($oldpass, $password)) //#001 end, change "if" to "else if"
         {      
            if(!strcmp($newpass1, $newpass2))
            {                  
               if(strlen($newpass1) >= VALUE_MIN_ADMIN && strlen($newpass1) <= VALUE_MAX_ADMIN)
               {
                  $newpass1 = hash('md5', $newpass1);
                  $retval = SUCCESS;         
               }
            }
         }         
      }
   }
   else
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
   //////////////////////////////
   //value check ok => update DB
   /////////////////////////////
   if($retval == SUCCESS)
   {
      //update validcode by newpass1 to DB   
      if(!strcmp($cmd, "user"))
      {
         $entry_update = "set validcode = '$newpass1'";
      }
      //update password by newpass1 to DB
      else if(!strcmp($cmd, "admin"))
      {
         $entry_update = "set password = '$newpass1'";   
      }
      $str_update = 
         "update customer
         $entry_update
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
   }   
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   } 

   echo $retval;
   return;
?>
