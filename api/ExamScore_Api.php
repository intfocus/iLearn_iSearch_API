<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["uid"])){   //判断所需要的参数是否存在，isset用来检测变量是否设置，返回true or false
         $userid=$_GET["uid"];   //存在
      }
      else{
         echo json_encode(array("status"=>-2, "result"=>"成绩不存在！")); //-2没有传用户ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"成绩不存在！")); //-1没有传任何参数
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
   $deptcount;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   class StuExamScore
   {
      public $ExamId;
      public $UserId;
      public $IsSubmit;
      public $Status;
      public $Score;
   }
   
   $dataExamScore = array();
   $escount = 0;
   
   //----- query -----
   $str_dept = "select er.ExamId, er.UserId, er.IsSubmit, er.Status, es.Score 
from examroll er left join examscore es on er.ExamId = es.ExamId and er.UserId = es.UserId 
where er.UserId=$userid and es.ExamId is not null";
   if($rs = mysqli_query($link, $str_dept)){
      $escount = mysqli_num_rows($rs);
      if ($escount > 0)
      {
         while($row = mysqli_fetch_assoc($rs))
         {
            $es = new StuExamScore();
            $es->ExamId = $row["ExamId"];
            $es->UserId = $row["UserId"];
            $es->IsSubmit = $row["IsSubmit"];
            $es->Status = $row["Status"];
            $es->Score = $row["Score"];
            array_push($dataExamScore, $es);
         }
      }
      else {
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         // echo -__LINE__;
         echo json_encode(array("status"=>0, "result"=>"成绩不存在！")); 
         return;
      }
   }
   
   mysqli_close($link);
   echo json_encode(array(
      "status"=> 1, 
      "count"=>$escount, 
      "data"=>$dataExamScore,
      "result"=>""
   ));      
   return;
?>