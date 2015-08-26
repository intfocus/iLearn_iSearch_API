<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["eid"])){
         $ExamId = $_GET["eid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"回答信息不存在！")); //-2没有传考试ID
         return;
      }
      if(isset($_GET["uid"])){
         $UserId = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"回答信息不存在！")); //-2没有传考生ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"回答信息不存在！")); //-1没有传任何参数
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
   
   $dataexamanswers = array();
   class StuExamAnswers{
      public $ExamId;
      public $ProblemId;
      public $UserId;
      public $ProblemType;
      public $Score;
      public $Answer;
      public $Result;
      public $CreatedTime;
   }
   
   //----- query -----
   $str_examanswer = "select ExamId, ProblemId, UserId, ProblemType, Score, Answer, Result, CreatedTime from examanswer 
			where ExamId = $ExamId and UserId = $UserId";

   if($rs = mysqli_query($link, $str_examanswer)){
      $examanswercount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){
         $sea = new StuExamAnswers();
         $sea->ExamId = $row['ExamId'];
         $sea->ProblemId = $row['ProblemId'];
         $sea->UserId = $row['UserId'];
         $sea->CreatedTime = date("Y/m/d",strtotime($row['CreatedTime']));
         $sea->ProblemType = $row['ProblemType'];
         $sea->Score = $row['Score'];
         $sea->Answer = $row['Answer'];
         $sea->Result = $row['Result'];
         array_push($dataexamanswers,$sea);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$examanswercount, "examanswersdata"=>$dataexamanswers, "result"=>"回答信息不存在！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$examanswercount, "examanswersdata"=>$dataexamanswers, "result"=>""));      
   return;
?>