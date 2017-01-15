<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["tid"])){
         $tid = $_GET["tid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"课程信息不存在！")); //-2没有传课程ID
         return; 
      }
   }
   else{
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
   $categorycount;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datacheckin = array();
   class StuCheckIn{
      public $Id;
      public $Name;
      public $UserId;
	  public $UserName;
      public $EmployeeId;
      public $CreatedTime;
      public $Status;
      public $TrainingId;
   }
   
   //----- query -----
   $str_file = "select ci.CheckInId, ci.CheckInName, ci.CreatedUser, ci.CreatedTime, ci.Status, ci.TrainingId, u.UserName, u.EmployeeId 
      from CheckIns as ci left join users as u on ci.CreatedUser = u.UserId where TrainingId = $tid and ci.Status = 1;";

   if($rs = mysqli_query($link, $str_file)){
      $checkincount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sc = new StuCheckIn();
         $sc->Id = $row['CheckInId'];
         $sc->Name = $row['CheckInName'];
         $sc->UserId = $row['CreatedUser'];
		 $sc->UserName = $row['UserName'];
         $sc->EmployeeId = $row['EmployeeId'];
         $sc->CreatedTime = date("Y/m/d H:i:s",strtotime($row['CreatedTime']));
         $sc->Status = $row['Status'];
         $sc->TrainingId = $row['TrainingId'];
         array_push($datacheckin,$sc);
      }
      // mysqli_close($link);
      
      //$data = doSql('SELECT nodeID id,fid,nodeName text FROM mytable');
      //$bta = new BuildTreeArray($data,'id','fid',0);
      //$data = $bta->getTreeArray();
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      // echo -__LINE__;
      echo json_encode(array("status"=> 0, "result"=>"课程信息不存在！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$checkincount, "data"=>$datacheckin, "result"=>""));      
   return;
?>