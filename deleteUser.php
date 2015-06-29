<?php
///////////////////////////////////////////////////
//deleteUser.php
//
//1.get information of the user to be deleted
//2.delete user 
//3.return string of the refreshed user table
///////////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   define(STR_LENGTH, 128);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   
   session_start();
   if (!session_is_registered("GUID"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   session_write_close();
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define(REFRESH_PATH, "$working_path/refreshUserPage.php");  //refresh page function
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "delete_user"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //check name
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {         
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt;", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt;", $check_str);
      }
      return $check_str;
   }
   
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
        return SYMBOL_ERROR; 
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $departID;

   //query
   $link;
   $str_delete;
   $result;                 //query result
   $row;                    //1 data array
   $refresh_str;
   
   //depart
   $depID;
   $dep_name;
   
   //1.get information of the department to be delete
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }

   if(($login_name = check_name($_GET["login_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
      
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   //----- query -----
   $str_delete = "
      delete
      from userLogin 
      where GUID = '" . $GUID . "' and login_name='$login_name'";
   
   //2.delete user 
   //----- Connect to MySql ----- 

   if (mysqli_query($link, $str_delete))  //delete successfully
   {
      //3.refresh user page
      if(file_exists(REFRESH_PATH))
      {
         include_once(REFRESH_PATH);
      }
      else
      {
         sleep(DELAY_SEC);
         echo FILE_ERROR;
         return;
      }
      //$link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      $refresh_str = refreshUserPage($link, $GUID);
	   if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo $refresh_str;
   }
   else  //delete failed
   {      
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;
      return;
   }
   
?>
