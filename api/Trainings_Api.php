<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["edate"])){
         $enddate = $_GET["edate"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"课程信息不存在！")); //-2没有传截止时间
         return;
      }
      
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
   
   $dataTrainings = array();
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
   }
   
   //----- query -----
   $str_training = "select ti.TrainingId,ti.TrainingName,ti.SpeakerName,ti.TrainingBegin,ti.TrainingEnd,ti.StartDate,ti.EndDate,
      ti.TrainingLocation,ti.TrainingMemo,ti.TrainingManager,ti.Status,ti.ApproreLevel, te.Status as TraineesStatus from trainings ti 
      left join trainees te on ti.TrainingId = te.TrainingId
      where ti.status = 1 and TIMESTAMPDIFF(DAY,date(ti.StartDate),date('$enddate')) >= 0 
      and TIMESTAMPDIFF(DAY,date(ti.EndDate),date('$enddate')) <= 0 and (te.UserId = $userid or te.UserId is null or te.UserId = '') order by ti.TrainingId";

   if($rs = mysqli_query($link, $str_training)){
      $trainingcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sn = new StuTrainings();
         $sn->Id = $row['TrainingId'];
         $sn->Name = $row['TrainingName'];
         $sn->SpeakerName = $row['SpeakerName'];
         $sn->Begin = date("Y/m/d",strtotime($row['TrainingBegin']));
         $sn->End = date("Y/m/d",strtotime($row['TrainingEnd']));
         $sn->StartDate = date("Y/m/d",strtotime($row['StartDate']));
         $sn->EndDate = date("Y/m/d",strtotime($row['EndDate']));
         $sn->Location = $row['TrainingLocation'];
         $sn->Memo = $row['TrainingMemo'];
         $sn->Manager = $row['TrainingManager'];
         $sn->ApproreLevel = $row['ApproreLevel'];
         $sn->Status = $row['Status'];
         $sn->TraineesStatus = $row['TraineesStatus'];
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
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$trainingcount, "trainingsdata"=>$dataTrainings, "result"=>""));      
   return;
?>
