<?php
/*************************************************
 * genReportChart.php
 * 1. Check receive information from client
 * 2. SQL Command 
 *    2.1 get information from sql 
 *    2.2 folder_name = pid_timestamp 
 * 3. Create json format 
 * 4. Gen report chart
 *    4.1 include drawChart.php
 *    4.2 receive draw chart string from drawChart.php 
 * 5. Return string to ajax => json + drawChart string
 *    2012/02/15 Jeffrey Chan
*************************************************/

//////////////////////////////////////////////////////////////
// #001 Modified by Odie 2013/03/04
//    1. New features:
//       1.1 DIFF: Retrieve one more latest scan record and compare them 
//       1.2 Merge the most renctly action with this time if the user hasn't upload action file
//       1.3 Check if the XML files returned by DB exist
//    2. Modified
//       2.1 Write two additional files: iFoundLast(1.1), iAction(1.2)
//       2.2 As for each row of the original SQL query result, query the DB again and return an array for each computer(1.3)
// 
// #002 Modified by Odie 2013/03/25
//    1. Fix the problem that a scan without any privacy data will be skipped when generating report
// 
// #003 Modified by Odie 2013/04/30
//    1. Some data in DB maybe converted when inserting, convert them back when generating report
//       1.1 Add a function for converting back
//       1.2 Imply the function on department, employee_name and employee_email
// 
// #004 Modified by Odie 2013/07/22
//    1. For the new feature to send individual report, add employee_email to iFound file
// 
// #005 Modified by Odie 2013/09/11
//    1. Add the 8th type data, and change the SQL command (need to query "count7")
//    2. Query if the 8th type is enable and its name, and write them to iFound file
//
// #006 Modified by Odie 2013/09/17
//    1. Write "scan_express_timeout" and "scan_express_count" to iFound file
//
// #007 Modified by Odie 2013/10/17
//    1. Customize for Gobo, generete report according to login name (domain_name + hostname + login_name)
//
//////////////////////////////////////////////////////////////

