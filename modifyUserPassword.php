<?php
/********************************************
 *modifyUserPassword.php
 *1. Check session (GUID <> "", loginLevel=2, loginName <> "") 
 *2. Get guid by session
 *3  Get cmd, oldpass, newpass1, newpass2
 *4  check if oldpass match 
 *5  check if newpass is legal
 *6  if is admin, encrypt newpass and update the oldpass by newpass
 *   2013/04/26 Phantom 
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
      //echo FAILED;
      echo -1; //FAILED is not defined yet
      
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
   if($_SESSION["loginLevel"] <> 2)
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if($_SESSION["loginName"] == "")
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $guid = $_SESSION["GUID"];
   $loginName = $_SESSION["loginName"];
   session_write_close(); 
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
   if(strcmp($cmd, "userLogin"))
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
      from userLogin 
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
      $password = $row["password"];      
      mysqli_free_result($result);
      unset($row);
      ////////////////////////////////////////
      //if cmd == userLogin
      //encrypt oldpass
      //check if oldpass == password   
      //check if newpass1 == newpass2
      //check the length of newpass1(must be >= 8 && <=30)
      //encrypt newpass1
      //retval = SUCCESS   
      ///////////////////////////////////////
      if(!strcmp($cmd, "userLogin"))
      {      
         $oldpass = hash('md5', $oldpass);
         if(!strcmp($oldpass, $password))
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
      //update password by newpass1 to DB
         $entry_update = "set password = '$newpass1'";   
      $str_update = 
         "update userLogin set password = '$newpass1' 
         where GUID = '$guid' and login_name='$loginName'
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
