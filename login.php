<?php
//////////////////////////////////////////////
// #001  Phantom,Odie   2013-04-26  Add loginType parameter, 
//                                  if loginType=1 ==> Admin, will check customer table
//                                  if loginType=2 ==> User, will check userLogin table
//
// #002  Odie           2014-11-26  Add SQL escape to $login_name
   
   //----- Define -----
   // include_once("http.php");
   // include_once("wstrust.php");
   define("FILE_NAME", "./DB.conf"); //account file name
   define("DELAY_SEC", 3);                                       //delay reply
   define("FILE_ERROR", -3);
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;

      return;
   }
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("URL_PREFIX", $webui_link);
   define("ILLEGAL_CHAR", "'-;<>");                          //illegal char
   define("TIME_ZONE", "Asia/Taipei");
   define("VCODE_LENGTH", 29);             
   //return value
   define("DB_ERROR", -1);       
   define("EMPTY_REMAIN", -2);   
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_GUID", -4);
   define("SYMBOL_ERROR_HOSTNAME", -5);
   
   
   //////////////////////
   // Input validation
   //////////////////////
   function check($check_str)
   {
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR; 
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   session_start();
   $login_name = $_SESSION["login_name"];
   //if(($login_name = check($_SESSION["login_name"])) == SYMBOL_ERROR)
   //{
   //  sleep(DELAY_SEC);
   //  header("Location:main.php?cmd=err");
   //  exit();
   //}
   $password = "";//$_POST["password"];
   $username = $login_name;
   /////////////////////////////
   //Dylan 20120307
   //encrypt password by md5
   ////////////////////////////
   $password = hash('md5', $password);
   //////////////////////
   // check login and password
   //////////////////////

   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   //----- Connect to MySQL -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)
   {  //connect to server failure
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   //----- Query entryID by GUID, hostname, domainname -----
   
   $login_name = mysqli_real_escape_string($link, $login_name);   #002
   
   $str_query1 = "select UserId, status, UserName, EmployeeId from users where UserWId = '$login_name' and status=1";
   
   if($result = mysqli_query($link, $str_query1))
   {   //query success
      $row = mysqli_fetch_assoc($result);
      $rownum = mysqli_num_rows($result);
      
      if ($rownum > 0)
      {
         $uid = $row["UserId"];
         $status = $row["status"];
         $username = $row["UserName"];
		 $employeeId = $row["EmployeeId"];
         $loginType = 2;
         $timestr = date('Y/m/d H:i:s', time());
		 echo "1--- " . $uid . "<br />";
		 echo "1--- " . $employeeId . "<br />";
		 echo "1--- " . $username . "<br />";
		 
         $_SESSION["GUID"] = $uid;
         $_SESSION["loginName"] = $employeeId; //#001 Add
         $_SESSION["username"] = $username;
         session_write_close(); 
         header("Location:index.php");
         exit();
      }
      else 
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;   
         }
      header("Location:main.php?cmd=account_not_exist");
      exit();
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
      header("Location:main.php");
      exit();
   }
    
   //////////////////////
   // If failed, set session=empty, redirect to main.php
   //////////////////////
   session_start();
   $_SESSION["GUID"] = "";
   $_SESSION["GUID_ADM"] = "";
   $_SESSION["loginLevel"] = ""; //#001 Add
   $_SESSION["loginName"] = ""; //#001 Add
   session_write_close();
   sleep(DELAY_SEC);
   header("Location:main.php?cmd=err");
   exit();
?>
