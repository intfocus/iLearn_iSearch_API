<?php
   require("../lib/testemail.php");
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
      if(strcmp($check_str, "actionTraineeExamines"))
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
   $TrainingId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($TrainingId = check_number($_GET["TrainingId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($UserId = check_number($_GET["UserId"])) == SYMBOL_ERROR)
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
   $str_query1 = "select ParentId from depts as d left join users as u on d.DeptId = u.DeptId where u.UserId=$user_id";
   
   $parentId = 0;
   if($rs = mysqli_query($link, $str_query1))
   {
      $row = mysqli_fetch_assoc($rs);
      $parentId = $row["ParentId"];
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
   $emaillist = array();
   $approreLevel = 0;
   $str_query3 = "select ApproreLevel from trainings where TrainingId=$TrainingId";
   if($rs = mysqli_query($link, $str_query3))
   {
      $row = mysqli_fetch_assoc($rs);
      $approreLevel = $row["ApproreLevel"];
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
   
   $UserName = "";
   $str_users = "select UserName from users where UserId=$UserId";
   if($rs = mysqli_query($link, $str_users)){
	  $row = mysqli_fetch_assoc($rs);
      $UserName = $row["UserName"];
   }
   
   $TrainingName = "";
   $str_trainings = "select TrainingName, ApproreLevel from trainings where TrainingId=$TrainingId";
   if($rs = mysqli_query($link, $str_trainings)){
	  $row = mysqli_fetch_assoc($rs);
      $TrainingName = $row["TrainingName"];
	  $ApproreLevel = $row["ApproreLevel"];
   }
   
   if($parentId == 0)
   {
      $str_query2 = "update trainees set Status = $approreLevel, ExamineUser = '$user_id' where  TrainingId = $TrainingId and UserId = $UserId and Status = $Status";
      if(mysqli_query($link, $str_query2)){
		 $str_log = "Insert into log (UserId,FunctionName,ActionName,ActionTime,ActionReturn,ActionObject,AppName)" 
            . " VALUES('$UserId','报名审核','审核同意',now(),'$user_id','$TrainingId','pc')";
         if(!mysqli_query($link, $str_log))
         {
            echo -__LINE__ . $str_log;
            mysqli_close($link);
            return;
         }
		 $str_traineelog = "Insert into traineelogs (UserId,FunctionName,ActionName,ActionTime,TUserId,TrainingId)" 
            . " VALUES($UserId,'报名审核','审核同意',now(),$user_id,$TrainingId)";
         if(!mysqli_query($link, $str_traineelog))
         {
			echo -__LINE__ . $str_traineelog;
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
   }
   else
   {
      $newStatus = $Status + 1;
      if($approreLevel > $Status)
      {
         $ExamineUser = "";
         $str_userids = "select UserId,Email from users where DeptId=$parentId and CanApprove=1";
         if($uids = mysqli_query($link, $str_userids))
         {
			$ucount = mysqli_num_rows($uids);
			if($ucount)
			{
               while($row = mysqli_fetch_assoc($uids))
               {
                  $ExamineUser = $ExamineUser . "," . $row["UserId"] . ",";
			      array_push($emaillist, $row["Email"]);
               }
			}
			else
			{
			   $ExamineUser = "";
			}
         }
         else {
            sleep(DELAY_SEC);
            echo json_encode(array("status"=> 0, "count"=>0, "result"=>"报名失败！")); 
            return;
         }
		 if($approreLevel == $newStatus)
		 {
			$ExamineUser = ",$user_id,";
		 }
         $str_query4 = "update trainees set Status = $newStatus, ExamineUser = '$ExamineUser' where  TrainingId = $TrainingId and UserId = $UserId and Status = $Status";

         if(mysqli_query($link, $str_query4)){
			$str_log = "Insert into log (UserId,FunctionName,ActionName,ActionTime,ActionReturn,ActionObject,AppName)" 
               . " VALUES('$UserId','报名审核','审核同意',now(),'$user_id','$TrainingId','pc')";
            if(!mysqli_query($link, $str_log))
            {
               echo -__LINE__ . $str_log;
               mysqli_close($link);
               return;
            }
			$str_traineelog = "Insert into traineelogs (UserId,FunctionName,ActionName,ActionTime,TUserId,TrainingId)" 
				. " VALUES($UserId,'报名审核','审核同意',now(),$user_id,$TrainingId)";
			if(!mysqli_query($link, $str_traineelog))
			{
				echo -__LINE__ . $str_traineelog;
				mysqli_close($link);
				return;
			}
            echo "0";
            mysqli_close($link);
			$emaillist = array_unique($emaillist);
			if($approreLevel > $newStatus)
			{
			   $emailsmtp = new EmailSmtp();
               foreach ($emaillist as $el) {
                  //$emailsmtp->eSmtp($el,$UserName,$TrainingName);
			      $soap = new SoapClient("http://localhost/TsaSendEmail/EmailWebService.asmx?wsdl");
			      $result2 = $soap->TsaSendEmail(array(  
				    'email'=>$el,  
				    'username'=>$UserName,
				    'trainingname'=>$TrainingName
			      ));  
               }
			   if(count($emaillist) == 0)
			   {
		          //$emailsmtp->eSmtp("eric_yue@intfocus.com,albert_li@intfocus.com,dh5270@takeda.com,Shally.Wang@takeda.com",$username,$TrainingName);
			      $soap = new SoapClient("http://localhost/TsaSendEmail/EmailWebService.asmx?wsdl");
			      $result2 = $soap->TsaSendEmail(array(  
				    'email'=>'eric_yue@intfocus.com,albert_li@intfocus.com,dh5270@takeda.com,Shally.Wang@takeda.com',  
				    'username'=>$UserName,
				    'trainingname'=>$TrainingName
			      ));
			   }
			}
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
      }
   }
?>
