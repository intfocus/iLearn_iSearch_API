<?php
   //接收传送的数据
   $rollcallContent = file_get_contents("php://input");
   //$jsonResult = htmlspecialchars_decode($fileContent);
   $rollcall = json_decode($rollcallContent);
   //echo var_dump($user->username);
   $trainingId = $rollcall->TrainingId;
   $eId = $rollcall->UserId;
   $issueDate = $rollcall->IssueDate;
   $createdUser = $rollcall->CreatedUser;
   $status = $rollcall->Status;
   $reason = $rollcall->Reason;
   $checkinId = $rollcall->CheckInId;
   
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

   //query          
   $link;
   $db_host;
   $admin_account;
   $admin_password;
   $connect_db;
   
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
   
   $userId=0;
   $str_user = "select UserId from Users where EmployeeId = '$eId'";
   if($rs = mysqli_query($link, $str_user)){
      while($row = mysqli_fetch_assoc($rs)){
		 $userId = $row['UserId'];
	  }
   }
   
   $str_log = "Insert into RollCall (TrainingId, UserId, IssueDate, Status, Reason, CreatedUser, CheckInId)" 
               . " VALUES('$trainingId', $userId, '$issueDate', $status, '$reason', $createdUser, $checkinId)" ;
   // echo $str_log;
   // return;
   if(mysqli_query($link, $str_log))
   {
      echo "0";
      return;
   }
   else
   {
      echo -__LINE__ . $str_log;
      return;
   }
?>