?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   //----- Read connect information from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include_once(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   define(DRAW_PATH, "$working_path/drawChart.php");
   define(ILLEGAL_CHAR, "'-;<>");                              //illegal char
   define(TIME_ZONE, "Asia/Taipei");
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(REPORT_NAME_LENGTH, 255);
   define(STR_LENGTH, 50);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(REPORT_FILE_NAME, $report_file_name);
   define(DEBUG, 0);
   //xml status
   define(XML_COMPLETED, 0);  //xml is completed
   //return value
   define(SYMBOL_ERROR, -1);

   ////////////////
   // #003 add the following function
   ////////////////
   function decode_escape_string($str){
      $str = str_replace("&lt;","<",$str);
      $str = str_replace("&gt;",">",$str);
      $str = str_replace("&#39;","'",$str);
      return $str;
   }

   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "new_report"))
      {

         return SYMBOL_ERROR;
      }

      return $check_str;
   }
   //----- Check report name -----
   function check_report_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > REPORT_NAME_LENGTH)
      {

         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {

         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt", $check_str);
      }
      
      return $check_str;
   }
   //----- Check string -----
   function check($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {
         
         return SYMBOL_ERROR;
      }
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {

         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {

         return SYMBOL_ERROR;
      }

      return $check_str;
   }
   //----- Check report range begin -----
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

         return SYMBOL_ERROR;
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

         return SYMBOL_ERROR;
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
?>
<?php
   //----- Variable definition -----
   $cmd;                   //get command from client
   $guid;                  //get guid from client
   $report_name;           //get report name from client
   $identity_type;         //get identity type from client
   $range_begin;           //get report range from client
   $range_end;             //get report range from client
   $riskCategorySelect;    //get riskCategorySelect(2 or 3) from client
   //$str_find_new;          //find newest data
   $str_extreme_type;      //query string of extreme type
   $str_identity_type;     //query string of identity type 
   $str_sum_identity_type;
   $str_sum_all_identity_type;
   $risk_low;              //low from risk category table
   $risk_high;             //high from risk category table
   $risk_extreme;          //extreme from risk category table
   $risk_extreme_type_num; //extreme type number from risk category table
   $risk_extreme_type;     //extreme type from risk category table
   $arr_extreme_type;      //array, put extreme type
   $arr_identity_type;
   $arr_sum_risk_count;    //array, sum of all risk_type count
   $arr_iFound_risk_count; //array, risk_type count of the latest scan (iFound)
   $now_time;              //current time
   $name_pid;              //process id
   $name_timestamp;        //now timestamp            
   $folder_name;           //folder name, pid + timestamp
   $file_path;
   $str_temp;
   $nExtremeFile;          //number of extreme files
   $nExtremeData;          //number of all extreme file's data
   $nHighFile;             //number of high files
   $nHighData;             //number of all high file's data
   $nMediumFile;           //number of medium files
   $nMediumData;           //number of all medium file's data
   $nLowFile;              //number of low files
   $nLowData;              //number of all low file's data
   
   // #006 add
   $scan_express_timeout;
   $scan_express_count;

   $arr_nDepExtremeFile;   //array, number of department's extreme risk files    
   $arr_nDepHighFile;
   $arr_nDepMediumFile;
   $arr_nDepLowFile;
   $arr_nCompExtremeFile;
   $arr_nCompHighFile;
   //$computer_numbers;      //number of all scan computer numbers
   //$str_extreme;
   $str_query;             //sql command string, query
   $str_update;            //sql command string, update
   $str_insert;            //sql command string, insert
   $link;                  //connect to mysql
   $result;                //result object, receive query result from mysql
   $row;                   //array, put result into an array
   //return string
   $arr_json;
   $str_return;

   $type8_enable = 0;      // #005
   $type8_name = "生日";   // #005

   /*
   $fp = fopen("$working_path/debuglog.txt", "w");
   if ($fp != null)
   {
      fwrite($fp, $_GET["report_name"].  "\n");
   }
    */
   
   //////////////////////////////////////////////////////////////////////////
   //----------------------------------------------------
   //----- 1. Check receive information from client -----
   //----------------------------------------------------

   //----- session check -----
   if(!DEBUG)
   {
      session_start();
      if (!session_is_registered("GUID") || !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
      {
         sleep(DELAY_SEC);
         echo -__LINE__;

         return;
      }
      if ($_SESSION["GUID"] == "" || $_SESSION["loginLevel"] == "" || $_SESSION["loginName"] == "")
      {
         sleep(DELAY_SEC);
         echo -__LINE__;

         return;
      }
      $guid = $_SESSION["GUID"];
      $login_level = $_SESSION["loginLevel"];
      $login_name = $_SESSION["loginName"];
      
      session_write_close();
   }
   else
   {
      $guid = "8f44a8ab_5c6c_6232_cd4f_642761007428";
   }
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($report_name = check_report_name($_GET["report_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($identity_type = check($_GET["identity_type"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($range_begin = check_range_begin($_GET["range_begin"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($range_end = check_range_end($_GET["range_end"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $riskCategorySelect = $_GET["riskCategorySelect"];
   if ($riskCategorySelect !== "2" && $riskCategorySelect !== "3")
      $riskCategorySelect = "2";
   $departName = $_GET["departName"];
   $reportHostname = $_GET["reportHostname"];

   date_default_timezone_set(TIME_ZONE);
   if(DEBUG)
   {   
      $str_test =  "cmd= $cmd<br>GUID= $guid<br>report_name= $report_name<br>identity_type= $identity_type<br>range_begin= $range_begin<br>range_end= $range_end<br>riskCategory= $riskCategory<br>";
      echo "$str_test<br>";
   }
   //////////////////////////////////////////////////////////////////////////
   //--------------------------
   //----- 2. SQL Command -----
   //--------------------------
   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- query risk category by guid -----
   $str_query = "
      select *
      from riskCategory
      where GUID = '$guid'";
   if($result = mysqli_query($link, $str_query))   //query riskCategory success
   {
      //----- riskCategory have this GUID -----
      if($row = mysqli_fetch_assoc($result))
      {
         $risk_low = $row["low"];
         $risk_high = $row["high"];
         $risk_extreme = $row["extreme"];
         $risk_extreme_type_num = $row["extreme_type_num"];
         $risk_extreme_type = $row["extreme_type"];
         $type8_enable = $row["type8_enable"];           // #005
         $type8_name = $row["type8_name"];               // #005
         mysqli_free_result($result);    //free useless result
         
         if(DEBUG)   
         {  
            echo "<h1>risk category:</h1><br>"; 
            print_r($row); 
            echo "<br><br>";
         }
         unset($row);    //clean array
      }
      //----- riskCategory doesn't have this GUID -----
      else
      {
         mysqli_free_result($result);    //free useless result
         $str_query = "
            select *
            from riskCategory
            where GUID = '" . DEFAULT_GUID . "'";
         if($result = mysqli_query($link, $str_query))   //query riskCategory by default success
         {
            $row = mysqli_fetch_assoc($result);
            $risk_low = $row["low"];
            $risk_high = $row["high"];
            $risk_extreme = $row["extreme"];
            $risk_extreme_type_num = $row["extreme_type_num"];
            $risk_extreme_type = $row["extreme_type"];
            mysqli_free_result($result);    //free useless result
            if(DEBUG)   
            {   
               echo "<h1>risk category:</h1><br>"; 
               print_r($row); 
               echo "<br><br>";
            }   
            unset($row);    //clean array
         }
         else   //query riskCategory by default failure
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo -__LINE__;

            return;
         }
      }
   }
   else   //query riskCategory failure
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      } 
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- split extreme type and put into an array -----
   $arr_extreme_type = explode(",", $risk_extreme_type);
   $str_extreme_type = "";
   
   foreach($arr_extreme_type as $element)
   {
      $str_extreme_type = $str_extreme_type . "( identityFile.count$element >= $risk_extreme ) + ";
   }
   $str_extreme_type = substr_replace($str_extreme_type, "", -2, 1);
   if(DEBUG)
   {
      echo "<h1>extreme type:</h1>";
      print_r($arr_extreme_type);
      echo "<br>$str_extreme_type<br>";
   }
   //----- split identity type and put into an array -----
   $arr_identity_type = explode(",", $identity_type);

   foreach($arr_identity_type as $element)
   {
      $str_identity_type = $str_identity_type . " identityFile.count$element + ";
      $str_sum_identity_type = $str_sum_identity_type . "sum(count$element) as count$element, ";
      $str_sum_all_identity_type = $str_sum_all_identity_type . "sum(identityFile.count$element) + ";
   }
   $str_identity_type = substr_replace($str_identity_type, "", -2, 1);
   $str_sum_identity_type = substr_replace($str_sum_identity_type, "", -2, 1);
   $str_sum_all_identity_type = substr_replace($str_sum_all_identity_type, "", -2, 1);
   if(DEBUG)
   {
      echo "<h1>identity type:</h1>";
      print_r($arr_identity_type);
      echo "<br>$str_identity_type<br>";
      echo "$str_sum_identity_type<br>";
      echo "$str_sum_all_identity_type<br>";
   }
   
   ////////////////
   // #003
   // if login_level == 2, get "dept_list" from "userLogin"
   ////////////////
   
   $dept_list = "";
   if($login_level == 2){
      $sql = "select dept_list from userLogin where GUID = '$guid' and login_name = '$login_name'";
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
   
   ///////////////////////////
   // yaoan 20120508 add
   /////////////////////////
   
   //----- get information -----
   $name_pid = getmypid();
   $now_time = date("Y-m-d H:i:s");
   $name_timestamp = date("YmdHis", strtotime($now_time));    
   $folder_name = $name_pid . "_" . $name_timestamp;
   $file_path = "$report_path/$guid/$folder_name";

   if(mkdir("$file_path",0755,true) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   $iFound_file = "$file_path/iFound";
   if(($f = @fopen($iFound_file,"w")) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
   fwrite($f,"$guid\n");
   
   // #005 modified, write the information about type8_enable and type8_name
   fwrite($f,"$risk_low\t$risk_high\t$risk_extreme\t$risk_extreme_type_num\t$risk_extreme_type\t$riskCategorySelect\t$type8_enable\t$type8_name\n");
   
   $type = implode("\t",$arr_identity_type);
   fwrite($f,"$type\n");

   ////////////////////////////
   // #001 begin
   //  Write the header for iFoundLast and iAction
   ////////////////////////////

   $iFoundLast_file = "$file_path/iFoundLast";
   if(($f2 = @fopen($iFoundLast_file,"w")) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
   fwrite($f2,"$guid\n");
   fwrite($f2,"$risk_low\t$risk_high\t$risk_extreme\t$risk_extreme_type_num\t$risk_extreme_type\t$riskCategorySelect\n");
   $type = implode("\t",$arr_identity_type);
   fwrite($f2,"$type\n");
   
   $iAction_file = "$file_path/iAction";
   if(($f3 = @fopen($iAction_file,"w")) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
   fwrite($f3,"$guid\n");
   fwrite($f3,"$risk_low\t$risk_high\t$risk_extreme\t$risk_extreme_type_num\t$risk_extreme_type\t$riskCategorySelect\n");
   $type = implode("\t",$arr_identity_type);
   fwrite($f3,"$type\n");
   
   ////////////////////////////
   // #001 end
   ////////////////////////////

   if($departName !== ""){
      $departName = mysql_real_escape_string($departName);
      $departName = "department = '$departName' and ";
   }

   if($reportHostname !== ""){
      $reportHostname = mysql_real_escape_string($reportHostname);
      $reportHostname = "hostname = '$reportHostname' and ";
   }
   
   ////////////////
   // #002
   // if login_level == 2, a user can only see some records of his departments
   ////////////////

   if($login_level == 1){
      $sql = "
         select XMLID,create_time,hostname,domain_name,login_name
         from identityFound as iFound
         where iFound.GUID = '$guid' and status = 0 and $departName $reportHostname
         iFound.start_time = (
            select max(tmp.start_time)
            from identityFound as tmp
            where tmp.GUID = iFound.GUID and
            tmp.create_time between '$range_begin' and '$range_end' and
            tmp.hostname = iFound.hostname and
            tmp.domain_name = iFound.domain_name and
            tmp.login_name = iFound.login_name and
            tmp.status = iFound.status
         ) order by iFound.create_time desc
         ";
   }
   else if($login_level == 2){
      $sql = "
         select XMLID,create_time,hostname,domain_name,login_name
         from identityFound as iFound
         where iFound.GUID = '$guid' and status = 0 and $departName $reportHostname
         iFound.start_time = (
            select max(tmp.start_time)
            from identityFound as tmp
            where tmp.GUID = iFound.GUID and
            tmp.create_time between '$range_begin' and '$range_end' and
            tmp.hostname = iFound.hostname and
            tmp.domain_name = iFound.domain_name and
            tmp.login_name = iFound.login_name and
            tmp.status = iFound.status
         ) and iFound.department in ($dept_list) order by iFound.create_time desc
         ";
   }
   /*
      $sql = "
         select XMLID,create_time,hostname,domain_name
         from identityFound as iFound
         where iFound.GUID = '$guid' and status = 0 and $departName $reportHostname
         iFound.start_time = (
            select max(tmp.start_time)
            from identityFound as tmp
            where tmp.GUID = iFound.GUID and
            tmp.create_time between '$range_begin' and '$range_end' and
            tmp.hostname = iFound.hostname and
            tmp.domain_name = iFound.domain_name and
            tmp.login_name = iFound.login_name and
            tmp.status = iFound.status
         ) order by iFound.create_time desc
         ";
    */
   /*
   $sql = "
      select i.XMLID, e.create_time, e.hostname, e.domain_name
            from (select * from entry e1 where e1.GUID = '$guid' and e1.entryID = 
            (select max(entryID) from entry e2 where e1.domain_name = e2.domain_name and e1.hostname = e2.hostname and e1.GUID = e2.GUID 
            and (e2.last_modified_time between '$range_begin' and '$range_end') and e2.status = 1)) e 
            left join (select * from identityFound i1 where start_time = (select max(start_time) from identityFound i2 where i1.entryID = i2.entryID and i2.status = 0)) i
         on e.entryID = i.entryID
      order by e.create_time DESC
      ";
   */

   if($result = mysqli_query($link, $sql)){
      
      $computer_count = 0;
      $pdata_computer_count = 0;
      
      $nExtremeFile = 0;
      $nHighFile = 0;
      $nMediumFile = 0;
      $nLowFile = 0;

      $nExtremeData = 0;
      $nHighData = 0;
      $nMediumData = 0;
      $nLowData = 0;

      foreach ($arr_identity_type as $type){
         $arr_sum_risk_count[$type] = 0;
         $arr_iFound_risk_count[$type] = 0;  // #001 Add
      }

      ////////////////////////////
      // #001 begin
      ////////////////////////////
      
      while($row = mysqli_fetch_assoc($result)){
         
         $xmlid = $row["XMLID"];
         $create_time = $row["create_time"];
         $hostname = $row["hostname"];
         $domain_name = $row["domain_name"];
         $com_login_name = $row["login_name"];  // #007

         // #007, add escape for sql
         $hostname_escape = mysql_real_escape_string($hostname);
         $domain_name_escape = mysql_real_escape_string($domain_name);
         $com_login_name_escape = mysql_real_escape_string($com_login_name);

         // #004 modified
         // #005 modified
         // #007 modified, add the constraint on login_name when using diff
         $sql2 = "select XMLID,create_time,ip,hostname,domain_name,login_name,employee_name,department,count0,count1,count2,count3,count4,count5,count6,count7,start_time,end_time,total_file, employee_email,
                  scan_express_timeout, scan_express_count
                  from identityFound where GUID = '$guid' and hostname = '$hostname_escape' and domain_name = '$domain_name_escape' and login_name = '$com_login_name_escape'
                  and create_time <= '$create_time' and status = 0 order by create_time desc";

         $iFound_id = -1;
         $iFoundLast_id = -1;
         $iAction_id = -1;
         $iFound_time = "1970-01-01 00:00:00";
         $iFoundLast_time = "1970-01-01 00:00:00";
         $iAction_time = "1970-01-01 00:00:00";
         $xml_file = "";
         $path_file = "";
         $mtime_file = "";
         $count_file = "";
         $action_file = "";
         $ip = "";
         $login_name = "";
         $department = "";
         $start_time = "";
         $end_time = "";
         $total_file = 0;
         $nFile = 0;
         $employee_email = "";
         
         if($result2 = mysqli_query($link, $sql2)){
            while($row2 = mysqli_fetch_assoc($result2)){
               $xmlid_new = $row2["XMLID"];
               $create_time_new = $row2["create_time"];
               $nFile = $row2["nFile"];
               
               if($iFound_id == -1 && strtotime($create_time_new) < strtotime($range_begin)){
                  break;
               }
               if($iFound_id != -1 && $iFoundLast_id != -1 && $iAction_id != -1){
                  break;
               }
               $yymm = substr($create_time_new, 0, 4). substr($create_time_new, 5, 2);
               $file_prefix = "$working_path/upload_old/$guid/$yymm/$xmlid_new";
               $xml_file = "$file_prefix-$guid.xml";
               $path_file = "$file_prefix.path";
               $mtime_file = "$file_prefix.mtime";
               $count_file = "$file_prefix.count";
               $action_file = "$file_prefix.action";
               
               /////////////////////////////////
               // #002
               // The scan successes in the two following cases:
               //    case 1. xml, path, mtime, count, all exist
               //    case 2. xml exists, and nFile == 0 (the scan is successful, and there is no privacy data)
               // //////////////////////////////

               if((file_exists($xml_file) && file_exists($path_file) && file_exists($mtime_file) && file_exists($count_file)) || (file_exists($xml_file) && $nFile == 0)){

                  // #002 Create empty path, mtime and count files if not exist
                  if(!file_exists($path_file)){
                     if(($fp_tmp = @fopen($path_file,"w")) == FALSE){
                        sleep(DELAY_SEC);
                        echo -__LINE__;
                        return;
                     }
                     fclose($fp_tmp);
                  }
                  if(!file_exists($mtime_file)){
                     if(($fp_tmp = @fopen($mtime_file,"w")) == FALSE){
                        sleep(DELAY_SEC);
                        echo -__LINE__;
                        return;
                     }
                     fclose($fp_tmp);
                  }
                  if(!file_exists($count_file)){
                     if(($fp_tmp = @fopen($count_file,"w")) == FALSE){
                        sleep(DELAY_SEC);
                        echo -__LINE__;
                        return;
                     }
                     fclose($fp_tmp);
                  }
                  if($iFound_id == -1){
                     $iFound_id = $xmlid_new;
                     $iFound_time = $create_time_new;
                     $ip =  $row2["ip"];
                     $hostname = $row2["hostname"];
                     $domain_name = $row2["domain_name"];
                     $login_name = $row2["employee_name"];
                     $department = $row2["department"];
                     $start_time = $row2["start_time"];
                     $end_time = $row2["end_time"];
                     $total_file = $row2["total_file"];
                     $employee_email = $row2["employee_email"];
                     
                     // #006
                     $scan_express_timeout = $row2["scan_express_timeout"];
                     $scan_express_count = $row2["scan_express_count"];

                     // #003, add the followig two lines for converting back
                     $login_name = decode_escape_string($login_name);
                     $department = decode_escape_string($department);

                     foreach($arr_identity_type as $type){
                        $arr_iFound_risk_count[$type] = $row2["count$type"];
                     }
                  }
                  else if($iFoundLast_id == -1){
                     $iFoundLast_id = $xmlid_new;
                     $iFoundLast_time = $create_time_new;
                  }
                  if($iAction_id == -1 && file_exists($action_file)){
                     $iAction_id = $xmlid_new;
                     $iAction_time = $create_time_new;
                  }
               }
               else{
                  $sql3 = "update identityFound set status = -1 
                     where GUID = '$guid' and hostname = '$hostname_escape' and domain_name = '$domain_name_escape'
                     and XMLID = '$xmlid_new' and create_time = '$create_time_new'";
                  mysqli_query($link, $sql3);
               }
            }
            if($iFound_id != -1){
               $computer_count++;
               $pdata_flag = 0;

               foreach($arr_identity_type as $type){
                  if($arr_iFound_risk_count[$type] > 0){
                     $pdata_flag = 1; 
                     $arr_sum_risk_count[$type] += $arr_iFound_risk_count[$type];
                  }
               }
   
               if($pdata_flag){
                  $pdata_computer_count++;
               }
               //////////////////////////////////////////////
               // start_time and end_time 只取到分
               // Ex. 2012-05-08 22:17:17 -> 2012-05-08 22:17
               //////////////////////////////////////////////
               $start_time = substr($start_time,0,16);
               $end_time = substr($end_time,0,16);

               $diff_time = strtotime($end_time) - strtotime($start_time);

               //////////////
               // 算 diff_day
               //////////////
               $diff_day = (int)($diff_time / 86400);
               $diff_time -= $diff_day * 86400;

               ///////////////
               // 算 diff_hour
               ///////////////
               $diff_hour = (int)($diff_time / 3600);
               $diff_time -= $diff_hour * 3600;

               /////////////////
               // 算 diff_minute
               /////////////////
               $diff_minute = (int)($diff_time / 60);

               $spent_time = "${diff_day}天${diff_hour}小時${diff_minute}分";
               //$spent_time = iconv('BIG-5','UTF-8',$spent_time);  #005 comment out, 這個檔案已經被轉成utf8了

               /////////////////
               // #002
               //  原本只有在$pdata_flag為1時才會寫，現在改成無論如何都會寫
               /////////////////

               // #004, add "employee_email" for writing $f
               // #006, add "scan_express_timeout" and "scan_express_count" for writing $f
               // #007, add "com_login_name" for writing $f
               fwrite($f,"$iFound_id\t$iFound_time\t$ip\t$hostname\t$domain_name\t$login_name\t$department\t$start_time\t$end_time\t$spent_time\t$total_file\t$employee_email\t$scan_express_timeout\t$scan_express_count\t$com_login_name\n");
               fwrite($f2,"$iFoundLast_id\t$iFoundLast_time\n");
               fwrite($f3,"$iAction_id\t$iAction_time\n");
            }
            mysqli_free_result($result2);
         }
      }

      fclose($f);
      fclose($f2);
      fclose($f3);
      ////////////////////////////
      // #001 end
      ////////////////////////////
      
      # write basic info to file

      if(($f = @fopen("$file_path/desc","w")) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }

      $risk_count = implode("\t",$arr_sum_risk_count); 

      fwrite($f,"$computer_count\t$pdata_computer_count\t$riskCategorySelect\n");
      fwrite($f,"$risk_count\n");

      fclose($f);

      // 呼叫 perl, 會產生 result.tmp, data.total, [top10_extreme.file, top10_high.file, top10_extreme.data, top10_high.data]

      if($pdata_computer_count > 0){

         system("$working_path/genReport.pl $iFound_file"); 

         // read data total count

         if(($f = @fopen("$file_path/data.total","r")) == FALSE){
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }

         if(($line = fgets($f)) == FALSE){
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }

         $line = trim($line);
         list($nExtremeData,$nHighData,$nMediumData,$nLowData) = explode("\t",$line);

         fclose($f);


         // read result info

         if(($lines = @file("$file_path/result.tmp")) == FALSE){
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         } 

         foreach($lines as $line){

            $line = trim($line);
            list($department,$computer,$login_name,$ip,$extreme,$high,$medium,$low) = explode("\t",$line);

            if($extreme > 0){
               $arr_nDepExtremeFile[$department] += $extreme;
               $nExtremeFile += $extreme;
            }

            if($high > 0){
               $arr_nDepHighFile[$department] += $high;
               $nHighFile += $high;
            }

            if($medium > 0){
               $arr_nDepMediumFile[$department] += $medium;
               $nMediumFile += $medium;
            }

            if($low > 0){
               $arr_nDepLowFile[$department] += $low;
               $nLowFile += $low ;
            }

         }

      }

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
   /////////////////
   // yaoan end add
   /////////////////

   //////////////////////////////////////////////////////////////////////////
   //---------------------------------
   //----- 3. Create json format -----
   //---------------------------------
   
   $arr_json["report_name"] = urlencode($report_name);
   $arr_json["create_time"] = urlencode($now_time);
   $arr_json["last_modified_time"] = urlencode($now_time);
   $arr_json["fileFolder"] = urlencode($folder_name);
   $arr_json["fileName"] = urlencode(REPORT_FILE_NAME);   //from DB.conf
   $arr_json["nExtremeFile"] = urlencode($nExtremeFile);
   $arr_json["nExtremeData"] = urlencode($nExtremeData);
   $arr_json["nHighFile"] = urlencode($nHighFile);
   $arr_json["nHighData"] = urlencode($nHighData);
   $arr_json["nMediumFile"] = urlencode($nMediumFile);
   $arr_json["nMediumData"] = urlencode($nMediumData);
   $arr_json["nLowFile"] = urlencode($nLowFile);
   $arr_json["nLowData"] = urlencode($nLowData);
   $arr_json["identity_type"] = urlencode($identity_type);
   $arr_json["range_begin"] = urlencode($range_begin);
   $arr_json["range_end"] = urlencode($range_end);
   $arr_json["riskCategorySelect"] = urlencode($riskCategorySelect);
   
   //////////////////////////////////////////////////////////////////////////
   //-------------------------------
   //----- 4. gen report chart -----
   //-------------------------------
   
   if(file_exists(DRAW_PATH))
   {
      include_once(DRAW_PATH);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;   
   }
   $swfStr = drawChart($nLowFile, $nMediumFile, $nHighFile, $nExtremeFile, $arr_sum_risk_count,
      $arr_nDepExtremeFile, $arr_nDepHighFile, $arr_nDepMediumFile, $arr_nDepLowFile, $arr_nCompHighFile, $arr_nCompExtremeFile, $name_timestamp); 
   if(!$swfStr)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;   
   }
   
   //////////////////////////////////////////////////////////////////////////
   //------------------------------------
   //----- 5. return srting to ajax ----- 
   //------------------------------------
   
   $str_return = json_encode($arr_json) . ";" . $swfStr;
   echo $str_return;

   return;
   
?>

