<?php
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
   
   $dataQuestion = array();
   class StuQuestion{
      public $TemplateId;
      public $Name;
      public $Desc;
      public $StartTime;
      public $EndTime;
      public $CreatedUser;
      public $Status;
      public $Id;
   }
   
   //----- query -----
   $str_file = "select QuestionId, QuestionTemplateId, QuestionName, QuestionDesc, StartTime, EndTime, CreatedUser, Status from Question where Status = 1;";

   if($rs = mysqli_query($link, $str_file)){
      $questioncount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sq = new StuQuestion();
         $sq->Id = (int)$row['QuestionId'];
         $sq->TemplateId = (int)$row['QuestionTemplateId'];
         $sq->Name = $row['QuestionName'];
         $sq->Desc = $row['QuestionDesc'];
         $sq->StartTime = date("Y/m/d H:i:s",strtotime($row['StartTime']));
         $sq->EndTime = date("Y/m/d H:i:s",strtotime($row['EndTime']));
         $sq->CreatedUser = (int)$row['CreatedUser'];
         $sq->Status = (int)$row['Status'];
         array_push($dataQuestion,$sq);
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
      echo json_encode(array("status"=> 0, "result"=>"问卷获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$questioncount, "data"=>$dataQuestion, "result"=>""));      
   return;
?>