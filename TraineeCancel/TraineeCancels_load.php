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
      if(strcmp($check_str, "searchTraineeCancels"))
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
   if(($searchTraineeCancelsNameSpeaker = check_name($_GET["searchTraineeCancelsNameSpeaker"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
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
   $str_query1 = "select te.TrainingId, te.UserId, te.RegisterDate, te.Status, ti.TrainingName, ti.TrainingMemo, ti.ApproreLevel, u.UserName, u.EmployeeId, ti.SpeakerName, te.CancelMsg 
from traineeCancels as te left join trainings as ti on te.TrainingId = ti.TrainingId
left join users as u on te.UserId = u.UserId where te.Status >=0 and ExamineUser like '%,$user_id,%' and te.Status <> ti.ApproreLevel";
   
   //TODO: trim space
   if (strlen($searchTraineeCancelsNameSpeaker) > 0)
   {
      $str_query1 = $str_query1 . " AND (ti.TrainingName like '%$searchTraineesNameSpeaker%' 
      OR ti.SpeakerName like '%$searchTraineesNameSpeaker%' 
      OR u.UserName like '%$searchTraineesNameSpeaker%' 
      OR u.EmployeeId like '%$searchTraineeCancelsNameSpeaker%') ";
   }
   
   //***Step16 页面搜索SQl语句 结束
   
   // echo $str_query1;
   // return;
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
                                      . "<input type=\"hidden\" name=search_TraineeCancel_page_no value=1>"
                                      . "<input type=\"hidden\" name=search_TraineeCancel_page_size value=" . $page_size . ">";
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            //***Step6 function name ==> clickSearchTraineesPage
            $return_string = $return_string . "\" id=search_TraineeCancel_page_begin_no_" . ($i + 1) . " OnClick=clickSearchTraineeCancelsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      //***Step7 function name ==> expandSearchTraineesContentFunc
      $return_string = $return_string . "</span>"
                       . "<span class=\"btn TrainingLogsexpandSR\" OnClick=\"expandSearchTraineeCancelsContentFunc();\">显示过长内容</span>"
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
                                         . "<col class=\"EmployeeId\" />"
                                         . "<col class=\"RegisterDate\" />"
                                         . "<col class=\"Status\" />"
                                         . "<col class=\"CancelMsg\" />"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>课程简介</th>"
                                         . "<th>讲师</th>"
                                         . "<th>学员姓名</th>"
                                         . "<th>学员编号</th>"
                                         . "<th>报名时间</th>"
                                         . "<th>撤销原因</th>"
                                         . "<th>操作</th>"
                                         . "</tr>"
                                         . "<tr>"
                                         . "<td colspan=\"9\" class=\"empty\">请输入上方查询条件，并点选\"开始查询\"</td>"
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
               $return_string = $return_string . "<div id=\"search_TraineeCancel_page" . $page_no . "\" ";
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
                                         . "<col class=\"EmployeeId\" />"
                                         . "<col class=\"RegisterDate\" />"
                                         . "<col class=\"Status\" />"
                                         . "<col class=\"CancelMsg\" />"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>课程简介</th>"
                                         . "<th>讲师</th>"
                                         . "<th>学员姓名</th>"
                                         . "<th>学员编号</th>"
                                         . "<th>报名时间</th>"
                                         . "<th>撤销原因</th>"
                                         . "<th>操作</th>"
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
               $EmployeeId = $row["EmployeeId"];
               $RegisterDate = date("Y-m-d H:i:s", strtotime($row["RegisterDate"]));
               $Status = $row["Status"];
               $CancelMsg = $row["CancelMsg"];
               $page_count_display = $page_count + 1;
               
               $return_string = $return_string 
                  . "<tr>"
                  . "<td>$page_count_display</td>"
                  . "<td><span class=\"TraineeName fixWidth\">$TrainingName</span></td>"
                  . "<td><span class=\"TrainingMemo fixWidth\">$TrainingMemo</span></td>"
                  . "<td><span class=\"SpeakerName fixWidth\">$SpeakerName</span></td>"
                  . "<td><span class=\"UserName breakAll\">$UserName</span></td>"
                  . "<td><span class=\"EmployeeId breakAll\">$EmployeeId</span></td>"
                  . "<td><span class=\"RegisterDate breakAll\">$RegisterDate</span></td>"
                  . "<td><span class=\"CancelMsg fixWidth\">$CancelMsg</span></td>"
                  . "<td><A OnClick=\"actionSearchTraineeCancels($TrainingId,$Status,$UserId);\">撤销审核 同意</A><br/>"
                  . "<A OnClick=\"deleteSearchTraineeCancels($TrainingId,$UserId);\">撤销审核 驳回</A></td>"
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
            $return_string = $return_string . "<span class=\"search_TraineeCancel_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            $return_string = $return_string . "\" id=search_TraineeCancel_page_end_no_" . ($i + 1) . " OnClick=clickSearchTraineeCancelsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      $return_string = $return_string . "</span>"
                       . "<span class=\"btn TrainingLogsexpandSR\" OnClick=\"expandSearchTraineeCancelsContentFunc();\">显示过长内容</span>"
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
