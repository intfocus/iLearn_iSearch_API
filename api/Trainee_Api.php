<?php
   $traineeContent = file_get_contents("php://input");
   $trainee = json_decode($traineeContent);
   $trainingId = $trainee->TrainingId;
   $userId = $trainee->UserId;
   $registerDate = date('Y-m-d H:i:s',time());
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
   
   $str_users = "select DeptId, CanApprove from users where UserId=$userId";
   if($us = mysqli_query($link, $str_users))
   {
      $row = mysqli_fetch_assoc($us);
      $deptid = $row["DeptId"];
      if($row["CanApprove"] == 1)
      {
         $str_depts = "select ParentId from Depts where DeptId=$deptid";
         if($us = mysqli_query($link, $str_depts))
         {
            $row = mysqli_fetch_assoc($us);
            $deptid = $row["ParentId"];
         }
         else {
            sleep(DELAY_SEC);
            echo json_encode(array("status"=> 0, "count"=>0, "result"=>"报名失败！")); 
            return;
         }
      }
   }
   else {
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>0, "result"=>"报名失败！")); 
      return;
   }
   
   $ExamineUser = "";
   $str_userids = "select UserId from users where DeptId=$deptid and CanApprove=1";
   if($uids = mysqli_query($link, $str_userids))
   {
      while($row = mysqli_fetch_assoc($uids))
      {
         $ExamineUser = $ExamineUser . "," . $row["UserId"] . ",";
      }
   }
   else {
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>0, "result"=>"报名失败！")); 
      return;
   }
   
   class Stutrainees{
      public $TrainingId;
      public $UserId;
      public $RegisterDate;
   }
   $st = new Stutrainees();
   //----- query -----
   $str_trainees = "select * from trainees where TrainingId=$trainingId and UserId=$userId";
   if($rs = mysqli_query($link, $str_trainees)){
      $traineecount = mysqli_num_rows($rs);
      if($traineecount<=0){
         $str_trainees = "insert trainees(TrainingId, UserId, RegisterDate, Status, ExamineUser) values($trainingId, $userId, '$registerDate', 0, '$ExamineUser')";
         if(!mysqli_query($link, $str_trainees)){
            if($link){
               mysqli_close($link);
            }
            sleep(DELAY_SEC);
            echo json_encode(array("status"=> 0, "count"=>0, "result"=>"报名失败！")); 
            return;
         }
      }
      else{
         echo json_encode(array("status"=> 0, "count"=>0, "result"=>"重复报名！")); 
         mysqli_close($link);
         return;
      }
   }
   
   mysqli_close($link);
   
   echo json_encode(array("status"=> 1, "count"=>1, "UserId"=>$userId, "TrainingId"=>$trainingId, "RegisterDate"=>$registerDate, "result"=>""));
   return;
?>
