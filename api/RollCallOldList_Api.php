<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["tid"])){
         $tid = $_GET["tid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"点名信息不存在！")); //-2没有传课程编号
         return;
      }
      if(isset($_GET["ciid"])){
         $ciid = $_GET["ciid"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"点名信息不存在！")); //-2没有传签到编号
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"点名信息不存在！")); //-1没有传任何参数
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
   $newcount = 0;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datarollcalls = array();
   class StuRollCalls{
      public $TrainingId;
      public $UserId;
      public $CheckInId;
      public $IssueDate;
      public $Status;
      public $Reason;
      public $CreatedUser;
      public $UserName;
	  public $EmployeeId;
   }
   
   //----- query -----
   $str_rollcall = "select b.TrainingId,b.UserId,b.CheckInId,b.IssueDate,b.Status,b.Reason,b.CreatedUser,u.UserName,u.EmployeeId from RollCall as b left join users as u on b.UserId = u.UserId
            where not exists(select 1 from RollCall where TrainingId=b.TrainingId and UserId=b.UserId and CheckInId=b.CheckInId and b.IssueDate<IssueDate) 
            and b.TrainingId = $tid and b.CheckInId = $ciid and b.status = 1";

   if($rs = mysqli_query($link, $str_rollcall)){
      $rollcallcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){
         $src = new StuRollCalls();
         $src->TrainingId = $row['TrainingId'];
         $src->UserId = $row['UserId'];
         $src->CheckInId = $row['CheckInId'];
         $src->IssueDate = date("Y/m/d",strtotime($row['IssueDate']));
         $src->Reason = $row['Reason'];
         $src->CreatedUser = $row['CreatedUser'];
         $src->UserName = $row['UserName'];
         $src->Status = $row['Status'];
		 $src->EmployeeId = $row['EmployeeId'];
         array_push($datarollcalls,$src);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$rollcallcount, "rollcalldata"=>$datarollcalls, "result"=>"点名信息不存在！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$rollcallcount, "traineesdata"=>$datarollcalls, "result"=>""));      
   return;
?>