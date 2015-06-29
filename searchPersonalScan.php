<?php
///////////////////////////////
//searchPersonalScan.php
//
// According to the submit criteria, find all computer status in the computerList and identityFound
// 2013/04/11 created by Odie
//
// #001 modified by Odie 2013/04/26
//  To support new feature: mutli-level admin
//     1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//        admin => 1
//        user  => 2
//     2. If user, restrict the departments he can see
//
//  #002 2014/09/15 Phantom        如果是 system admin 的話, 不能刪除 XML
//  #003 2014/12/03 Odie           安泰客製，加上 IP 及登入帳號欄位
//  
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
   
   // #001, add checking $_SESSION["loginLevel"] and $_SESSION["loginName"]
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
   $GUID_ADM = $_SESSION["GUID_ADM"];
   $login_level = $_SESSION["loginLevel"];
   $login_name = $_SESSION["loginName"];
   session_write_close();

   //////////////////////////////
   // #002 Check GUID_ADM and set systemAdm flag
   //////////////////////////////
   if ($GUID_ADM == "")
      $systemAdminFlag = 0;
   else if ($GUID_ADM == "00000000_0000_0000_0000_000000000000")
      $systemAdminFlag = 1;
   else
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   
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
   $domain_name;
   $hostname;
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
   if(($domain_name = check_name($_GET["domain_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($hostname = check_name($_GET["hostname"])) == SYMBOL_ERROR)
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
   // print table header
   ///////////////
?>
   <!DOCTYPE HTML>
   <html>
   <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta http-equiv="Pragma" content="no-cache">
   <meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
   <title>Openfind P-Marker 部門統計結果頁</title>
   <link rel="stylesheet" type="text/css" href="lib/yui-cssreset-min.css">
   <link rel="stylesheet" type="text/css" href="lib/yui-cssfonts-min.css">
   <link rel="stylesheet" type="text/css" href="css/OSC_layout.css">
   <link type="text/css" href="lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
   <link rel="stylesheet" type="text/css" href="css/login.css">
   <script type="text/javascript" src="lib/jquery.min.js"></script>
   <script type="text/javascript" src="lib/jquery-ui.min.js"></script>
   <script type="text/javascript" src="js/OSC_layout.js"></script>
   <script type="text/javascript" src="js/css3pie.js"></script>
   <script type="text/javascript" src="js/PMarkFunction.js"></script>
   <script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
   <script type="text/javascript" src="openflashchart/js/json/json2.js"></script>
   <script Language=JavaScript>
   function deleteXML(xmlID,entryID)
   {  
      var ret = window.confirm("確定要刪除此記錄並自資料庫刪除此項掃描結果？ (次數無法加回)");
      if (ret)
      {
         document.getElementsByName("deleteXMLID")[0].value = xmlID;
         document.getElementsByName("deleteEntryID")[0].value = entryID;
         document.getElementsByName("deleteXMLButton")[0].click();
      }
   }
   var user_searchHint = "";
   </script>
   </head>
   <body>
   <div id="loadingWrap" class="nodlgclose loading" style="position: fixed; display:none;">
      <div id="loadingContent">
         <span id="loadingContentInner">
            <span id="loadingIcon"></span><span id="loadingText">讀取中(需要數分鐘)...</span>
         </span>
      </div>
   </div>
   <div id="header">
      <span class="logo"></span>
   </div>
   <div id="banner">
      <span class="bLink first"><span>盤點歷程結果頁</span><span class="bArrow"></span></span>
   </div>
   <div class="listUploadW">
<?php
   echo "<div class=\"title\">$domain_name / $hostname</div>
         <div class=\"content\">
            <div id=\"enterpiceReport\" style=\"display:block;\">
               <div class=\"mainContent\" style=\"border:0px;\">
                  <div class=\"uResultW]\" id=\"userMgmtPages\">
                     <table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                     <colgroup><col class=\"cIndex\"><col class=\"cName\"><col class=\"cMember\"><col class=\"cMember\"><col class=\"cMember\"><col class=\"time\"><col class=\"time\">";
   if ($systemAdminFlag != 1)
      echo "<col class=\"cAction\">";
   else
      echo "</tr>";

   echo "<tr><th>序號</th><th>人員名稱</th><th>部門</th><th>IP</th><th>登入帳號</th><th>開始時間</th><th>完成時間</th>";
   //#002 begin
   if ($systemAdminFlag != 1)
      echo "<th>刪除</th></tr>";
   else
      echo "</tr>";
   //#002 end

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   ////////////////////
   // process input from GET method
   ////////////////////

   if(!get_magic_quotes_gpc())
   {
      $domain_name = mysql_real_escape_string($domain_name);
      $hostname = mysql_real_escape_string($hostname);
      $range_begin = mysql_real_escape_string($range_begin);
      $range_end = mysql_real_escape_string($range_end);
   }

   $time_range = "";

   //create time_range
   if ($range_begin != "" && $range_end != "")
      $time_range = "and (e.last_modified_time between '$range_begin' and '$range_end')";
   else if ($range_begin == "" && $range_end != "")
      $time_range = "and (e.last_modified_time < '$range_end')";
   else if ($range_begin != "" && $range_end == "")
      $time_range = "and (e.last_modified_time > '$range_begin')";
   else if ($range_begin == "" && $range_end == "")
      $time_range = "";

   ////////////////
   // #001
   // if login_level == 2, get "dept_list" from "userLogin
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

   ////////////////
   // #001, a user can only see some records of his departments
   // #003, modify sql query to get ip and login_name
   ////////////////

   if($login_level == 1){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.ip as i_IP, i.login_name as i_loginName, i.XMLID, i.nFile, i.department, i.employee_name, 
         e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
            from (select * from entry where GUID = '$GUID' and domain_name = '$domain_name' and hostname = '$hostname') e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
               on e.entryID = i.entryID where 1 $time_range
            order by e.create_time DESC
         ";
   }
   else if($login_level == 2){
      $sql = "
         select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.ip as i_IP, i.login_name as i_loginName, i.XMLID, i.nFile, i.department, i.employee_name, 
         e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
            from (select * from entry where GUID = '$GUID' and domain_name = '$domain_name' and hostname = '$hostname') e 
               left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID)) i
               on e.entryID = i.entryID where i.department in ($dept_list) $time_range
            order by e.create_time DESC
         ";
   }
      
   $total_count = 0;
   $completed_count = 0;
   $dropped_count = 0;
   $waiting_count = 0;
   
   if($result = mysqli_query($link, $sql)){
      while($row = mysqli_fetch_assoc($result)){
         $e_entryID = $row["e_entryID"];
         $i_entryID = $row["i_entryID"];
         $i_IP = $row["i_IP"];
         $i_login_name = $row["i_loginName"];
         $xmlID = $row["XMLID"];
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

         $total_count++;
         echo "<tr><td>$total_count</td><td>$employee_name</td><td>$department</td><td>$i_IP</td><td>$i_login_name</td><td>$start_time</td>";

         if($e_status === COMPLETED || $e_status === DELETED){
            $completed_count++;
            echo "<td>$end_time</td>";
            //#002 begin
            if($systemAdminFlag != 1){
               if($i_status === "0")   // identityFound 存在這筆資料，且不是已刪除狀態
                  echo "<td><a class=\"del\" OnClick=deleteXML($xmlID,$i_entryID)>刪除</a></td>";
               else
                  echo "<td>已刪除</td></tr>";
            }
            //#002 end
         }
         else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE){
            $waiting_count++;
            echo "<td>". WAITING_STATUS. "</td>";
            if($systemAdminFlag != 1){
               echo "<td></td>";
            }
            echo "</tr>";
         }
         else if($e_status === DROPPED || $e_status === PARSE_FAIL){
            $dropped_count++;
            echo "<td>". DROPPED_STATUS. "</td>";
            if($systemAdminFlag != 1){
               echo "<td></td>";
            }
            echo "</tr>";
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

   ////////////////
   // print table end 
   ///////////////
?>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="declaration">© 2013 Openfind Information Technology, Inc. All rights reserved.<br>版權所有 網擎資訊軟體股份有限公司</div>
<form name=deleteXMLform>
   <input type="hidden" name="deleteXMLID" value="">
   <input type="hidden" name="deleteEntryID" value="">
   <input type="button" style="display:none" name="deleteXMLButton" class="btn_submit_new deleteXMLClass">
</form>
</body>
</html>
