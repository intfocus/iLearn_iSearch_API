<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["cpid"])){
         $cpid = $_GET["cpid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"课程包不存在！")); //-2没有传课程包ID
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
   
   class Stuppt{
      public $PPTName;
      public $PPTList;
      public $PPTDesc;
   }
   
   $datacpstr = array();
   function CPList($CPstr)
   {
      $strlink = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      $CPstr = substr($CPstr,1);
      $CPstr = substr($CPstr,0,-1);
      $CPstr = str_replace(",,",",",$CPstr);
      $str_ppt = "select PPTName, CoursewareList, CoursewareDesc from ppts where PPTId in ($CPstr)";
      if($rsppt = mysqli_query($strlink, $str_ppt)){
         while($row = mysqli_fetch_assoc($rsppt)){
            $scc = new StuC();      
            $scppt->PPTName = $row['PPTName'];
            $scppt->PPTList = CList($row['CoursewareList']);
            $scppt->PPTDesc = $row['CoursewareDesc'];
            array_push($datacpstr, $scppt);
         }
      }
      else
      {
         if($strlink){
            mysqli_close($strlink);
         }
         sleep(DELAY_SEC);
         return;
      }
      mysqli_close($strlink);
      return $datacpstr;
   }
   
   class StuC{
      public $CoursewareId;
      public $CoursewareName;
      public $CoursewareDesc;
      public $CoursewareFile;
      public $Extension;
   }
   
   function CList($Cstr)
   {
      $strlink = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      $datacstr = array();
      $Cstr = substr($Cstr,1);
      $Cstr = substr($Cstr,0,-1);
      $Cstr = str_replace(",,",",",$Cstr);
      $str_c = "select CoursewareId, CoursewareName, CoursewareDesc, CoursewareFile from Coursewares where Status = 1 and CoursewareId in ($Cstr)";
      if($rsc = mysqli_query($strlink, $str_c)){
         while($row = mysqli_fetch_assoc($rsc)){
            $scc = new StuC();      
            $scc->CoursewareId = $row['CoursewareId'];
            $scc->CoursewareName = $row['CoursewareName'];
            $scc->CoursewareDesc = $row['CoursewareDesc'];
            $scc->CoursewareFile = $row['CoursewareFile'];
            $extensions = explode('.',$row['CoursewareFile']);
            $escount = count($extensions)-1;
            $scc->Extension = $extensions[$escount];
            array_push($datacstr, $scc);
         }
      }
      else
      {
         if($strlink){
            mysqli_close($strlink);
         }
         sleep(DELAY_SEC);
         return;
      }
      mysqli_close($strlink);
      return $datacstr;
   }
   
   class StuE{
      public $ExamId;
      public $ExamName;
      public $ExamType;
      public $ExamLocation;
      public $ExamAnsType;
      public $ExamPassword;
      public $ExamDesc;
      public $ExamContent;
      public $Duration;
      public $AllowTime;
      public $QualifyPercent;
   }
   
   function EList($Estr)
   {
      $strlink = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      $dataestr = array();
      $Estr = substr($Estr,1);
      $Estr = substr($Estr,0,-1);
      $Estr = str_replace(",,",",",$Estr);
      $str_e = "select ExamId, ExamName, ExamType, ExamLocation, ExamAnsType, ExamPassword, ExamDesc, ExamContent, Duration, AllowTime, QualifyPercent from exams
             where Status = 1 and ExamId in ($Estr)";
      if($rse = mysqli_query($strlink, $str_e)){
         while($row = mysqli_fetch_assoc($rse)){
            $sce = new StuE();
            $sce->ExamId = $row['ExamId'];
            $sce->ExamName = $row['ExamName'];
            $sce->ExamType = $row['ExamType'];
            $sce->ExamLocation = $row['ExamLocation'];
            $sce->ExamAnsType = $row['ExamAnsType'];
            $sce->ExamPassword = $row['ExamPassword'];
            $sce->ExamDesc = $row['ExamDesc'];
            $sce->ExamContent = $row['ExamContent'];
            $sce->AllowTime = $row['AllowTime'];
            $sce->QualifyPercent = $row['QualifyPercent'];
            array_push($dataestr, $sce);
         }
      }
      else
      {
         if($strlink){
            mysqli_close($strlink);
         }
         sleep(DELAY_SEC);
         return;
      }
      mysqli_close($strlink);
      return $dataestr;
   }
   
   function QList($Qstr)
   {
      return array();
   }
   
   $datacp = array();
   class Stucp{
      public $Name;
      public $Desc;
      public $AvailableTime;
      public $CoursewarePacketList;
      public $CoursewareList;
      public $QuestionnaireList;
      public $ExamList;
      public $Status;
      public $EditTime;
   }
   
   //----- query -----
   $sc = new Stucp();
   $str_cp = "select CoursePacketName, CoursePacketDesc, AvailableTime, Status, CoursewarePacketList, CoursewareList, QuestionnaireList, ExamList, EditTime from CoursePacket 
      where Status = 1 and CoursePacketId = " . $cpid;
   if($rs = mysqli_query($link, $str_cp)){
      $cpcount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sc->Name = $row['CoursePacketName'];
         $sc->Desc = $row['CoursePacketDesc'];
         $sc->AvailableTime = date("Y/m/d",strtotime($row['AvailableTime']));
         $sc->CoursewarePacketList = CPList($row['CoursewarePacketList']);
         $sc->CoursewareList = CList($row['CoursewareList']);
         $sc->QuestionnaireList = QList($row['QuestionnaireList']);
         $sc->ExamList = EList($row['ExamList']);
         $sc->Status = $row['Status'];
         $sc->EditTime = $row['EditTime'];
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo json_encode(array("status"=> 0, "count"=>$cpcount, "cpdata"=>array(), "result"=>"课程包获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$cpcount, "cpdata"=>$sc, "result"=>""));      
   return;
?>
