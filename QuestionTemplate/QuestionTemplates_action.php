<?php

   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
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
   if ($_SESSION["GUID"] == "" || $_SESSION["username"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      //header("Location:". $web_path . "main.php?cmd=err");
      $return_string = "<div id=\"sResultTitle\" class=\"sResultTitle\">Session 已经过期，请重新登录！</div>";
      echo $return_string;
      exit();
   }
   $user_id = $_SESSION["GUID"];
   $login_name = $_SESSION["username"];
   // $login_name = "Phantom";
   // $user_id = 1;
   $current_func_name = "iSearch";
   session_write_close();
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "actionQTs"))
      {
         return SYMBOL_ERROR;
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
      if($check_str < 0)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $DeptId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($QTId = check_number($_GET["QTId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($Status = check_number($_GET["Status"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   echo $Status . "<br />";
   if ($Status == 0)
   {
      $QTStatus = 1;
	  $ActionReturn = "问卷模板上架";
   }
   else if ($Status == 1)
   {
      $QTStatus = 0;
	  $ActionReturn = "问卷模板下架";
   }
   else
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
   //***Step18 上下架动作修改SQL语句
   $str_query1 = "Update QuestionTemplate set Status=$QTStatus where QuestionTemplateId=$QTId";
   /////////////////////
   // prepare the SQL command and query DB
   /////////////////////
   if(mysqli_query($link, $str_query1)){
	  $str_log = "Insert into log (UserId,FunctionName,ActionName,ActionTime,ActionReturn,ActionObject,AppName)" 
         . " VALUES('$user_id','问卷模板管理','问卷模板上下架',now(),'$ActionReturn','$QTId','pc')";
      if(!mysqli_query($link, $str_log))
      {
         echo -__LINE__ . $str_log;
         mysqli_close($link);
         return;
      }
      echo "0";
      mysqli_close($link);
      return;
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
?>
