<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["qid"])){
         $qid = $_GET["qid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"问卷不存在！")); //-2没有传部门ID
         return; 
      }
   }
   else{
      echo json_encode(array("status"=>-1, "result"=>"问卷不存在！")); //-1没有传任何参数
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
   
   $Id;
   $Name;
   $Desc;
   
   $dataqd = array();
   class StuQuestionDetail{
      public $ProblemId;
      public $QuestionTemplateId;
      public $ProblemType;
      public $ProblemDesc;
      public $ProblemSelectA;
      public $ProblemSelectB;
      public $ProblemSelectC;
      public $ProblemSelectD;
      public $ProblemSelectE;
      public $ProblemSelectF;
      public $ProblemSelectG;
      public $ProblemSelectH;
      public $ProblemSelectI;
      public $GroupName;
   }
   
   //----- query -----
   $str_qt = "select q.QuestionId, q.QuestionName, q.QuestionDesc, q.StartTime, q.EndTime, qt.QuestionTemplateId, qt.QuestionTemplateName, 
               qt.QuestionTemplateDesc, q.Status 
               from Question q left join QuestionTemplate qt 
               on q.QuestionTemplateId = qt.QuestionTemplateId 
               where q.Status = 1 and q.QuestionId = $qid;";
   
   if($rs = mysqli_query($link, $str_qt)){
      $row = mysqli_fetch_assoc($rs);
      $Id = (int)$row["QuestionId"];
      $Name = $row["QuestionName"];
      $Desc = $row["QuestionDesc"];
      $STime = $row["StartTime"];
      $ETime = $row["EndTime"];
      $TId = (int)$row["QuestionTemplateId"];
      $TName = $row["QuestionTemplateName"];
      $TDesc = $row["QuestionTemplateDesc"];
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      // echo -__LINE__;
      echo json_encode(array("status"=> 0, "result"=>"问卷获取失败！")); 
      return;
   }
   
   $str_qt = "select ProblemId, QuestionTemplateId, ProblemType, ProblemDesc, ProblemSelectA, ProblemSelectB, ProblemSelectC, ProblemSelectD, 
      ProblemSelectE, ProblemSelectF, ProblemSelectG, ProblemSelectH, ProblemSelectI, GroupName from QuestionDetail where QuestionTemplateId = $TId;";
   
   if($rs = mysqli_query($link, $str_qt)){
      $qtcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $qd = new StuQuestionDetail();
         $qd->ProblemId = (int)$row["ProblemId"];
         $qd->QuestionTemplateId = (int)$row["QuestionTemplateId"];
         $qd->ProblemType = (int)$row["ProblemType"];
         $qd->ProblemDesc = $row["ProblemDesc"];
         $qd->ProblemSelectA = $row["ProblemSelectA"] == "" ? null : $row["ProblemSelectA"];
         $qd->ProblemSelectB = $row["ProblemSelectB"] == "" ? null : $row["ProblemSelectB"];
         $qd->ProblemSelectC = $row["ProblemSelectC"] == "" ? null : $row["ProblemSelectC"];
         $qd->ProblemSelectD = $row["ProblemSelectD"] == "" ? null : $row["ProblemSelectD"];
         $qd->ProblemSelectE = $row["ProblemSelectE"] == "" ? null : $row["ProblemSelectE"];
         $qd->ProblemSelectF = $row["ProblemSelectF"] == "" ? null : $row["ProblemSelectF"];
         $qd->ProblemSelectG = $row["ProblemSelectG"] == "" ? null : $row["ProblemSelectG"];
         $qd->ProblemSelectH = $row["ProblemSelectH"] == "" ? null : $row["ProblemSelectH"];
         $qd->ProblemSelectI = $row["ProblemSelectI"] == "" ? null : $row["ProblemSelectI"];
         $qd->GroupName = $row["GroupName"];
         array_push($dataqd,$qd);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      // echo -__LINE__;
      echo json_encode(array("status"=> 0, "result"=>"问卷获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("Id"=> $Id, "Name" => $Name, "Desc" => $Desc, "StartTime" => $STime, "EndTime" => $ETime, "status"=> 1, "data"=> $dataqd, "result"=>""));      
   return;
?>