<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["did"])){
         $deptid = $_GET["did"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"公司公告和公司活动不存在！")); //-2没有传部门ID
         return;
      }
      
      if(isset($_GET["strdate"])){
         $strdate = $_GET["strdate"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"公司公告和公司活动不存在！")); //-3没有传发布时间和公告时间不存在
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"公司公告和公司活动不存在！")); //-1没有传任何参数
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
   
   $datagg = array();
   $datahd = array();
   class Stunews{
      public $newtitle;
      public $newmsg;
      public $edittime;
      public $occurtime;
   }
   
   //----- query -----
   $str_newgg = "select NewTitle, NewMsg, EditTime, OccurTime from news 
      where status = 1 and TIMESTAMPDIFF(DAY,date(EditTime),date('$strdate')) <= 30 
      and TIMESTAMPDIFF(DAY,date(EditTime),date('$strdate')) > 0 and OccurTime is null 
      and DeptList like '%," . $deptid .",%'";
   if($rs = mysqli_query($link, $str_newgg)){
      $newcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sn = new Stunews();
         $sn->newtitle = $row['NewTitle'];
         $sn->newmsg = $row['NewMsg'];
         $sn->edittime = $row['EditTime'];
         $sn->occurtime = $row['OccurTime'];
         array_push($datagg,$sn);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$newcount, "ggdata"=>$datagg, "hddata"=>$datahd, "result"=>"公司公告获取失败！")); 
      return;
   }
   
   $str_newhd = "select NewTitle, NewMsg, EditTime, OccurTime from news 
      where status = 1 and TIMESTAMPDIFF(DAY,date(OccurTime),date('$strdate')) <= 0 
      and OccurTime is not null and DeptList like '%," . $deptid . ",%'";
   if($rs = mysqli_query($link, $str_newhd)){
      $newcount = $newcount + mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sn = new Stunews();
         $sn->newtitle = $row['NewTitle'];
         $sn->newmsg = $row['NewMsg'];
         $sn->edittime = $row['EditTime'];
         $sn->occurtime = $row['OccurTime'];
         array_push($datahd,$sn);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$newcount, "ggdata"=>$datagg, "hddata"=>$datahd, "result"=>"公司活动获取失败！")); 
      return;
   }
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$newcount, "ggdata"=>$datagg, "hddata"=>$datahd, "result"=>"公司活动和公司公告获取成功！"));      
   return;
?>
