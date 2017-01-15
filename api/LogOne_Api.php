<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["uid"])){
         $uid = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"日志不存在！")); //-2没有传用户ID
         return;
      }
	  
	  if(isset($_GET["uan"])){
         $uan = $_GET["uan"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"日志不存在！")); //-2没有传App名称
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"日志不存在！")); //-1没有传任何参数
      return;
   }
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
   
   header('Content-Type:application/json;charset=utf-8');
   
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
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $ucount = 0;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datalog = array();
   class StuLogs{
      public $UserId;
      public $FunctionName;
      public $ActionName;
      public $ActionTime;
	  public $ActionReturn;
	  public $ActionObject;
	  public $AppName;
   }
   
   //----- query -----
   $str_user = "select UserId, FunctionName, ActionName, ActionTime, ActionReturn, ActionObject, AppName from log ";
   if($uid != 0 && $uan != "All")
   {
	   $str_user = $str_user .  "where UserId = $uid and AppName = '$uan' ";
   }
   else if($uid == 0 && $uan != "All")
   {
	   $str_user = $str_user .  "where AppName = '$uan' ";
   }
   else if($uid != 0 && $uan == "All")
   {
	   $str_user = $str_user .  "where UserId = $uid ";
   }
   
   $str_user = $str_user .  "order by ActionTime desc";
   if($rs = mysqli_query($link, $str_user)){
      $ucount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sl = new StuLogs();
         $sl->UserId = $row['UserId'];
         $sl->FunctionName = $row['FunctionName'];
         $sl->ActionName = $row['ActionName'];
         $sl->ActionTime = date("Y/m/d H:i:s",strtotime($row['ActionTime']));
		 $sl->ActionReturn = $row['ActionReturn'];
		 $sl->ActionObject = $row['ActionObject'];
		 $sl->AppName = $row['AppName'];
         array_push($datalog,$sl);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$ucount, "logdata"=>$datalog, "result"=>"日志获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$ucount, "logdata"=>$datalog, "result"=>""));      
   return;
?>
