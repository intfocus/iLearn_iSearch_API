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
   
   session_start();
   if ($_SESSION["GUID"] == "" || $_SESSION["username"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      $return_string = "<div id=\"sResultTitle\" class=\"sResultTitle\">Session 已经过期，请重新登录！</div>";
      echo $return_string;
      exit();
   }
   $user_id = $_SESSION["GUID"];
   $login_name = $_SESSION["username"];
   // $login_name = "Phantom";
   // $user_id = 1;
   $current_func_name = "iSearch";
   session_write_close();
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                          //illegal char
   define("STR_LENGTH", 50);
   define("SEARCH_SIZE", 1000);                             //上限1000笔数
   define("PAGE_SIZE", 100);                                //设置列表显示笔数

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "searchTrainingLogs"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //----- Check name -----
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {
         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt;", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt;", $check_str);
      }
      return $check_str;
   }
   //----- Check time range begin -----
   function check_range_begin($check_str)
   {
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return $check_str;
      }
      //----- format begin range mm/dd/yy to yyyy-mm-dd 00:00:00 -----
      date_default_timezone_set(TIME_ZONE);
      $check_str = $check_str . " 00:00:00";
      if(($check_str = strtotime($check_str)) == "")
      {
         //----- str to time failure -----
         return SYMBOL_ERROR;
      }
      $check_str = date("Y-m-d H:i:s", $check_str);
      return $check_str; 
   }
   //----- Check report range end -----
   function check_range_end($check_str)
   {
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return $check_str;
      }
      //----- format end range mm/dd/yy to yyyy-mm-dd 23:59:59 -----
      date_default_timezone_set(TIME_ZONE);
      $check_str = $check_str . " 23:59:59";

      if(($check_str = strtotime($check_str)) == "")
      {
         //----- str to time failure -----
         return SYMBOL_ERROR;
      }
      $check_str = date("Y-m-d H:i:s", $check_str);
      return $check_str;
   }
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str < 0)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //----- Check encrypt setting -----
   function check_encrypt($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str != 0 && $check_str != 1)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $TraineeTitle;
   $TraineeMsg;
   $Status;
   $EditTime;
   $OccurTime;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($searchTrainingLogsNameSpeaker = check_name($_GET["searchTrainingLogsNameSpeaker"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   function get_employ_id_from_usernames($userids)
   {
      if(strlen($userids)>0){
         $userids = substr($userids,1);
         $userids = substr($userids,0,-1);
         $userids = str_replace(",,",",",$userids);
      }
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;
         die("连接DB失败");
      }
      
      $strusernames = "";
      $str_query = "select * from users where UserId in ($userids)";
      if($result = mysqli_query($link, $str_query)){
         while($row = mysqli_fetch_assoc($result)){
            $strusernames = $strusernames . $row["UserName"] . "<br />";
         }
         return $strusernames;
      }
      else
      {
         //echo DB_ERROR;
         //die("操作资料库失败");
         echo "";
      }
   }

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);   
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   
   
   //----- query -----
   //***Step16 页面搜索SQl语句 起始
   $str_query1 = "select tl.LogId, tl.UserId, us.UserName, tis.TrainingMemo, tl.FunctionName, right(tl.ActionName,2) as ActionName, tl.ActionTime, tl.TUserId, tl.TrainingId, ts.TrainingName, ts.SpeakerName, 
ts.ApproreLevel, tc.CancelMsg, us.EmployeeId, tis.ApproreLevel, tes.Status as teStatus, tc.Status as tcStatus, tes.ExamineUser as teUser, tc.ExamineUser as tcUser
from traineelogs as tl left join trainings as ts on tl.TrainingId = ts.TrainingId 
left join users as us on tl.UserId = us.UserId 
left join traineecancels as tc on tl.UserId = tc.UserId and tl.TrainingId = tc.TrainingId 
left join trainings as tis on tl.TrainingId = tis.TrainingId 
left join trainees as tes on tl.TrainingId = tes.TrainingId and tl.UserId = tes.UserId 
where tl.TUserId = $user_id";
   
   //TODO: trim space
   if (strlen($searchTrainingLogsNameSpeaker) > 0)
   {
      $str_query1 = $str_query1 . " AND (ts.TrainingName like '%$searchTrainingLogsNameSpeaker%' 
      OR ts.SpeakerName like '%$searchTrainingLogsNameSpeaker%' 
      OR us.UserName like '%$searchTrainingLogsNameSpeaker%' 
      OR us.EmployeeId like '%$searchTrainingLogsNameSpeaker%') ";
   }
   $str_query1 = $str_query1 . " order by tl.ActionTime desc";
   
   //***Step16 页面搜索SQl语句 结束
   
   //echo $str_query1;
   //return;
   /////////////////////
   // prepare the SQL command and query DB
   /////////////////////
   if($result = mysqli_query($link, $str_query1)){
      $row_number = mysqli_num_rows($result);
      
      //4.return string of the refreshed Pages
      //----- Print Search Pages -----
      $return_string = "";
      $page_default_no = 1;
      $page_size = PAGE_SIZE;
      
      $return_string = $return_string . "<div id=\"sResultTitle\" class=\"sResultTitle\">查询结果 : 共有 <span>" 
                                      . number_format($row_number) 
                                      . "</span> 笔数据符合查询条件</div>";
      if ($row_number > SEARCH_SIZE)
         $row_number = SEARCH_SIZE;
      $page_num = (int)(($row_number - 1) / $page_size + 1);
      $return_string = $return_string . "<div class=\"toolMenu\">"
                                      . "<span class=\"paging\">"
                                      . "<input type=\"hidden\" id=search_no value=$row_number>"
                                      . "<input type=\"hidden\" name=search_TrainingLog_page_no value=1>"
                                      . "<input type=\"hidden\" name=search_TrainingLog_page_size value=" . $page_size . ">";
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            //***Step6 function name ==> clickSearchTraineesPage
            $return_string = $return_string . "\" id=search_TrainingLog_page_begin_no_" . ($i + 1) . " OnClick=clickSearchTrainingLogsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      //***Step7 function name ==> expandSearchTraineesContentFunc
      $return_string = $return_string . "</span>"
                       . "<span class=\"btn TrainingLogsexpandSR\" OnClick=\"expandSearchTrainingLogsContentFunc();\">显示过长内容</span>"
                       . "</div>";                   
      
      //----- Print Search Tables -----
      //***Step8 Field name and field number must be modified. begin
      //Be care of colspan=7 
      //----- If No Data -----
      if ($row_number == 0)
      {
         $return_string = $return_string . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                         . "<colgroup>"
                                         . "<col class=\"num\"/>"
                                         . "<col class=\"TraineeName\" />"
                                         . "<col class=\"TrainingMemo\" />"
                                         . "<col class=\"SpeakerName\" />"
                                         . "<col class=\"UserName\" />"
                                         . "<col class=\"ActionName\" />"
										 . "<col class=\"strteStatus\" />"
                                         . "<col class=\"CancelMsg\" />"
										 . "<col class=\"struser\" />"
                                         . "<col class=\"ActionDate\" />"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>课程简介</th>"
                                         . "<th>讲师</th>"
                                         . "<th>学员姓名</th>"
                                         . "<th>操作</th>"
										 . "<th>审核状态</th>"
                                         . "<th>撤销原因</th>"
										 . "<th>审核人</th>"
                                         . "<th>操作时间</th>"
                                         . "</tr>"
                                         . "<tr>"
                                         . "<td colspan=\"10\" class=\"empty\">请输入上方查询条件，并点选\"开始查询\"</td>"
                                         . "</tr>"
                                         . "</table>";
      }
      else
      {
         $i = 0;
         $page_no = 1;
         $page_count = 0;
         while ($i < $row_number)
         {
            if ($page_count == 0)
            {
               $return_string = $return_string . "<div id=\"search_TraineeExamine_page" . $page_no . "\" ";
               if ($page_no == 1)
                  $return_string = $return_string . "style=\"display:block;\"";
               else
                  $return_string = $return_string . "style=\"display:none;\"";
               $return_string = $return_string . ">"
                                         . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                         . "<colgroup>"
                                         . "<col class=\"num\"/>"
                                         . "<col class=\"TraineeName\" />"
                                         . "<col class=\"TrainingMemo\" />"
                                         . "<col class=\"SpeakerName\" />"
                                         . "<col class=\"UserName\" />"
                                         . "<col class=\"ActionName\" />"
										 . "<col class=\"strteStatus\" />"
                                         . "<col class=\"CancelMsg\" />"
										 . "<col class=\"struser\" />"
                                         . "<col class=\"ActionDate\" />"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>课程简介</th>"
                                         . "<th>讲师</th>"
                                         . "<th>学员姓名</th>"
                                         . "<th>操作</th>"
										 . "<th>审核状态</th>"
                                         . "<th>撤销原因</th>"
										 . "<th>审核人</th>"
                                         . "<th>操作时间</th>"
                                         . "</tr>";
            }
            if ($page_count < $page_size)
            {
               $row = mysqli_fetch_assoc($result);
               $TrainingId = $row["TrainingId"];
               $TrainingName = $row["TrainingName"];
               $TrainingMemo = $row["TrainingMemo"];
               $SpeakerName = $row["SpeakerName"];
               $UserName = $row["UserName"];
               $UserId = $row["UserId"];
               $ActionName = $row["ActionName"];
               $EmployeeId = $row["EmployeeId"];
               $ActionTime = date("Y-m-d H:i:s", strtotime($row["ActionTime"]));
               $CancelMsg = $row["CancelMsg"];
               $page_count_display = $page_count + 1;
			   $ApproreLevel = $row["ApproreLevel"];
			   $teStatus = $row["teStatus"];
			   $tcStatus = $row["tcStatus"];
			   $FunctionName = $row["FunctionName"];
			   $strteStatus = "";
			   $strtcStatus = "";
			   $teUser = $row["teUser"];
			   $tcUser = $row["tcUser"];
			   if($teStatus < 0)
			   {
				   $strteStatus = "驳回";
			   }
			   else
			   {
			      if($teStatus == $ApproreLevel)
			      {
				      $strteStatus = "通过";
			      }
				  else
				  {
				      $strteStatus = "审核中";
			      }
			   }
			   
			   $struser = "";
			   if($tcStatus < 0)
			   {
				   $strtcStatus = "驳回";
			   }
			   else
			   {
			      if($tcStatus == $ApproreLevel)
			      {
				      $strtcStatus = "通过";
			      }
				  else
				  {
				      $strtcStatus = "审核中";
			      }
			   }
			   
			   $strStatus = "";
			   if($FunctionName == "撤销审核")
			   {
			      $strStatus = $strtcStatus;
				  $struser = get_employ_id_from_usernames($tcUser);
			   }
			   
			   if($FunctionName == "报名审核")
			   {
			      $strStatus = $strteStatus;
				  $struser = get_employ_id_from_usernames($teUser);
			   }
               
               $return_string = $return_string 
                  . "<tr class=\"$TrainingId\">"
                  . "<td>$page_count_display</td>"
                  . "<td><span class=\"TraineeName fixWidth\">$TrainingName</span></td>"
                  . "<td><span class=\"TrainingMemo fixWidth\">$TrainingMemo</span></td>"
                  . "<td><span class=\"SpeakerName breakAll\">$SpeakerName</span></td>"
                  . "<td><span class=\"UserName breakAll\">$UserName</span></td>"
                  . "<td><span class=\"RegisterDate breakAll\">$FunctionName</span></td>"
				  . "<td><span class=\"teStatus breakAll\">$ActionName</span></td>"
                  . "<td><span class=\"CancelMsg fixWidth\">$CancelMsg</span></td>"
				  . "<td><span class=\"teStatus breakAll\">$struser</span></td>"
                  . "<td><span class=\"RegisterDate breakAll\">$ActionTime</span></td>"
                  . "</tr>";

               $i++;
               $page_count++;
               if ($page_count == $page_size)
               {
                  $return_string = $return_string . "</table>"
                                                  . "</div>\n";
                  $page_no++;
                  $page_count = 0;
               }
            }
         }
         if ($page_count > 0)
         {
            $return_string = $return_string . "</table>"
                        . "</div>\n";
         }               
      }
      //***Step8 Field name and field number must be modified. end


      $return_string = $return_string . "<div class=\"toolMenu\">"
                        . "<span class=\"paging\">";
      
      //----- Print Search Pages -----
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_TrainingLog_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            $return_string = $return_string . "\" id=search_TrainingLog_page_end_no_" . ($i + 1) . " OnClick=clickSearchTrainingLogsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      $return_string = $return_string . "</span>"
                       . "<span class=\"btn TrainingLogsexpandSR\" OnClick=\"expandSearchTrainingLogsContentFunc();\">显示过长内容</span>"
                                      . "</div>";
      echo $return_string;
      mysqli_free_result($result);
      mysqli_close($link);
      return;
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
?>
