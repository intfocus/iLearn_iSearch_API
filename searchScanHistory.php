<?php
///////////////////////////////
// searchScanHistory.php
//
// Modified from searchUserMgmt.php
// Search the history scan info for each computer
// Called by OSC_index.php => 紀錄頁面
// 2013/04/02 created by Odie
// 
// #001 modified by Odie 2013/04/26
//  To support new feature: mutli-level admin
//     1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//        admin => 1
//        user  => 2
//     2. If user, restrict the departments he can see
///////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
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
   if (!session_is_registered("GUID") || !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "" || $_SESSION["loginLevel"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   $login_level = $_SESSION["loginLevel"];
   $login_name = $_SESSION["loginName"];
   session_write_close();
   
   //$GUID = "8f44a8ab_5c6c_6232_cd4f_642761007428";
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(STR_LENGTH, 50);
   define(EXTREME_TYPE_NUMBER, '7');                      //個資類型
   define(SEARCH_RISK_LIMIT, 3);                          //極高+高
   define(SEARCH_SIZE, 1000);                             //上限1000筆
   define(PAGE_SIZE, 100);
   define(SEARCH_EXTREME, 2);
   define(SEARCH_HIGH, 1);
   define(TRIAL, 0);

   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(MAPPING_ERROR, -5);

   define(DELETED, "-1");
   define(WAITING_UPLOAD, "0");
   define(COMPLETED, "1");
   define(DROPPED, "2");
   define(PARSE_FAIL, "3");
   define(WAITING_PARSE, "4");

   define(DELETED_STATUS, "已刪除");
   define(WAITING_STATUS, "清查中");
   define(COMPLETED_STATUS, "已完成");
   define(DROPPED_STATUS, "已逾時");
   define(NOTYET_STATUS, "未實施");
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "search"))
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
   //----- Check time range end -----
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
      if($check_str <= 0 || $check_str > SEARCH_RISK_LIMIT)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   //get data from client
   $cmd;
   $scan_history_keyword;
   $range_begin;
   $range_end;

   //query
   $link;
   $sql;
   $result;                 //query result
   $row;                    //1 data array
   
   //data
   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($scan_history_keyword = check_name($_GET["scan_history_keyword"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($range_begin = check_range_begin($_GET["range_begin"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($range_end = check_range_end($_GET["range_end"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   ////////////////
   // for keyword search, only apply to domain_name, hostname, department, and employee_name
   ////////////////
   $scan_history_keyword_str = "";
   if($scan_history_keyword != "")
   {
      if(!get_magic_quotes_gpc())
         $scan_history_keyword = mysql_real_escape_string($scan_history_keyword);

      $scan_history_keyword_str = " and 
          (e.hostname like '%$scan_history_keyword%' or
           e.domain_name like '%$scan_history_keyword%' or
           i.employee_name like '%$scan_history_keyword%' or
           i.department like '%$scan_history_keyword%')
           ";
   }

   $time_range = "";
   //create time_range
   if($range_begin != "" && $range_end != "")
      $time_range = "and (last_modified_time between '$range_begin' and '$range_end')";
   else if($range_begin == "" && $range_end != "")
      $time_range = "and (last_modified_time < '$range_end')";
   else if($range_begin != "" && $range_end == "")
      $time_range = "and (last_modified_time > '$range_begin')";
   else if($range_begin == "" && $range_end == "")
      $time_range = "";

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if(!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   ////////////////
   // #001
   // if login_level == 2, get "dept_list" from "userLogin"
   ////////////////

   $dept_list = "";
   $arr_dept_list = array();
   if($login_level == 2){
      $sql = "
         select dept_list from userLogin where GUID = '$GUID' and login_name = '$login_name'
         ";
      if($result = mysqli_query($link, $sql)){
         $row_num = mysqli_num_rows($result);
         if($row_num != 1){
            if($link){
               mysqli_close($link);
            }
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }
         $row = mysqli_fetch_assoc($result);
         $dept_list = $row["dept_list"];
      }
      else{
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }
   }

   ///////////////
   // prepare query and query DB
   // #001, a user can only see some records of his departments
   ///////////////
   
   if($login_level == 1){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
         e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
            from (select * from entry where GUID = '$GUID' $time_range) e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
            on e.entryID = i.entryID where 1 $scan_history_keyword_str
         order by e.create_time DESC
         ";
   }
   else if($login_level == 2){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
         e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
            from (select * from entry where GUID = '$GUID' $time_range) e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
            on e.entryID = i.entryID where i.department in ($dept_list) $scan_history_keyword_str
         order by e.create_time DESC
         ";
   }
   $total_count = 0;
   $completed_count = 0;
   $waiting_count = 0;
   $arr_result = array();
   
   ////////////////
   // put the results into an array for further display (need to be divided into several pages)
   ////////////////
   if($result = mysqli_query($link, $sql)){
      while($row = mysqli_fetch_assoc($result)){
         $e_entryID = $row["e_entryID"];
         $i_entryID = $row["i_entryID"];
         $xmlID = $row["XMLID"];
         $hostname = $row["hostname"];
         $domain_name = $row["domain_name"];
         $nFile = $row["nFile"];
         $department = $row["department"];
         $employee_name = $row["employee_name"];
         $e_status = $row["e_status"];
         $i_status = $row["i_status"];
         
         if($row["start_time"] != null)
            $start_time = $row["start_time"];
         else
            $start_time = $row["create_time"];
       
         if($row["end_time"] != null)
            $end_time = $row["end_time"];
         else
            $end_time = $row["upload_time"];
         
         if($start_time != null){
            $start_time1 = substr($start_time,0,10);
            $start_time2 = substr($start_time,11,20);
            $start_time = $start_time1 . "<br>" . $start_time2;
         }
         else
            $start_time = "";

         if($end_time != null){
            $end_time1 = substr($end_time,0,10);
            $end_time2 = substr($end_time,11,20);
            $end_time = $end_time1 . "<br>" . $end_time2;
         }
         else
            $end_time = "";

         $pc_name = "$domain_name / $hostname";

         if($e_status === COMPLETED || $e_status === DELETED){
            $total_count++;
            $completed_count++;
            $arr_result[] =
                 "<tr>
                  <td><span class=\"cIndex\">$total_count</span></td>
                  <td><span class=\"cName\">$pc_name</span></td>
                  <td><span class=\"cMember\">$employee_name</span></td>
                  <td><span class=\"cDpmt\">$department</span></td>
                  <td><span class=\"time\">$start_time</span></td>
                  <td><span class=\"time\">$end_time</span></td>
                  </tr>";
         }
         else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE){
            $total_count++;
            $waiting_count++;
            $arr_result[] =
                 "<tr>
                  <td><span class=\"cIndex\">$total_count</span></td>
                  <td><span class=\"cName\">$pc_name</span></td>
                  <td><span class=\"cMember\">$employee_name</span></td>
                  <td><span class=\"cDpmt\">$department</span></td>
                  <td><span class=\"time\">$start_time</span></td>
                  <td><span class=\"time\">". WAITING_STATUS. "</span></td>
                  </tr>";
         }
      }  // end of while($row = mysqli_fetch_assoc($result))
      mysqli_free_result($result);
   }
   else{
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
   
   if($link){
      mysqli_close($link);
   }

   //////////////////
   // print report pages in the up right position
   //////////////////
   $return_string = "";

   $page_default_no = 1;
   $page_num = (int)(($total_count - 1) / PAGE_SIZE + 1);
   
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">"
                                   . "<input type=\"hidden\" id=scan_no value=$total_count>"
                                   . "<input type=\"hidden\" name=scan_page_no value=1>"
                                   . "<input type=\"hidden\" name=scan_page_size value=" . PAGE_SIZE . ">";
   if($page_num > 1){
     	for($i = 0; $i < $page_num; $i++){
         $return_string = $return_string . "<span class=\"page";
         if($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=scan_page_begin_no_" . ($i + 1) . " OnClick=clickScanPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   //////////////////
   // print statistics about the total counts for completed scan
   //////////////////
   $return_string = $return_string . "</span><span id=\"countStrScan\"></span>";
                                  
   //////////////////
   // print report table
   //////////////////

   // no data
   if($total_count == 0){
      $return_string = $return_string . "<table id=\"scan_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">".
                              "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                                 <colgroup>
                                    <col class=\"cIndex\" />
                                    <col class=\"cName\" />
                                    <col class=\"cMember\" />
                                    <col class=\"cDpmt\" />
                                    <col class=\"time\" />
                                    <col class=\"time\" />
                                 </colgroup>
                              <tr>
                                 <th>序號</span></th>
                                 <th>電腦名稱</th>
                                 <th>人員名稱</th>
                                 <th>部門</th>
                                 <th>開始時間</th>
                                 <th>完成時間</th>
                              </tr>
                              <tr>
                                 <td colspan=\"6\" class=\"empty\">無任何結果，請重新查詢</td>
                              </tr>";
   }
   // with data
   else{
      $i = 0;
      $page_no = 1;
      $page_count = 0;
      while($i < $total_count){
         //----- If No Data -----
         if($page_count == 0){
            $return_string = $return_string . "<div id=\"scan_page" . $page_no . "\" ";
            if($page_no == 1)
               $return_string = $return_string . "style=\"display:block; padding-top:10px\"";
            else
               $return_string = $return_string . "style=\"display:none; padding-top:10px\"";
            $return_string = $return_string . ">".
                              "<table id=\"scan_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">".
                                 "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                                    <colgroup>
                                       <col class=\"cIndex\" />
                                       <col class=\"cName\" />
                                       <col class=\"cMember\" />
                                       <col class=\"cDpmt\" />
                                       <col class=\"time\" />
                                       <col class=\"time\" />
                                    </colgroup>
                                 <tr>
                                    <th>序號</span></th>
                                    <th>電腦名稱</th>
                                    <th>人員名稱</th>
                                    <th>部門</th>
                                    <th>開始時間</th>
                                    <th>完成時間</th>
                                    </tr>";
         }
         if($page_count < PAGE_SIZE){
            $return_string = $return_string . $arr_result[$i];
            $i++;
            $page_count++;
            if($page_count == PAGE_SIZE){
               $return_string = $return_string . "</table>"
                                               . "</div>";
               $page_no++;
               $page_count = 0;
            }                    
         }
      }
      if($page_count > 0){
         $return_string = $return_string . "</table>"
                                         . "</div>";
      }               
   }
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
      
   /////////////////////
   // print report pages in right bottom position
   /////////////////////
   if($page_num > 1){
     	for ($i = 0; $i < $page_num; $i++){
         $return_string = $return_string . "<span class=\"page";
         if($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=scan_page_end_no_" . ($i + 1) . " OnClick=clickScanPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }      
   $return_string = $return_string . "</span>"
                                   . "</div>";
   echo $return_string;

   ////////////////
   // print table end 
   ///////////////
   echo "<form>";
   echo "<input type=hidden name=completed value=$completed_count>";
   echo "<input type=hidden name=searching value=$waiting_count>";
   echo "</form>";
?>
