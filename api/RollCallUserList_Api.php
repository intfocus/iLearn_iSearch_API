<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["tid"])){
         $tid = $_GET["tid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"点名信息不存在！")); //-2没有传课程编号
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
   
   $dataTraninees = array();
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
   }
   
   //----- query -----
   $str_training = "select TrainingId,TrainingName,SpeakerName,TrainingBegin,TrainingEnd,StartDate,EndDate,
      TrainingLocation,TrainingMemo,TrainingManager,Status,ApproreLevel from trainings 
      where status = 1 and TrainingId = $tid";
   $sn = new StuTrainings();

   if($rs = mysqli_query($link, $str_training)){
      while($row = mysqli_fetch_assoc($rs)){
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
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$trainingcount, "trainingsdata"=>$dataTrainings, "result"=>"点名信息不存在！")); 
      return;
   }
   
   class StuTrainees{
      public $UserId;
      public $UserName;
      public $TrainingId;
      public $RegisterDate;
      public $EmployeeId;
   }
   
   $str_trainee = "select te.UserId, u.UserName, u.EmployeeId, te.RegisterDate, te.TrainingId from Trainees te left join Users u on te.UserId = u.UserId where te.TrainingId = ". $sn->Id;

   if($rs = mysqli_query($link, $str_trainee)){
      $traineecount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){
         $st = new StuTrainees();
         $st->UserId = $row['UserId'];
         $st->UserName = $row['UserName'];
         $st->EmployeeId = $row['EmployeeId'];
         $st->RegisterDate = date("Y/m/d",strtotime($row['RegisterDate']));
         $st->TrainingId = $row['TrainingId'];
         array_push($dataTraninees, $st);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "result"=>"点名信息不存在！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, 
      "Id" => $sn->Id, 
      "Name" => $sn->Name, 
      "SpeakerName" => $sn->SpeakerName, 
      "Begin" => $sn->Begin,
      "End" => $sn->End,
      "StartDate" => $sn->StartDate,
      "EndDate" => $sn->EndDate,
      "Location" => $sn->Location,
      "Memo" => $sn->Memo,
      "Manager" => $sn->Manager,
      "Status" => $sn->Status,
      "count"=>$traineecount, "traineesdata"=>$dataTraninees, "result"=>""));      
   return;
?>