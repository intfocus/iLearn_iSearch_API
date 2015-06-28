<?php
///////////////////////////////
//searchUserMgmt.php
//
// According to the submit criteria, find all computer status in the computerList and identityFound
// 2012/06/13 created by Phantom and Billy
//
// #001 modified by Odie 2013/04/08
//    1. Change the column "動作" to "紀錄", which provides the scan history for the computer, 
//       and move the column "刪除" into the scan history
//
// #002 modified by Odie 2013/04/26
//  To support new feature: mutli-level admin
//     1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//        admin => 1
//        user  => 2
//     2. If user, restrict the departments he can see
//
// #003 modified by Odie 2014/07/31
//     Add download button and generate CSV file for download
// 
// #004 modified by Phantom 2014/09/16
//     增加 IP and Login_Name 兩個欄位
//
// #005 modified by Odie 2015/02/12
//     1. Use KLogger class for logging
//     2. Modify SQL for keyword condition search (should match entry table instead of identityFound table)
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

   // #005
   include_once("/usr/local/www/apache22/KLogger.php");
   $log = new KLogger("/usr/local/www/apache22/logs/searchUserMgmt.log", KLogger::INFO);

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

   define("CSV_PATH", "/usr/local/www/apache22/data/search_work/P-Marker_Report_User_Status.csv"); //#003

   
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
   
   //data
   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($userMgmt_type = check_name($_GET["userMgmt_type"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($userMgmt_targetCom = check_name($_GET["userMgmt_targetCom"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($userMgmt_keyword = check_name($_GET["userMgmt_keyword"])) == SYMBOL_ERROR)
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

   $log->LogInfo(json_encode($_GET));

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
<?php
   }
?>
      <span id="countStr"></span>
      <span class="btn new" style="float: right;" OnClick="downloadStatus();">匯出</span>
   </div>
   <table class="report" border="0" cellspacing="0" cellpadding="0">
      <colgroup>
         <col class="cIndex" />
         <col class="cName" />
         <col class="cMember" />
         <col class="cDpmt" />
         <col class="cIP" /> <!-- #004 -->
         <col class="cLoginName" /> <!-- #004 -->
         <col class="cStatus" />
         <col class="uLvl" />
         <col class="time" />
         <col class="time" />
         <col class="cAction" />
      </colgroup>
      <tr>
         <th>序號</th>
         <th>電腦名稱</th>
         <th>人員名稱</th>
         <th>部門</th>
         <th>IP</th> <!-- #004 -->
         <th>登入帳號</th> <!-- #004 -->
         <th>狀態</th>
         <th>含個資檔案數</th>
         <th>開始時間</th>
         <th>完成時間</th>
         <th>紀錄</th>
      </tr>
<?php
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
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

      // #005 start, $keyword_str_2 is for not yet type
      $userMgmt_keyword_str_1 = " and 
          (e.hostname like '%$userMgmt_keyword%' or
           e.domain_name like '%$userMgmt_keyword%' or
           e.employee_name like '%$userMgmt_keyword%' or
           e.department like '%$userMgmt_keyword%')
           ";

      $userMgmt_keyword_str_2 = " and 
          (i.hostname like '%$userMgmt_keyword%' or
           i.domain_name like '%$userMgmt_keyword%' or
           i.employee_name like '%$userMgmt_keyword%' or
           i.department like '%$userMgmt_keyword%')
           ";
      // #005 end
   }

   ////////////////
   // for inside the ComputerList and outside the ComputerList 
   ////////////////
   $userMgmt_targetCom_str = "";

   if ($userMgmt_targetCom == "inside")
   {
      $userMgmt_targetCom_str = " and
         exists (select * from computerList 
                where GUID=e.GUID and hostname=e.hostname and domain_name=e.domain_name)
      ";
   }
   else if ($userMgmt_targetCom == "outside")
   {
      $userMgmt_targetCom_str = " and
         not exists (select * from computerList 
                    where GUID=e.GUID and hostname=e.hostname and domain_name=e.domain_name)
      ";
   }

   ////////////////
   // for time range
   ////////////////
   $time_range = "";

   if ($range_begin != "" && $range_end != "")
      $time_range = "and (last_modified_time between '$range_begin' and '$range_end')";
   else if ($range_begin == "" && $range_end != "")
      $time_range = "and (last_modified_time < '$range_end')";
   else if ($range_begin != "" && $range_end == "")
      $time_range = "and (last_modified_time > '$range_begin')";
   else if ($range_begin == "" && $range_end == "")
      $time_range = "";

   ////////////////
   // #002
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
         // ex. $dept_list = "'marketing', 'advertising'";
         // user reg expression to parse it into array
         // *? => non-greedy match
         preg_match_all("/'(.*?)'/", $dept_list, $arr_dept_list, PREG_PATTERN_ORDER);
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

   ////////////////
   // sql command to query DB, find every record in "entry"
   // #002, a user can only see some records of his departments
   ////////////////

   $sql = "
      select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.ip as i_IP, i.login_name as i_loginName, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
      e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
         from (select * from entry where GUID = '$GUID' $time_range) e 
            left join (select * from identityFound i1 where XMLID = (select max(XMLID) from identityFound i2 where i1.entryID = i2.entryID)) i
            on e.entryID = i.entryID where 1 $userMgmt_keyword_str_1 $userMgmt_targetCom_str
         order by e.create_time DESC
      ";
   
   $log->LogDebug($sql);

   // a 2-D array to store the scan history of each pc
   $arr_total = array();
   
   // the follwoing 3 arrays are used to store information for display
   $arr_completed = array();
   $arr_dropped = array();
   $arr_waiting = array();

   $completed_count = 0;
   $dropped_count = 0;
   $waiting_count = 0;
   $notyet_count = 0;
   
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
         $i_ip = $row["i_IP"]; //#004
         $i_loginName = $row["i_loginName"]; //#004

            
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

         ////////////////////////////
         // Check if the PC occurs for the first time
         // if yes
         // => 1. create an item in arr_total
         // => 2. Check if the department of the PC can be shown
         //       if yes
         //       => 2.1. store it in arr_completed, arr_waiting or arr_dropped according to e_status for further display
         //       if no
         //       => 2.2. continue
         // /////////////////////////

         if(array_key_exists($pc_name, $arr_total) == FALSE){
            // put information to arr_total
            $arr_total[$pc_name] = array();
            $arr_total[$pc_name]["completed_count"] = 0;
            $arr_total[$pc_name]["waiting_count"] = 0;
            $arr_total[$pc_name]["dropped_count"] = 0;
            if($login_level == 2){
               if(!in_array($department, $arr_dept_list[1]))
                  continue;
            }
            if($e_status === COMPLETED || $e_status === DELETED){
               $completed_count++;
               $arr_completed[$completed_count] = array();
               $arr_completed[$completed_count]["domain_name"] = $domain_name;
               $arr_completed[$completed_count]["hostname"] = $hostname;
               $arr_completed[$completed_count]["pc_name"] = $pc_name;
               $arr_completed[$completed_count]["employee_name"] = $employee_name;
               $arr_completed[$completed_count]["department"] = $department;
               $arr_completed[$completed_count]["status"] = COMPLETED_STATUS;
               $arr_completed[$completed_count]["nFile"] = $nFile;
               $arr_completed[$completed_count]["start_time"] = $start_time;
               $arr_completed[$completed_count]["end_time"] = $end_time;
               $arr_completed[$completed_count]["xmlID"] = $xmlID;
               $arr_completed[$completed_count]["entryID"] = $i_entryID;
               $arr_completed[$completed_count]["IP"] = $i_ip; //#004
               $arr_completed[$completed_count]["loginName"] = $i_loginName; //#004
            }
            else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE){
               $waiting_count++;
               $arr_waiting[$waiting_count] = array();
               $arr_waiting[$waiting_count]["domain_name"] = $domain_name;
               $arr_waiting[$waiting_count]["hostname"] = $hostname;
               $arr_waiting[$waiting_count]["pc_name"] = $pc_name;
               $arr_waiting[$waiting_count]["employee_name"] = $employee_name;
               $arr_waiting[$waiting_count]["department"] = $department;
               $arr_waiting[$waiting_count]["status"] = WAITING_STATUS;
               $arr_waiting[$waiting_count]["nFile"] = $nFile;
               $arr_waiting[$waiting_count]["start_time"] = $start_time;
               $arr_waiting[$waiting_count]["IP"] = $i_IP; //#004
               $arr_waiting[$waiting_count]["loginName"] = $i_loginName; //#004
            }
            else if($e_status === DROPPED || $e_status === PARSE_FAIL){
               $dropped_count++;
               $arr_dropped[$dropped_count] = array();
               $arr_dropped[$dropped_count]["domain_name"] = $domain_name;
               $arr_dropped[$dropped_count]["hostname"] = $hostname;
               $arr_dropped[$dropped_count]["pc_name"] = $pc_name;
               $arr_dropped[$dropped_count]["employee_name"] = $employee_name;
               $arr_dropped[$dropped_count]["department"] = $department;
               $arr_dropped[$dropped_count]["status"] = DROPPED_STATUS;
               $arr_dropped[$dropped_count]["nFile"] = $nFile;
               $arr_dropped[$dropped_count]["start_time"] = $start_time;
               $arr_dropped[$dropped_count]["IP"] = $i_IP; //#004
               $arr_dropped[$dropped_count]["loginName"] = $i_loginName; //#004
            }
         }

         //////////////////////
         // Store the correpsonding count in arr_total according to e_status
         //////////////////////

         if($e_status === COMPLETED || $e_status === DELETED)
            $arr_total[$pc_name]["completed_count"] += 1;
         else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE)
            $arr_total[$pc_name]["waiting_count"] += 1;
         else if($e_status === DROPPED || $e_status === PARSE_FAIL)
            $arr_total[$pc_name]["dropped_count"] += 1;
      
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
   
   $range_begin_ori = $_GET["range_begin"];
   $range_end_ori = $_GET["range_end"];

   ////////////////////
   // Show result
   // 1. completed
   // 2. searching
   // 3. dropped
   ////////////////////
   
   $download_str = "";  // #003

   $total_count = 0;
   if(strpos($userMgmt_type,"completed",0) !== false){   // check if the user tick the "completed" box for display
      if(count($arr_completed) > 0){                     // check if there is any completed data
         foreach($arr_completed as $pc_count => $arr){
            if($total_count >= SEARCH_SIZE)
               break;
            $total_count++;
            $pc_name = $arr["pc_name"];
            $domain_name = $arr["domain_name"];
            $hostname = $arr["hostname"];
            $employee_name = $arr["employee_name"];
            $department = $arr["department"];
            $status = $arr["status"];
            $nFile = $arr["nFile"];
            $start_time = $arr["start_time"];
            $end_time = $arr["end_time"];
            $xmlID = $arr["xmlID"];
            $entryID = $arr["entryID"];
            $ip = $arr["IP"]; //#004
            $loginName = $arr["loginName"]; //#004
            $ok_count = $arr_total[$pc_name]["completed_count"] + $arr_total[$pc_name]["waiting_count"];

            echo "<tr>
                  <td><span class='cIndex'>$total_count</span></td>
                  <td><span class='cName'>$pc_name</span></td>
                  <td><span class='cMember'>$employee_name</span></td>
                  <td><span class='cDpmt'>$department</span></td>
                  <td><span class='cIP'>$ip</span></td>
                  <td><span class='cLoginName'>$loginName</span></td>
                  <td><span class='cStatus'>$status</span></td>
                  <td><span class='uLvl'>$nFile</span></td>
                  <td><span class='time'>$start_time</span></td>
                  <td><span class='time'>$end_time</span></td>
                  <td><span class='cAction'><a class='del' OnClick='open_scan_result(\"$domain_name\",\"$hostname\",\"$range_begin_ori\",\"$range_end_ori\")'>紀錄($ok_count)</a></span></td>
                  </tr>";
            $start_time_nobr = str_replace("<br>", " ", $start_time);
            $end_time_nobr = str_replace("<br>", " ", $end_time);
            // #004
            $download_str = $download_str . $total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t" . $nFile . "\t" . $start_time_nobr . "\t" . $end_time_nobr . "\r\n";
            // #005
            $log->LogDebug($total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t" . $nFile . "\t" . $start_time_nobr . "\t" . $end_time_nobr);
         } //end of foreach
      }      
   }
   else
      $completed_count = 0;

   if(strpos($userMgmt_type,"searching",0) !== false){   // check if the user tick the "searching" box for display
      if(count($arr_waiting) > 0){                       // check if there is any searching data
         foreach($arr_waiting as $pc_count => $arr){
            if($total_count >= SEARCH_SIZE)
               break;
            $total_count++;
            $pc_name = $arr["pc_name"];
            $domain_name = $arr["domain_name"];
            $hostname = $arr["hostname"];
            $employee_name = $arr["employee_name"];
            $department = $arr["department"];
            $status = $arr["status"];
            $nFile = $arr["nFile"];
            $start_time = $arr["start_time"];
            $end_time = $arr["end_time"];
            $xmlID = $arr["xmlID"];
            $entryID = $arr["entryID"];
            $ip = $arr["IP"]; //#004
            $loginName = $arr["loginName"]; //#004
            $ok_count = $arr_total[$pc_name]["completed_count"] + $arr_total[$pc_name]["waiting_count"];
            echo "<tr>
                  <td><span class='cIndex'>$total_count</span></td>
                  <td><span class='cName'>$pc_name</span></td>
                  <td><span class='cMember'>$employee_name</span></td>
                  <td><span class='cDpmt'>$department</span></td>
                  <td><span class='cIP'>$ip</span></td>
                  <td><span class='cLoginName'>$loginName</span></td>
                  <td><span class='cStatus'>$status</span></td>
                  <td><span class='uLvl'></span></td>
                  <td><span class='time'>$start_time</span></td>
                  <td><span class='time'></span></td>
                  <td><span class='cAction'><a class='del' OnClick='open_scan_result(\"$domain_name\",\"$hostname\",\"$range_begin_ori\",\"$range_end_ori\")'>紀錄($ok_count)</a></span></td>
                  </tr>";
            
            $start_time_nobr = str_replace("<br>", " ", $start_time);
            // #004
            $download_str = $download_str . $total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t\t" . $start_time_nobr . "\t\r\n";
            // #005
            $log->LogDebug($total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t\t" . $start_time_nobr . "\t");
         
         } //end of foreach
      }
   }
   else
      $waiting_count = 0;

   if(strpos($userMgmt_type,"expired",0) !== false){     // check if the user tick the "expired" box for display
      if(count($arr_dropped) > 0){                       // check if there is any expired (dropped) data
         foreach($arr_dropped as $pc_count => $arr){
            if($total_count >= SEARCH_SIZE)
               break;
            $total_count++;
            $pc_name = $arr["pc_name"];
            $domain_name = $arr["domain_name"];
            $hostname = $arr["hostname"];
            $employee_name = $arr["employee_name"];
            $department = $arr["department"];
            $status = $arr["status"];
            $nFile = $arr["nFile"];
            $start_time = $arr["start_time"];
            $end_time = $arr["end_time"];
            $xmlID = $arr["xmlID"];
            $entryID = $arr["entryID"];
            $ip = $arr["IP"]; //#004
            $loginName = $arr["loginName"]; //#004
            $ok_count = $arr_total[$pc_name]["completed_count"] + $arr_total[$pc_name]["waiting_count"];
            echo "<tr>
                  <td><span class='cIndex'>$total_count</span></td>
                  <td><span class='cName'>$pc_name</span></td>
                  <td><span class='cMember'>$employee_name</span></td>
                  <td><span class='cDpmt'>$department</span></td>
                  <td><span class='cIP'>$ip</span></td>
                  <td><span class='cLoginName'>$loginName</span></td>
                  <td><span class='cStatus'>$status</span></td>
                  <td><span class='uLvl'></span></td>
                  <td><span class='time'>$start_time</span></td>
                  <td><span class='time'></span></td>
                  <td><span class='cAction'><a class='del' OnClick='open_scan_result(\"$domain_name\",\"$hostname\",\"$range_begin_ori\",\"$range_end_ori\")'>紀錄($ok_count)</a></span></td>
                  </tr>";

            $start_time_nobr = str_replace("<br>", " ", $start_time);
            // #004
            $download_str = $download_str . $total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t\t" . $start_time_nobr . "\t\r\n";
            // #005
            $log->LogDebug($total_count . "\t" . "$pc_name" . "\t" . $employee_name . "\t" . $department . "\t" . $ip . "\t" . $loginName . "\t" . $status . "\t\t" . $start_time_nobr . "\t");

         } //end of foreach
      }
   }
   else
      $dropped_count = 0;

   ////////////////
   // notyet
   // #001, a user can only see some records of his departments
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
                     $time_range
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
                     $time_range
               )
            and department in ($dept_list) $userMgmt_keyword_str_2
      ";
   }

   if(strpos($userMgmt_type,"notyet",0) !== false){
      if(($result = mysqli_query($link, $sql)) && ($userMgmt_targetCom != "outside")){
         $notyet_count = mysqli_num_rows($result);
         while($row = mysqli_fetch_assoc($result)){
            if($total_count >= SEARCH_SIZE)
               break;
            $total_count++;
            $hostname = $row["hostname"];
            $domain_name = $row["domain_name"];
            $department = $row["department"];
            $employee_name = $row["employee_name"];
            echo "<tr>
               <td><span class='cIndex'>$total_count</span></td>
               <td><span class='cName'>$domain_name / $hostname</span></td>
               <td><span class='cMember'>$employee_name</span></td>
               <td><span class='cDpmt'>$department</span></td>
               <td><span class='cIP'></span></td>
               <td><span class='cLoginName'></span></td>
               <td><span class='cStatus'>". NOTYET_STATUS. "</span></td>
               <td><span class='uLvl'></span></td>
               <td><span class='time'></span></td>
               <td><span class='time'></span></td>
               <td><span class='cAction'></span></td>
               </tr>";

            $start_time_nobr = str_replace("<br>", " ", $start_time);
            // #004
            $download_str = $download_str . $total_count . "\t" . "$domain_name / $hostname" . "\t" . $employee_name . "\t" . $department . "\t\t\t" . NOTYET_STATUS . "\t\t\t\r\n";
            // #005
            $log->LogDebug($total_count . "\t" . "$domain_name / $hostname" . "\t" . $employee_name . "\t" . $department . "\t\t\t" . NOTYET_STATUS . "\t\t\t");
         }
         mysqli_free_result($result);
      }
   }

   if($link){
      mysqli_close($link);
   }

   ////////////////
   // For empty
   ///////////////
   
   if($total_count == 0)
      echo "<td colspan='11' class='empty'>無任何結果，請重新查詢</td>";

   ////////////////
   // print table end 
   ///////////////
   
   $download_str = urlencode($download_str);

   echo "</table>";
   echo "<form style=\"display: none;\" id=\"downloadStatus\" method=\"post\" action=\"downloadStatus.php\" target=\"blank\">";
   echo "<input type=\"text\" name=\"count1\" value=\"$completed_count\">";
   echo "<input type=\"text\" name=\"count2\" value=\"$waiting_count\">";
   echo "<input type=\"text\" name=\"count3\" value=\"$notyet_count\">";
   echo "<input type=\"text\" name=\"count4\" value=\"$dropped_count\">";
   echo "<input type=\"text\" name=\"status\" value=\"$download_str\">";
   echo "<input type=\"submit\">";
   echo "</form>";
?>
