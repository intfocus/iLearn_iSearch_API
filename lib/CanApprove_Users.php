<?php
   class CanApproveUser{
      function __construct($tid,$uid,$sid,$did=0){
         //define("FILE_NAME", "../DB.conf");
         //define("DELAY_SEC", 3);
         //define("FILE_ERROR", -2);
   
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
         
         //define
         // define("DB_HOST", $db_host);
         // define("ADMIN_ACCOUNT", $admin_account);
         // define("ADMIN_PASSWORD", $admin_password);
         // define("CONNECT_DB", $connect_db);
         // define("TIME_ZONE", "Asia/Shanghai");
         // define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
//       
         // //return value
         // define("SUCCESS", 0);
         // define("DB_ERROR", -1);
//          
         // //timezone
         // date_default_timezone_set(TIME_ZONE);
      
         $this->TrainingId = $tid;
         $this->UserId = $uid;
         $this->Status = $sid;
         $this->DeptId = $did;
      }
      
      //产生审核人员列表
      function clist(){
         //link    
         $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
         if (!$link)  //connect to server failure    
         {
            sleep(DELAY_SEC);
            echo DB_ERROR;       
            return;
         }
         
         //获取课程审核层级 
         $str_training_approrelevel = "select ApproreLevel from Trainings where trainingid= " . $this->TrainingId;
         if($rs = mysqli_query($link, $str_training_approrelevel)){
            $row = mysqli_fetch_assoc($rs);
            $approreLevel = $row["ApproreLevel"];
         }
         
         //获取课程已报名层级
         $str_trainee_approrelevel = "select Status from trainees where trainingid=" . $this->TrainingId . " and userid=" . $this->UserId;
         if($rs = mysqli_query($link, $str_trainee_approrelevel)){
            $row = mysqli_fetch_assoc($rs);
            $status = $row["Status"];
         }
         
         $eus = array();
         //判断当前用户所在部门是否已经审批过
         if($approreLevel >= $this->Status && $status < $this->Status){
            $str_trainees = "update trainees set Status=$this->Status where TrainingId=$this->TrainingId and UserId=$this->UserId";
            if(!mysqli_query($link, $str_trainees)){
               if($link){
                  mysqli_close($link);
               }
               sleep(DELAY_SEC);
               echo json_encode(array("status"=> 0, "count"=>0, "result"=>"审批失败！")); //写入数据失败
               return;
            }
         }
         else {
             return $eus;
         }
         
         //判断是新报名还是审批,0:为新报名 ；大于0:审批
         if($this->Status == 0){
            $str_deptId = "select DeptId from users where userid = " . $this->UserId;
            if($rs = mysqli_query($link, $str_deptId)){
               $row = mysqli_fetch_assoc($rs);
               $deptId = $row["DeptId"];
            }
         }else{
            $str_deptId = "select ParentId from Depts where DeptId = " . $this->DeptId;
            if($rs = mysqli_query($link, $str_deptId)){
               $row = mysqli_fetch_assoc($rs);
               $deptId = $row["ParentId"];
            }
         }
         
         //获取审核人员信息
         $str_users = "select * from users where deptid=$deptId and CanApprove=1";
         if($rs = mysqli_query($link, $str_users)){
            $usercount = mysqli_num_rows($rs);
            while ($row = mysqli_fetch_assoc($rs)) {
               $eu = new EmailUser();
               $eu->Email = $row["Email"];
               $eu->Status = $this->Status + 1;
               $eu->DeptId = $deptId;
               $eu->TrainingId = $this->TrainingId;
               $eu->UserId = $this->UserId;
               array_push($eus,$eu);
            }
         }
         mysqli_close($link);
         return $eus;
      }
   }

   class EmailUser{
      public $Email;
      public $Status;
      public $TrainingId;
      public $DeptId;
      public $UserId;
   }
?>