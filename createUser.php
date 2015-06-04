<?php
///////////////////////////////////////////////////
//createUser.php
//
//1.get information of the user to be create
//2.create a new user in the userLogin table
//3.return string of the refreshed user table
//
//2013/05/20 #001 Odie modified
//1. Add checking "customer" table before creating a normal user account
//   (a normal user account should not be as the same as the admin account with the same GUID)
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
   define(REFRESH_PATH, "$working_path/refreshUserPage.php");  //refresh page function
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
   define(PASSWORD_ERROR, -7);
   
   //check dept_list 
   function check_dept_list($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > DEPT_LENGTH)
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
   
   //check name 
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > LOGIN_NAME_LENGTH)
      {         
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }
      /*      
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
       */
      return $check_str;
   }

   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "new_user"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //----- Check password ----
   function check_password($check_str)
   {
      if(strlen($check_str) == 0)
         return $check_str;
      if(strlen($check_str) < PASSWORD_MIN || strlen($check_str) > PASSWORD_MAX)
      {
         return PASSWORD_ERROR;
      } 
      return $check_str;
   }
   //get data from client
   $cmd;
   $userName;

   //query
   $link;
   $str_query;
   $str_insert;
   $result;                 //query result
   $row;                    //1 data array
   $refresh_str;
   
   //depart
   $depID;
   $dep_name;
   
   //1.get information of the user to be set   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }

   if(($dept_list = check_dept_list($_GET["dept_list"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   if(($login_name = check_name($_GET["login_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   if(($password = check_password($_GET["password"])) == PASSWORD_ERROR)
   {
      sleep(DELAY_SEC);
      echo PASSWORD_ERROR;
      return;
   }
   $password = hash('md5',$password);
      
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }


   //escape
   $login_name = mysqli_real_escape_string($link, $login_name); 
   $dept_list = mysqli_real_escape_string($link, $dept_list); 

   //----- query -----
   $str_query = "
      select *
      from userLogin 
      where login_name = '" . $login_name . "'";

   // #001 begin
   $str_query_1 = "
      select *
      from customer
      where login_name = '" . $login_name . "' and GUID = '". $GUID . "'";
   // #001 end

   $str_insert = "
      insert into userLogin (GUID, login_name, password, last_login_time, status, dept_list)
      values('$GUID', '$login_name', '$password', '0000-00-00 00:00:00',1,'$dept_list')";
   
   //2.create department
   //----- Connect to MySql ----- 

   if (($result = mysqli_query($link, $str_query)) && ($result_1 = mysqli_query($link, $str_query_1)))
   {
      if(($row_number = mysqli_num_rows($result)) > 0)  //if the user already exists
      {
         mysqli_free_result($result);
         mysqli_free_result($result_1);
         if ($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         echo ERROR_SAME_NAME;
         return;
      }
      // #001 begin
      else if(($row_number = mysqli_num_rows($result_1)) > 0)  //if the user already exists
      {
         mysqli_free_result($result);
         mysqli_free_result($result_1);
         if ($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         echo ERROR_SAME_NAME;
         return;
      }
      // #001 end
      else
      {
         mysqli_free_result($result);
         mysqli_free_result($result_1);
         if (!mysqli_query($link, $str_insert))  //insert failed
         {
            if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo DB_ERROR;
            return;
         }
         else  //insert successfully
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
      }
   }
   else
   {  
      if ($result)
         mysqli_free_result($result);    
      if ($result_1)
         mysqli_free_result($result_1);    
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
