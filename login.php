<?php
//////////////////////////////////////////////
// #001  Phantom,Odie   2013-04-26  Add loginType parameter, 
//                                  if loginType=1 ==> Admin, will check customer table
//                                  if loginType=2 ==> User, will check userLogin table
//
// #002  Odie           2014-11-26  Add SQL escape to $login_name
   
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   define(FILE_ERROR, -3);
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
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(URL_PREFIX, $webui_link);
   define(ILLEGAL_CHAR, "'-;<>");                          //illegal char
   define(TIME_ZONE, "Asia/Taipei");
   define(VCODE_LENGTH, 29);             
   //return value
   define(DB_ERROR, -1);       
   define(EMPTY_REMAIN, -2);   
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_GUID, -4);
   define(SYMBOL_ERROR_HOSTNAME, -5);

   session_start();
   if (!session_is_registered("GUID"))
   {
      session_register("GUID");
   }
   if (!session_is_registered("GUID_ADM"))
   {
      session_register("GUID_ADM");
   }

   session_write_close();
   
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

   if(($login_name = check($_POST["login_name"])) == SYMBOL_ERROR)
   {
     sleep(DELAY_SEC);
     header("Location:main.php?cmd=err");
     exit();
   }
   $password = $_POST["password"];
   //$loginType = $_POST["loginType"]; //直接猜測 system admin or user, 不再用 loginType
   /* 
   if(($password = check($_POST["password"])) == SYMBOL_ERROR)
   {
     sleep(DELAY_SEC);
     header("Location:main.php?cmd=err");
     exit();
   }
   */
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
   
   $str_query1 = "
         select *
         from customer 
         where login_name = '$login_name' and password = '$password' and status=1";
   $str_query2 = "
         select *
         from userLogin 
         where login_name = '$login_name' and password = '$password' and status=1";

   if($result = mysqli_query($link, $str_query2))
   {   //query success
      $row = mysqli_fetch_assoc($result);
      $rownum = mysqli_num_rows($result);
      if ($rownum > 0)
      {
         $guid = $row["GUID"];
         $status = $row["status"];
         $loginType = 2;
         $timestr = date('Y-m-d H:i:s', time());
         $str_query2 = "Update userLogin set last_modify_time='$timestr' where login_name='$login_name';";
         mysqli_query($link, $str_query2); // no check, 失敗就算了, 只是修改 userLogin 裡面的 last_modify_time
      }
      else 
      {
         mysqli_free_result($result);
         if($result = mysqli_query($link, $str_query1))
         {
            $row = mysqli_fetch_assoc($result);
            $rownum = mysqli_num_rows($result);
            if ($rownum > 0)
            {
               $guid = $row["GUID"];
               $status = $row["status"];
               $loginType = 1;
            }
            else
            {
               $guid = "";
            }
         }
         else
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;   
            }
         }
      }
      mysqli_free_result($result);
      if($link)
      {
         mysqli_close($link);
         $link = 0;   
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
   // If success, set session=GUID, redirect to Billy
   //////////////////////
   if ($rownum > 0)
   {
      //////////////////////
      // 20120326 Billy
      // If status = -1, set session=empty, redirect to login_err.php
      //////////////////////
      if ($status < 0)
      {
         //----- Connect to MySQL -----
         $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
         if(!$link)
         {  //connect to server failure
            sleep(DELAY_SEC);
            header("Location:" . URL_PREFIX . "/approve_err.html");
            exit();
         }
         $str_query2 = "
            select vcode
            from queryPassword 
            where GUID = '$guid' and length(vcode) = " . VCODE_LENGTH;
         
         if($result = mysqli_query($link, $str_query2))
         {
            $row = mysqli_fetch_assoc($result);
            $rownum = mysqli_num_rows($result);
            if ($rownum != 0)  //query success
            {
               $vcode = $row["vcode"];
            }
            mysqli_free_result($result);
            if($link)
            {
               mysqli_close($link);
               $link = 0;   
            }
            if ($vcode == "")  //vcode not found
            {
               sleep(DELAY_SEC);
               header("Location:" . URL_PREFIX . "/approve_err.html");
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
            header("Location:" . URL_PREFIX . "/approve_err.html");
            exit();
         }
         session_start();
         $_SESSION["GUID"] = "";
         $_SESSION["GUID_ADM"] = "";
         $_SESSION["loginLevel"] = ""; //#001 Add
         $_SESSION["loginName"] = ""; //#001 Add
         session_write_close();
         sleep(DELAY_SEC);
         header("Location:" . URL_PREFIX . "/login_err.php?vcode=" . $vcode);
         exit();
         
      }
      session_start();
      $_SESSION["GUID"] = $guid;
      $_SESSION["GUID_ADM"] = "";
      $_SESSION["loginLevel"] = $loginType;  //#001 Add
      $_SESSION["loginName"] = $login_name; //#001 Add 
      session_write_close();
      header("Location:OSC_index.php");
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
