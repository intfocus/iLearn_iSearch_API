<?php
   include '../lib/CanApprove_Users.php';
   
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["did"])){
         $deptid = $_GET["did"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"审批失败！")); //-2没有传部门ID
         return;
      }
      
      if(isset($_GET["sid"])){
         $status = $_GET["sid"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"审批失败！")); //-3没有传状态
         return;
      }
      
      if(isset($_GET["uid"])){
         $userid = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-4, "result"=>"审批失败！")); //-4没有传待审核人ID
         return;
      }
      
      if(isset($_GET["tid"])){
         $trainingid = $_GET["tid"];
      }
      else {
         echo json_encode(array("status"=>-5, "result"=>"审批失败！")); //-5没有传课程ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"审批失败！")); //-1没有传任何参数
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
   
   //header('Content-Type:application/json;charset=utf-8');
   
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
   // $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   // if (!$link)  //connect to server failure    
   // {
      // sleep(DELAY_SEC);
      // echo DB_ERROR;       
      // return;
   // }
//    
   // $str_trainees = "update trainees set Status=$status where TrainingId=$trainingid and UserId=$userid";
   // if(!mysqli_query($link, $str_trainees)){
      // if($link){
         // mysqli_close($link);
      // }
      // sleep(DELAY_SEC);
      // echo json_encode(array("status"=> 0, "count"=>0, "result"=>"审批失败！")); //写入数据失败
      // return;
   // }
   // mysqli_close($link);
   
   
   echo "---DeptId--- " . $deptid . " ---Status--- " . $status . " ---UserId--- " . $userid . " ---TrainingId--- " . $trainingid . "<br />";
   $cau = new CanApproveUser($trainingid,$userid,$status,$deptid);
   $eus = $cau->clist();
   print_r($eus);
?>