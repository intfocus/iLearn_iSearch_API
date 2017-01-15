<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["uid"])){
         $userid = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"课程信息不存在！")); //-3没有传用户ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"课程信息不存在！")); //-1没有传任何参数
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
   
   function get_employ_id_from_usernames($userids)
   {
      if(strlen($userids)>0){
         $userids = substr($userids,1);
         $userids = substr($userids,0,-1);
         $userids = str_replace(",,",",",$userids);
      }
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;
         die("连接DB失败");
      }
      
      $strusernames = "";
      $str_query = "select * from users where UserId in ($userids)";
      if($result = mysqli_query($link, $str_query)){
         while($row = mysqli_fetch_assoc($result)){
            $strusernames = $strusernames . $row["UserName"] . ",";
         }
         if(strlen($strusernames)>0)
         {
            $strusernames = substr($strusernames, 0,-1);
         }
         return $strusernames;
      }
      else
      {
         //echo DB_ERROR;
         //die("操作资料库失败");
         echo "";
      }
   }
   
   $dataTrainings = array();
   $dataTManagers = array();
   class StuTrainings{
      public $Id;
      public $Name;
      public $SpeakerName;
      public $Begin;
      public $End;
      public $StartDate;
      public $EndDate;
      public $Location;
      public $Memo;
      public $Manager;
      public $Status;
      public $ApproreLevel;
	  public $TraineesStatus;
	  public $CancelStatus;
   }
   
   //----- query -----
   $str_training = "select ti.TrainingId,ti.TrainingName,ti.SpeakerName,ti.TrainingBegin,ti.TrainingEnd,ti.StartDate,ti.EndDate,
      ti.TrainingLocation,ti.TrainingMemo,ti.TrainingManager,ti.Status,ti.ApproreLevel,te.Status as TraineesStatus, tc.Status as CancelStatus from trainings ti 
	  left join trainees te on ti.TrainingId = te.TrainingId and (te.UserId = $userid  or te.UserId is null)
	  left join traineecancels tc on ti.TrainingId = tc.TrainingId and (tc.UserId = $userid or tc.UserId is null)
      where ti.status = 1 and TIMESTAMPDIFF(DAY,date(ti.TrainingBegin),now()) >= 0 
      and TIMESTAMPDIFF(DAY,date(ti.EndDate),now()) <= 0 
      and ti.UserList like '%,$userid,%' order by ti.TrainingId";
	  //echo $str_training;
	  //return;

   if($rs = mysqli_query($link, $str_training)){
      $trainingcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sn = new StuTrainings();
         $sn->Id = $row['TrainingId'];
         $sn->Name = $row['TrainingName'];
         $sn->SpeakerName = $row['SpeakerName'];
         $sn->Begin = date("Y/m/d",strtotime($row['StartDate']));
         $sn->End = date("Y/m/d",strtotime($row['EndDate']));
         $sn->StartDate = date("Y/m/d",strtotime($row['TrainingBegin']));
         $sn->EndDate = date("Y/m/d",strtotime($row['TrainingEnd']));
         $sn->Location = $row['TrainingLocation'];
         $sn->Memo = $row['TrainingMemo'];
         $sn->Manager = get_employ_id_from_usernames($row['TrainingManager']);
         $sn->ApproreLevel = $row['ApproreLevel'];
         $sn->Status = $row['Status'];
		 $sn->TraineesStatus = $row['TraineesStatus'];
		 $sn->CancelStatus = $row['CancelStatus'];
         array_push($dataTrainings,$sn);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$trainingcount, "trainingsdata"=>$dataTrainings, "result"=>"课程信息获取失败！")); 
      return;
   }
   
   $str_tmanager = "select ti.TrainingId,ti.TrainingName,ti.SpeakerName,ti.TrainingBegin,ti.TrainingEnd,ti.StartDate,ti.EndDate,
      ti.TrainingLocation,ti.TrainingMemo,ti.TrainingManager,ti.Status,ti.ApproreLevel,te.Status as TraineesStatus, tc.Status as CancelStatus from trainings ti 
	  left join trainees te on ti.TrainingId = te.TrainingId and (te.UserId = $userid or te.UserId is null) 
	  left join traineecancels tc on ti.TrainingId = tc.TrainingId and (tc.UserId = $userid or tc.UserId is null)
      where ti.status = 1 and TIMESTAMPDIFF(DAY,date(ti.TrainingBegin),now()) >= 0 
      and TIMESTAMPDIFF(DAY,date(ti.EndDate),now()) <= 0 
      and ti.TrainingManager like '%,$userid,%' order by ti.TrainingId";

   if($rs = mysqli_query($link, $str_tmanager)){
      $tmanagercount = mysqli_num_rows($rs);
	  $trainingcount = $trainingcount + $tmanagercount;
      while($row = mysqli_fetch_assoc($rs)){      
         $sm = new StuTrainings();
         $sm->Id = $row['TrainingId'];
         $sm->Name = $row['TrainingName'];
         $sm->SpeakerName = $row['SpeakerName'];
         $sm->Begin = date("Y/m/d",strtotime($row['StartDate']));
         $sm->End = date("Y/m/d",strtotime($row['EndDate']));
         $sm->StartDate = date("Y/m/d",strtotime($row['TrainingBegin']));
         $sm->EndDate = date("Y/m/d",strtotime($row['TrainingEnd']));
         $sm->Location = $row['TrainingLocation'];
         $sm->Memo = $row['TrainingMemo'];
         $sm->Manager = get_employ_id_from_usernames($row['TrainingManager']);
         $sm->ApproreLevel = $row['ApproreLevel'];
         $sm->Status = $row['Status'];
		 $sm->TraineesStatus = $row['TraineesStatus'];
		 $sm->CancelStatus = $row['CancelStatus'];
         array_push($dataTManagers,$sm);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$trainingcount, "tmanagerdata"=>$dataTManagers, "result"=>"课程信息获取失败！")); 
      return;
   }
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$trainingcount, "trainingsdata"=>$dataTrainings, "tmanagerdata"=>$dataTManagers, "result"=>""));      
   return;
?>
