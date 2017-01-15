<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["an"])){
         $an = $_GET["an"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"日志不存在！")); //-2没有appname
         return;
      }
	  
	  if(isset($_GET["fn"])){
         $fn = $_GET["fn"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"日志不存在！")); //-2没有FunctionName
         return;
      }
	  
	  if(isset($_GET["acn"])){
         $acn = $_GET["acn"];
      }
      else {
         echo json_encode(array("status"=>-4, "result"=>"日志不存在！")); //-2没有ActionName
         return;
      }
	  
	  if(isset($_GET["ar"])){
         $ar = $_GET["ar"];
      }
      else {
         echo json_encode(array("status"=>-5, "result"=>"日志不存在！")); //-2没有ActionReturn
         return;
      }
	  
	  if(isset($_GET["ao"])){
         $ao = $_GET["ao"];
      }
      else {
         echo json_encode(array("status"=>-6, "result"=>"日志不存在！")); //-2没有ActionObject
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
   $logcount = 0;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datalogs = array();
   
   class Stulogs{
      public $LogId;
      public $UserId;
      public $FunctionName;
      public $ActionName;
	  public $ActionTime;
	  public $ActionReturn;
	  public $ActionObject;
	  public $AppName;
	  public $UserName;
   }
   
   //----- query -----
   $str_logs = "select l.LogId as LogId,l.UserId as UserId,l.FunctionName as FunctionName,l.ActionName as ActionName,l.ActionTime as ActionTime,l.ActionReturn as ActionReturn,l.ActionObject as ActionObject,l.AppName as AppName,u.UserName as UserName 
   from log l left join users u on l.UserId = u.UserId where l.AppName like '%$an%'";
   if(count($fn)>0)
   {
	   $str_logs = $str_logs . " and l.FunctionName like '%$fn%'";
   }
   
   if(count($acn)>0)
   {
	   $str_logs = $str_logs . " and l.ActionName like '%$acn%'";
   }
   
   if(count($ar)>0)
   {
	   $str_logs = $str_logs . " and l.ActionReturn like '%$ar%'";
   }
   
   if(count($ao)>0)
   {
	   $str_logs = $str_logs . " and l.ActionObject like '%$ao%'";
   }
   
   $str_logs = $str_logs . " order by ActionTime desc limit 1000";
   if($rs = mysqli_query($link, $str_logs)){
      $logcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sl = new Stulogs();
         $sl->LogId = $row['LogId'];
         $sl->UserId = $row['UserId'];
		 $sl->FunctionName = $row['FunctionName'];
		 $sl->ActionName = $row['ActionName'];
         $sl->ActionTime = date("Y/m/d H:i:s",strtotime($row['ActionTime']));
		 $sl->ActionReturn = $row['ActionReturn'];
		 $sl->ActionObject = $row['ActionObject'];
		 $sl->AppName = $row['AppName'];
         $sl->UserName = $row['UserName'];
         array_push($datalogs,$sl);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$logcount, "logdata"=>$datalogs, "result"=>"日志获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$logcount, "logdata"=>$datalogs, "result"=>""));      
   return;
?>
