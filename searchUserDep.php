<?php
///////////////////////////////
//searchUserDep.php
//
// Modified from searchUserMgmt.php
// According to the submit criteria, count the number of computers of each status in every department
//
// 2013/02/05 created by Odie
// #001 modified by Odie 2013/04/08
//      Rewrite most part due to new feature and new sql
// 
// #002 modified by Odie, 2013/04/26
//      To support new feature: mutli-level admin
//      1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//        admin => 1
//        user  => 2
//      2. If user, restrict the departments he can see
//
// #003 modified by Odie, 2015/02/12
//      Fix SQL bug that keyword search is not working for Not Yet type
// 
///////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DISPLAY_TEMPLATE, "/usr/local/www/apache22/data/comStatusDepartmentReport.html");
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   
   // #002, add checking $_SESSION["loginLevel"] and $_SESSION["loginName"]
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
   define(PAGE_SIZE, 1000);
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
   $userMgmt_type;
   $userMgmt_targetCom;
   $userMgmt_keyword;
   $range_begin;
   $range_end;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   $total_count=0;
   $count1=0;
   $count2=0;
   $count3=0;
   $count4=0;
   
   //data
   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($userMgmt_type = check_name($_GET["userMgmt_type"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($userMgmt_targetCom = check_name($_GET["userMgmt_targetCom"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($userMgmt_keyword = check_name($_GET["userMgmt_keyword"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($range_begin = check_range_begin($_GET["range_begin"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($range_end = check_range_end($_GET["range_end"])) == SYMBOL_ERROR){
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   ////////////////
   // print table header
   ///////////////
   echo "<div class=\"toolMenu\">";

   ////////////////
   //#002, users don't have the privilege to upload computer list
   ////////////////

   if($login_level == 1){
?>

                  <span class="btn new" OnClick="uploadAsset();">匯入用戶端電腦清單</span>
                  &nbsp;&nbsp;
                  <span class="btn new" OnClick="uploadReplace();">匯入替換電腦清單</span>
                  &nbsp;&nbsp;
<?php
   }
?>
                 <span id="countStr"></span>
              </div>
              <table class="report" border="0" cellspacing="0" cellpadding="0">
                 <colgroup>
                    <col class="department" />
                    <col class="complete" />
                    <col class="search" />
                    <col class="expire" />
                    <col class="notyet" />
                 </colgroup>
                 <tr>
                    <th>部門</th>
                    <th>已完成</th>
                    <th>清查中</th>
                    <th>未實施</th>
                    <th>已逾時</th>
                 </tr>
<?php
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if(!$link)  //connect to server failure
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   ////////////////
   // Update entry, copy department, employee_name from computerList
   ///////////////
   $sql = "update entry e, computerList c 
              set e.department=c.department,
                  e.employee_name=c.employee_name
              where e.domain_name = c.domain_name and
                    e.hostname = c.hostname and
                    e.GUID = c.GUID
          ";

   if($result = mysqli_query($link, $sql)){
      //mysqli_free_result($result);
   }
   else{
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   ////////////////
   // for keyword search, only apply to domain_name, hostname, department, and employee_name
   ////////////////
   $userMgmt_keyword_str_1 = "";
   $userMgmt_keyword_str_2 = "";
   if($userMgmt_keyword != "")
   {
      if(!get_magic_quotes_gpc())
         $userMgmt_keyword = mysql_real_escape_string($userMgmt_keyword);

      // #003 
      $userMgmt_keyword_str_1 = " and 
          (e.hostname like '%$userMgmt_keyword%' or
           e.domain_name like '%$userMgmt_keyword%' or
           e.employee_name like '%$userMgmt_keyword%' or
           e.department like '%$userMgmt_keyword%')
      ";
      // keyword_str_2 is for not yet type
      $userMgmt_keyword_str_2 = " and 
          (i.hostname like '%$userMgmt_keyword%' or
           i.domain_name like '%$userMgmt_keyword%' or
           i.employee_name like '%$userMgmt_keyword%' or
           i.department like '%$userMgmt_keyword%')
      ";
   }

   ////////////////
   // for inside the ComputerList and outside the ComputerList 
   ////////////////
   $userMgmt_targetCom_str = "";
   if($userMgmt_targetCom == "inside")
   {
      $userMgmt_targetCom_str = " and
         exists (select * from computerList 
                where GUID=e.GUID and hostname=e.hostname and domain_name=e.domain_name)
      ";
   }
   else if($userMgmt_targetCom == "outside")
   {
      $userMgmt_targetCom_str = " and
         not exists (select * from computerList 
                    where GUID=e.GUID and hostname=e.hostname and domain_name=e.domain_name)
      ";
   }

   /////////////////
   // for time range, create 2 SQL queries for later use
   // 1. time_range_1 => completed, waiting, dropped
   // 2. time_range_2 => not yet 
   /////////////////

   $time_range_1 = "";
   $time_range_2 = "";

   //create time_range
   if($range_begin != "" && $range_end != "")
   {
      $time_range_1 = "and (e2.last_modified_time between '$range_begin' and '$range_end')";
      $time_range_2 = "and (last_modified_time between '$range_begin' and '$range_end')";
   }
   else if($range_begin == "" && $range_end != "")
   {
      $time_range_1 = "and (e2.last_modified_time < '$range_end')";
      $time_range_2 = "and (last_modified_time < '$range_end')";
   }
   else if($range_begin != "" && $range_end == "")
   {
      $time_range_1 = "and (e2.last_modified_time > '$range_begin')";
      $time_range_2 = "and (last_modified_time > '$range_begin')";
   }
   else if($range_begin == "" && $range_end == "")
   {
      $time_range_1 = "";
      $time_range_2 = "";
   }

   ////////////////
   // #002
   // if login_level == 2, get "dept_list" from "userLogin"
   ////////////////

   $dept_list = "";
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

   /////////////////////
   // prepare the SQL command and query DB
   // #002, a user can only see some records of his departments
   ////////////////

   if($login_level == 1){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
            e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
               from (select * from entry e1 where e1.GUID = '$GUID' and e1.entryID = 
                  (select max(entryID) from entry e2 where e1.domain_name = e2.domain_name and e1.hostname = e2.hostname and e1.GUID = e2.GUID $time_range_1)) e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
            on e.entryID = i.entryID where 1 $userMgmt_keyword_str_1 $userMgmt_targetCom_str
         order by e.create_time DESC
         ";
   }
   else if($login_level == 2){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
            e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
               from (select * from entry e1 where e1.GUID = '$GUID' and e1.entryID = 
                  (select max(entryID) from entry e2 where e1.domain_name = e2.domain_name and e1.hostname = e2.hostname and e1.GUID = e2.GUID $time_range_1)) e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
            on e.entryID = i.entryID where i.department in ($dept_list) $userMgmt_keyword_str_1 $userMgmt_targetCom_str
         order by e.create_time DESC
         ";
   }
   $arr_department = array();
   $arr_completed = array();
   $arr_dropped = array();
   $arr_waiting = array();
   $arr_notyet = array();
   $total_count = 0;
   $complete_count = 0;
   $waiting_count = 0;
   $dropped_count = 0;
   $notyet_count = 0;
   $count1 = 0;
   $count2 = 0;
   $count3 = 0;
   $count4 = 0;
   
   if($result = mysqli_query($link, $sql)){
      while($row = mysqli_fetch_assoc($result)){
         $e_entryID = $row["e_entryID"];
         $i_entryID = $row["i_entryID"];
         $xmlID = $row["xmlID"];
         $entryID = $row["entryID"];
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

         $domain_name = strtoupper($domain_name);
         $hostname = strtoupper($hostname);
         $pc_name = "$domain_name / $hostname";

         ///////////////////
         // check if the department occurs for the first time
         // if yes, put it in arr_department
         ///////////////////
         if(!in_array($department, $arr_department))
            $arr_department[] = $department;
  
         ///////////////////
         // check the status of the PC and put it in corresponding array
         ///////////////////
         if($e_status === COMPLETED || $e_status === DELETED){
            if(strpos($userMgmt_type,"completed",0) !== false){
               $total_count++;
               if(!array_key_exists($department, $arr_completed))
                  $arr_completed[$department] = array();
               $str_val = $domain_name. " / ". $hostname. "\t". $employee_name. "\t". $start_time. "\t". $end_time;
               $arr_completed[$department][] = $str_val;
            }
         }
         else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE){
            if(strpos($userMgmt_type,"searching",0) !== false){
               $total_count++;
               if(!array_key_exists($department, $arr_waiting))
                  $arr_waiting[$department] = array();
               $str_val = $domain_name. " / ". $hostname. "\t". $employee_name. "\t". $start_time. "\t". WAITING_STATUS;
               $arr_waiting[$department][] = $str_val;
            }
         }
         else if($e_status === DROPPED || $e_status === PARSE_FAIL){
            if(strpos($userMgmt_type,"expired",0) !== false){
               $total_count++;
               if(!array_key_exists($department, $arr_dropped))
                  $arr_dropped[$department] = array();
               $str_val = $domain_name. " / ". $hostname. "\t". $employee_name. "\t". $start_time. "\t". DROPPED_STATUS;
               $arr_dropped[$department][] = $str_val;
            }
         }
      } // end of while
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

   ////////////////
   // notyet
   // #002, a user can only see some records of his departments
   ////////////////
   if($login_level == 1){
      $sql = "
         select i.domain_name,i.hostname,i.department,i.employee_name 
            from computerList i 
            where GUID = '$GUID' and
               not exists ( 
               select * from entry
                  where domain_name = i.domain_name and
                     hostname = i.hostname and
                     GUID = i.GUID
                     $time_range_2
               )
            $userMgmt_keyword_str_2
      ";
   }
   else if($login_level == 2){
      $sql = "
         select i.domain_name,i.hostname,i.department,i.employee_name 
            from computerList i 
            where GUID = '$GUID' and
               not exists ( 
               select * from entry
                  where domain_name = i.domain_name and
                     hostname = i.hostname and
                     GUID = i.GUID
                     $time_range_2
               )
            and department in ($dept_list) $userMgmt_keyword_str_2
      ";
   }
   if(strpos($userMgmt_type,"notyet",0) !== false){
      if(($result = mysqli_query($link, $sql)) && ($userMgmt_targetCom != "outside"))
      {
         while($row = mysqli_fetch_assoc($result)){
            $total_count++;
            $hostname = $row["hostname"];
            $domain_name = $row["domain_name"];
            $department = $row["department"];
            $employee_name = $row["employee_name"];
            $start_time = "";
            $end_time = "";
            if(!in_array($department, $arr_department))
               $arr_department[] = $department;
            if(!array_key_exists($department, $arr_notyet))
               $arr_notyet[$department] = array();
            $str_val = $domain_name. " / ". $hostname. "\t". $employee_name. "\t". $start_time. "\t". NOTYET_STATUS;
            $arr_notyet[$department][] = $str_val;
         }
         mysqli_free_result($result);
      }
   }

   if($link){
      mysqli_close($link);
   }

   ////////////////
   // Prepare the content for detail information
   ////////////////
   $str_display_content = "";
   $str_completed = "";
   $str_waiting = "";
   $str_dropped = "";
   $str_notyet = "";

   /////////////////////
   // check if the report html template exists
   /////////////////////
   if(file_exists(DISPLAY_TEMPLATE)){
      if(!@($str_display_content = file_get_contents(DISPLAY_TEMPLATE))){
         sleep(DELAY_SEC);
         echo __LINE__;
         return;
      }
   }
   else{
      $str_display_content = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">".
                            "</head><body>". "\$\$content\$\$". "</body></html>";
   }

   ///////////////
   // Display result
   ///////////////
   if($total_count == 0){
      echo "<td colspan='5' class='empty'>無任何結果，請重新查詢</td>";
   }
   else{
      asort($arr_department);
      foreach($arr_department as $val){
         // prepare the content for window.open()
         $str_start = "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">".
                      "<colgroup><col class=\"cIndex\"><col class=\"cName\"><col class=\"cMember\"><col class=\"time\"><col class=\"time\">".
                      "<tr><th>序號</th><th>電腦名稱</th><th>人員名稱</th><th>開始時間</th><th>完成時間</th></tr>";
         $str_end = "</table>";
         $str_completed = $str_start;
         $str_waiting = $str_start;
         $str_dropped = $str_start;
         $str_notyet = $str_start;
         $completed_count = count($arr_completed[$val]);
         $waiting_count = count($arr_waiting[$val]);
         $dropped_count = count($arr_dropped[$val]);
         $notyet_count = count($arr_notyet[$val]);
         if($completed_count > 0){
            $serial_num = 0;
            $count1 += $completed_count;
            foreach($arr_completed[$val] as $subval){
               $tmp_arr = explode("\t", $subval);
               $serial_num++;
               $str_completed .= "<tr><td>". $serial_num. "</td><td>". $tmp_arr[0]. "</td><td>". $tmp_arr[1]. "</td><td>". $tmp_arr[2]. "</td><td>". $tmp_arr[3]. "</td></tr>";
            }
         }
         if($waiting_count > 0){
            $serial_num = 0;
            $count2 += $waiting_count;
            foreach($arr_waiting[$val] as $subval){
               $tmp_arr = explode("\t", $subval);
               $serial_num++;
               $str_waiting .= "<tr><td>". $serial_num. "</td><td>". $tmp_arr[0]. "</td><td>". $tmp_arr[1]. "</td><td>". $tmp_arr[2]. "</td><td>". $tmp_arr[3]. "</td></tr>";
            }
         }
         if($dropped_count > 0){
            $serial_num = 0;
            $count4 += $dropped_count;
            foreach($arr_dropped[$val] as $subval){
               $tmp_arr = explode("\t", $subval);
               $serial_num++;
               $str_dropped .= "<tr><td>". $serial_num. "</td><td>". $tmp_arr[0]. "</td><td>". $tmp_arr[1]. "</td><td>". $tmp_arr[2]. "</td><td>". $tmp_arr[3]. "</td></tr>";
            }
         }
         if($notyet_count > 0){
            $serial_num = 0;
            $count3 += $notyet_count;
            foreach($arr_notyet[$val] as $subval){
               $tmp_arr = explode("\t", $subval);
               $serial_num++;
               $str_notyet .= "<tr><td>". $serial_num. "</td><td>". $tmp_arr[0]. "</td><td>". $tmp_arr[1]. "</td><td>". $tmp_arr[2]. "</td><td>". $tmp_arr[3]. "</td></tr>";
            }
         }
         $str_completed .= $str_end;
         $str_waiting .= $str_end;
         $str_dropped .= $str_end;
         $str_notyet .= $str_end;
        
         // Note: document.write()寫出去的字串不能有換行，也不能有沒有脫逸的雙引號，否則window.open()會失敗
         // 利用addslashes()加上脫逸，preg_replace()將new line去掉

         $str_completed = str_replace("\$\$content\$\$", $str_completed, $str_display_content);
         $str_completed = str_replace("部門統計結果清單", $val." 統計結果清單 -- 已完成", $str_completed);
         $str_completed = addslashes($str_completed);
         $str_completed = preg_replace("/\r\n|\n/", "", $str_completed);
         
         $str_waiting = str_replace("\$\$content\$\$", $str_waiting, $str_display_content);
         $str_waiting = str_replace("部門統計結果清單", $val." 統計結果清單 -- 清查中", $str_waiting);
         $str_waiting = addslashes($str_waiting);
         $str_waiting = preg_replace("/\r\n|\n/", "", $str_waiting);
         
         $str_dropped = str_replace("\$\$content\$\$", $str_dropped, $str_display_content);
         $str_dropped = str_replace("部門統計結果清單", $val." 統計結果清單 -- 已逾時", $str_dropped);
         $str_dropped = addslashes($str_dropped);
         $str_dropped = preg_replace("/\r\n|\n/", "", $str_dropped);
         
         $str_notyet = str_replace("\$\$content\$\$", $str_notyet, $str_display_content);
         $str_notyet = str_replace("部門統計結果清單", $val." 統計結果清單 -- 未實施", $str_notyet);
         $str_notyet = addslashes($str_notyet);
         $str_notyet = preg_replace("/\r\n|\n/", "", $str_notyet);
         
         echo "<tr>
               <td>$val</td>";

         if($completed_count == 0)
            echo "<td>0</td>";
         else
            echo "<td><a onClick='mywindow=window.open(\"\", \"\"); mywindow.document.write(\"$str_completed\");'>$completed_count</a></td>";
         if($waiting_count == 0)
            echo "<td>0</td>";
         else
            echo "<td><a onClick='mywindow=window.open(\"\", \"\"); mywindow.document.write(\"$str_waiting\");'>$waiting_count</a></td>";
         if($notyet_count == 0)
            echo "<td>0</td>";
         else
            echo "<td><a onClick='mywindow=window.open(\"\", \"\"); mywindow.document.write(\"$str_notyet\");'>$notyet_count</a></td>";
         if($dropped_count == 0)
            echo "<td>0</td></tr>";
         else
            echo "<td><a onClick='mywindow=window.open(\"\", \"\"); mywindow.document.write(\"$str_dropped\");'>$dropped_count</a></td></tr>";

      } //end of foreach
   } //end of if

   ////////////////
   // print table end 
   ////////////////
   echo "</table>";

   ////////////////
   // the following form is used to show the counts of completed, searching before the table
   ////////////////
   echo "<form>";
   echo "<input type=hidden name=count1 value=$count1>";
   echo "<input type=hidden name=count2 value=$count2>";
   echo "<input type=hidden name=count3 value=$count3>";
   echo "<input type=hidden name=count4 value=$count4>";
   echo "</form>";
?>
