<?php
   //接收传送的数据
   $checkinContent = file_get_contents("php://input");
   //$jsonResult = htmlspecialchars_decode($fileContent);
   $checkin = json_decode($checkinContent);
   //echo var_dump($user->username);
   $userId = $checkin->UserId;
   $checkinName = $checkin->CheckInName;
   $status = $checkin->Status;
   $checkinId = $checkin->CheckInId;
   $trainingId = $checkin->TrainingId;
   
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
   $login_name = "Phantom";

   //query          
   $link;
   $db_host;
   $admin_account;
   $admin_password;
   $connect_db;
   
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
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }
   
   if($status == 0)
   {
      
      $str_log = "Insert into CheckIns (CheckInName,CreatedUser,CreatedTime,Status,TrainingId)" 
                  . " VALUES('$checkinName',$userId,now(),1,$trainingId)" ;
      
      if(mysqli_query($link, $str_log))
      {
         $CheckInId = mysqli_insert_id($link);
         echo json_encode(array("status"=> 1, "Id"=> $CheckInId, "Name"=> $checkinName, "UserId"=> $userId, "TrainingId"=> $trainingId, "result"=>""));
         mysqli_close($link);
         return;
      }
      else
      {
         echo json_encode(array("status"=> 0, "result"=>"创建签到失败！")); 
         mysqli_close($link);
         return;
      }
   }elseif($status == 1)
   {
      $str_log = "update CheckIns set CheckInName = '$checkinName' where CheckInId = $checkinId" ;
      
      if(mysqli_query($link, $str_log))
      {
         echo json_encode(array("status"=> 1, "Id"=> $checkinId, "Name"=> $checkinName, "UserId"=> $userId, "result"=>""));
         mysqli_close($link);
         return;
      }
      else
      {
         echo json_encode(array("status"=> 0, "result"=>"创建签到失败！")); 
         mysqli_close($link);
         return;
      }  
   }else{
      $str_log = "update CheckIns set Status = -1 where CheckInId = $checkinId" ;
      
      if(mysqli_query($link, $str_log))
      {
         echo json_encode(array("status"=> 1, "Id"=> $checkinId, "Name"=> $checkinName, "UserId"=> $userId, "result"=>""));
         mysqli_close($link);
         return;
      }
      else
      {
         echo json_encode(array("status"=> 0, "result"=>"创建签到失败！"));
         mysqli_close($link);
         return;
      }
   }
?>