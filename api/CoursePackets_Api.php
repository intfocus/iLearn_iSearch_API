<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["uid"])){
         $userid = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"课程包不存在！")); //-2没有传用户ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"课程包不存在！")); //-1没有传任何参数
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
   $cpcount = 0;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datacp = array();
   class Stucps{
      public $Name;
      public $Desc;
      public $AvailableTime;
   }
   
   //----- query -----
   $str_cp = "select CoursePacketName, CoursePacketDesc, AvailableTimeEnd from CoursePacket 
      where status = 1 and TIMESTAMPDIFF(DAY, now(),date(AvailableTimeEnd)) >= 0 
      and TIMESTAMPDIFF(DAY, date(AvailableTimeBegin),now()) >=0 
      and UserList like '%," . $userid .",%'";
   if($rs = mysqli_query($link, $str_cp)){
      $cpcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sc = new Stucps();
         $sc->Name = $row['CoursePacketName'];
         $sc->Desc = $row['CoursePacketDesc'];
         $sc->AvailableTime = date("Y/m/d",strtotime($row['AvailableTimeEnd']));
         array_push($datacp,$sc);
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$cpcount, "cpdata"=>$datacp, "result"=>"课程包获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$cpcount, "cpdata"=>$datacp, "result"=>""));      
   return;
?>
