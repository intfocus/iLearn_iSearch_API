<?php
   include '../lib/CanApprove_Users.php';
   // if(is_array($_POST)&&count($_POST)>0){   //判断是否有Get参数
      // if(isset($_POST["TrainingId"])){
         // $trainingId = $_POST["TrainingId"];
      // }
      // else {
         // echo json_encode(array("status"=>-2, "result"=>"报名失败！")); //-2没有传课程ID
         // return;
      // }
//       
      // if(isset($_POST["UserId"])){
         // $userId = $_POST["UserId"];
      // }
      // else {
         // echo json_encode(array("status"=>-3, "result"=>"报名失败！")); //-3没有传用户ID
         // return;
      // }
//       
       // $registerDate = date('Y-m-d H:i:s',time());
   // }
   // else {
      // echo json_encode(array("status"=>-1, "result"=>"报名失败！")); //-1没有传任何参数
      // return;
   // }
   $traineeContent = file_get_contents("php://input");
   $trainee = json_decode($traineeContent);
   $trainingId = $trainee->TrainingId;
   $userId = $trainee->UserId;
   try{
      error_reporting(1);
      $registerDate = $trainee->RegisterDate;
      throw new Exception("abcd", 1);
   }catch(Exception $ex){   
      //echo "---1--- " . $ex->getMessage();
      $registerDate = date('Y-m-d H:i:s',time());
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
         $str_trainees = "insert trainees(TrainingId, UserId, RegisterDate, Status) values($trainingId, $userId, '$registerDate', 0)";
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
   
   $cau = new CanApproveUser($trainingId,$userId,0);
   $eus = $cau->clist();
   echo json_encode(array("status"=> 1, "count"=>1, "UserId"=>$userId, "TrainingId"=>$trainingId, "RegisterDate"=>$registerDate, "EmailUsers"=>$eus, "result"=>""));
   return;
?>
