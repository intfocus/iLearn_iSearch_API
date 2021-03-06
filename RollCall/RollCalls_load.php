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
      if(strcmp($check_str, "searchRollCalls"))
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
   $RollCallTitle;
   $RollCallMsg;
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
   if(($searchRollCallsName = check_name($_GET["searchRollCallsName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($statusCheckbox = check_number($_GET["statusCheckbox"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($searchRollCallsfrom12 = check_range_begin($_GET["searchRollCallsfrom12"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($searchRollCallsto12 = check_range_end($_GET["searchRollCallsto12"])) == SYMBOL_ERROR)
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
   $str_query1 = "select ti.TrainingName as TrainingName, us.UserName as UserName, rc.IssueDate as IssueDate, rc.Status as Status, rc.Reason as Reason, u.UserName as CreatedUser, rc.UserId, c.CheckInName
,case when locate('0',rc.Reason)>0 then '签到' else '未到' end as Statua
,case when locate('1',rc.Reason)>0 then '迟到' else '' end as Statub 
,case when locate('2',rc.Reason)>0 then '早退' else '' end as Statuc 
from rollcall rc left join trainings ti on rc.TrainingId = ti.TrainingId 
left join users us on rc.UserId = us.UserId left join users u on rc.CreatedUser = u.UserId 
left join checkins c on rc.TrainingId = c.TrainingId and rc.CheckInId = c.CheckInId where c.Status = 1 and rc.status";

   if ($statusCheckbox == 1)
   {
      $str_query1 = $str_query1 . "=1 ";
   }
   else if ($statusCheckbox == 2)
   {
      $str_query1 = $str_query1 . "=0 ";
   }
   else
   {
      $str_query1 = $str_query1 . ">=0 ";
   }
   
   //TODO: trim space
   if (strlen($searchRollCallsName) > 0)
   {
      $str_query1 = $str_query1 . "AND (ti.TrainingName like '%$searchRollCallsName%' OR us.UserName like '%$searchRollCallsName%') ";
   }
   
   if ($searchRollCallsfrom12 != '')
      $str_query1 = $str_query1 . "AND rc.IssueDate >= '$searchRollCallsfrom12' ";
   if ($searchRollCallsto12 != '')
      $str_query1 = $str_query1 . "AND rc.IssueDate <= '$searchRollCallsto12' ";
   $str_query1 = $str_query1 . "ORDER BY IssueDate DESC";
   
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
                                      . "<input type=\"hidden\" name=search_RollCall_page_no value=1>"
                                      . "<input type=\"hidden\" name=search_RollCall_page_size value=" . $page_size . ">";
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_RollCall_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            //***Step6 function name ==> clickSearchRollCallsPage
            $return_string = $return_string . "\" id=search_RollCall_page_begin_no_" . ($i + 1) . " OnClick=clickSearchRollCallsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      //***Step7 function name ==> expandSearchRollCallsContentFunc
      $return_string = $return_string . "</span>"
                       . "<span class=\"btn RollCallsexpandSR\" OnClick=\"expandSearchRollCallsContentFunc();\">显示过长内容</span>"
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
										 . "<col class=\"CheckInName\"/>"
                                         . "<col class=\"RollCallName\"/>"
                                         . "<col class=\"UserName\"/>"
                                         . "<col class=\"IssueDate\"/>"
                                         . "<col class=\"Statua\"/>"
										 . "<col class=\"Statub\"/>"
										 . "<col class=\"Statuc\"/>"
                                         . "<col class=\"CreatedUser\"/>"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
										 . "<th>签到名称</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>用户名称</th>"
                                         . "<th>点名时间</th>"
                                         . "<th>到场状态</th>"
										 . "<th>是否迟到</th>"
										 . "<th>是否早退</th>"
                                         . "<th>发起点名用户</th>"
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
               $return_string = $return_string . "<div id=\"search_RollCall_page" . $page_no . "\" ";
               if ($page_no == 1)
                  $return_string = $return_string . "style=\"display:block;\"";
               else
                  $return_string = $return_string . "style=\"display:none;\"";
               $return_string = $return_string . ">"
                                         . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                         . "<colgroup>"
                                         . "<col class=\"num\"/>"
										 . "<col class=\"CheckInName\"/>"
                                         . "<col class=\"RollCallName\"/>"
                                         . "<col class=\"UserName\"/>"
                                         . "<col class=\"IssueDate\"/>"
                                         . "<col class=\"Statua\"/>"
										 . "<col class=\"Statub\"/>"
										 . "<col class=\"Statuc\"/>"
                                         . "<col class=\"CreatedUser\"/>"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
										 . "<th>签到名称</th>"
                                         . "<th>课程名称</th>"
                                         . "<th>用户名称</th>"
                                         . "<th>点名时间</th>"
                                         . "<th>到场状态</th>"
										 . "<th>是否迟到</th>"
										 . "<th>是否早退</th>"
                                         . "<th>发起点名用户</th>"
                                         . "</tr>";
            }
            if ($page_count < $page_size)
            {
               $row = mysqli_fetch_assoc($result);
			   $CheckInName = $row["CheckInName"];
               $TrainingName = $row["TrainingName"];
               $UserName = $row["UserName"];
               $IssueDate = date("Y-m-d H:i:s", strtotime($row["IssueDate"]));
               $Status = $row["Status"];
			   $Statua = $row["Statua"];
			   $Statub = $row["Statub"];
			   $Statuc = $row["Statuc"];
               $CreatedUser = $row["CreatedUser"];
               $page_count_display = $page_count + 1;
               
               $return_string = $return_string 
                  . "<tr>"
                  . "<td>$page_count_display</td>"
				  . "<td><span class=\"CheckInName fixWidth\">$CheckInName</span></td>"
                  . "<td><span class=\"RollCallName fixWidth\">$TrainingName</span></td>"
                  . "<td>$UserName</td>"
                  . "<td>$IssueDate</td>"
                  . "<td>$Statua</td>"
				  . "<td>$Statub</td>"
				  . "<td>$Statuc</td>"
                  . "<td>$CreatedUser</td>"
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
                        . "<span class=\"btn RollCallsexpandSR\" OnClick=\"expandSearchRollCallsContentFunc();\">显示过长内容</span>"
                        . "<span class=\"paging\">";
      
      //----- Print Search Pages -----
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_RollCall_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            $return_string = $return_string . "\" id=search_RollCall_page_end_no_" . ($i + 1) . " OnClick=clickSearchRollCallsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      $return_string = $return_string . "</span>"
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
