<?php
///////////////////////////////
// OSC_index_query.php
//
// 系統管理者 可以看到全部 customer 的清查中, 已完成, 已逾時, 未實施
// 點選 customer name 可以用 system admin 身分 hack 進去
//
// #000 created by Phantom 2014/09/15
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
   <table class="report" border="0" cellspacing="0" cellpadding="0">
      <colgroup>
         <col class="cIndex" />
         <col class="cName" />
         <col class="cCompleted" />
         <col class="cWaiting" />
         <col class="cDropped" />
         <col class="cNotyet" />
         <col class="cPercentage" />
      </colgroup>
      <tr>
         <th>序號</th>
         <th>分行部門</th>
         <th>已完成</th>
         <th>清查中</th>
         <th>已逾時</th>
         <th>未實施</th>
         <th>成功率</th>
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
   // Find all the GUIDs from customer with status=1
   ////////////////
   $sql = "select GUID,name from customer where status=1";

   $total_count = 0;
   if ($result_customer = mysqli_query($link, $sql)) {
      while ($row_customer = mysqli_fetch_assoc($result_customer)) {
         ///////////////////////////
         // For each GUID in customer DB (with status=1)
         ///////////////////////////
         $total_count ++; 
         $GUID = $row_customer["GUID"];
         $name = $row_customer["name"];

         ////////////////
         // sql command to query DB, find every record in "entry"
         ////////////////

         $sql = "
            select e.GUID, e.entryID as e_entryID, i.entryID as i_entryID, i.XMLID, e.hostname, e.domain_name, i.nFile, i.department, i.employee_name, 
            e.create_time, e.upload_time, i.start_time, i.end_time, e.status as e_status, i.status as i_status
               from (select * from entry where GUID = '$GUID' $time_range) e 
                  left join (select * from identityFound i1 where XMLID = (select max(XMLID) from identityFound i2 where i1.entryID = i2.entryID)) i
                  on e.entryID = i.entryID
               order by e.create_time DESC
            ";

         // a 2-D array to store the scan history of each pc
         $arr_total = array();
   
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
               ////////////////////////////

               if(array_key_exists($pc_name, $arr_total) == FALSE){
                  // put information to arr_total
                  $arr_total[$pc_name] = "";
                  if($login_level == 2){
                     if(!in_array($department, $arr_dept_list[1]))
                        continue;
                  }
                  if($e_status === COMPLETED || $e_status === DELETED){
                     $completed_count++;
                  }
                  else if($e_status === WAITING_UPLOAD || $e_status === WAITING_PARSE){
                     $waiting_count++;
                  }
                  else if($e_status === DROPPED || $e_status === PARSE_FAIL){
                     $dropped_count++;
                  }
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
   
         ////////////////
         // notyet
         ////////////////
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

         if($result = mysqli_query($link, $sql)){
            $notyet_count = mysqli_num_rows($result);
            mysqli_free_result($result);
         }

         ///////////////////////////////
         // Print result here
         ///////////////////////////////
         $total = $completed_count + $dropped_count + $waiting_count + $notyet_count;
         if ($total == 0)
            $percentage_str = "0%";
         else {
            $percentage = (float)$completed_count / $total * 100;
            $percentage_str = number_format($percentage,2) . "%";
         }
         echo "<tr>
               <td><span class='cIndex'>$total_count</span></td>
               <td><span class='cName'><a href=branchQueryRedirect.php?GUID=$GUID target=_blank>$name</a></span></td>
               <td><span class='cCompleted'>$completed_count</span></td>
               <td><span class='cWaiting'>$waiting_count</span></td>
               <td><span class='cDropped'>$dropped_count</span></td>
               <td><span class='cNotyet'>$notyet_count</span></td>
               <td><span class='cPercentage'>$percentage_str</span</td>
               </tr>";
      }
      mysqli_free_result($result_customer);
   }
   else {
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
   // For empty
   ///////////////
   
   if($total_count == 0)
      echo "<td colspan='7' class='empty'>無任何結果，請重新查詢</td>";

   echo "</table>";
?>
