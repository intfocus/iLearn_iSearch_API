<?php
///////////////////////////////////////////////////
// 2013/05/03 created by Odie
// refreshDept.php
//   return string of the refreshed checkbox
//   (called after the department is edited, the checkbox needs to be refreshed again)
///////////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);

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
   define(REFRESH_PATH, "$working_path/refreshDepartCheckbox.php");  //refresh page function
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEPT_LENGTH, 1024);
   define(LOGIN_NAME_LENGTH, 128);
   define(PASSWORD_MIN, 8);
   define(PASSWORD_MAX, 30);
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(ERROR_SAME_NAME, -5);
   define(ERROR_NO_USER, -6);
   define(PASSWORD_ERROR, -7);
   

   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "refresh_checkbox"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
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
   
   $refresh_str = refreshDepartCheckbox($link, $GUID);

   //close link
	if ($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   echo $refresh_str;           
?>
