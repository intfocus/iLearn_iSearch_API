<?php
////////////////////////////////////////
//searchIdentityFile.php
//
//1.get information from client
//2.get default extreme setting from DB
//3.search files
//4.return string of the refreshed Pages
// 
// # 001 modified by Odie 2013/04/26
//       To support new feature: mutli-level admin
//       1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//          admin => 1
//          user  => 2
//       2. If user, restrict the department he can see
//
// # 002 modified by Odie 2013/07/03
//       Add the choice of showing encrypted file list in search result
//       => write a parameter to iFound file for search.pl to process
//
// # 003 modified by Odie 2013/09/10
//       1. Modified type "0,1,2,3,4,5,6" to "0,1,2,3,4,5,6,7" due to 8th type
//       2. Write the name of 8th type to iFound file
// # 004 modified by Odie 2013/09/25
//       1. replace SPACE with "&nbsp;" so it can show the corret numbers of SPACEs
////////////////////////////////////////

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
   define(SEARCH_RISK_LIMIT, 7);                          //極高(1)+高(2)+中(4), 和個資種類無關
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
      if($check_str < 0 || $check_str > SEARCH_RISK_LIMIT)
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
   /*
   //----- Name Mask -----
   function NameMask($name)
   {
      $pattern = '[a-zA-Z]';
      $mask_symbol = '?';
      $name_masked = $name;
      //////////////////////////
      //判斷$name為中文或是英文
      /////////////////////////
      if(preg_match("/^[a-zA-Z]/",$name_masked))
      {   
         $i;
         //////////////////////////
         //如果是英文,
         //對英文名字的第一個單字,
         //將第一個字母後的字都mask
         /////////////////////////
         for($i = 1 ; preg_match("/^[a-zA-Z]/",substr($name_masked, $i, 1)) && $i < strlen($name_masked) ; $i++)
         {
            $name_masked[$i] = '?';
         }
      }
      else 
      {
         //////////////////////////
         //如果是中文,
         //對中文名字
         // 如果姓名為三個字以上mask倒數第二個字
         // 如果姓名為兩個字mask第二個字
         /////////////////////////
         if(mb_strlen($name_masked, 'UTF-8') == 2)
         {         
            $name_masked = implode(array(mb_substr($name_masked, 0, 1,'UTF-8'),"?",
            mb_substr($name_masked, 2,  mb_strlen($name_masked, 'UTF-8') - 2,'UTF-8')));               
         }
         else if(mb_strlen($name_masked, 'UTF-8') > 2)
         {
            $name_masked = implode(
            array(mb_substr($name_masked, 0, mb_strlen($name_masked, 'UTF-8') - 2,'UTF-8'),
            "?",
            mb_substr($name_masked, mb_strlen($name_masked, 'UTF-8') - 1,  1,'UTF-8')));                  
         }
      }
      return $name_masked;
   }
   */
   
   //get data from client
   $cmd;
   $computerName;
   $employee_name;
   $fileName;
   $departName;
   $riskCheckbox;
   $lastModifyTimeBegin;
   $lastModifyTimeEnd;
   $createTimeBegin;
   $createTimeEnd;
   $departID;
   $encryptCheckbox;        // #002

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   
   //data
   $risk_extreme_type;
   $risk_extreme_type_num;
   $risk_extreme_threshold;
   $risk_high_threshold;
   $risk_low_threshold;
   
   //search table
   $num;
   $search_num;
   $computer_name;
   $employee_name;
   $department;
   $risk_type;
   $last_modify;
   $temp_last_modify;
   $filetype;
   $type_count_num;
   $type_found;
   $filepath;
   $type8_enable = 0;            // #003 add
   $type8_name = "生日";         // #003 add
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($computerName = check_name($_GET["computerName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($employeeName = check_name($_GET["employeeName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($fileName = check_name($_GET["fileName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($departName = check_name($_GET["departName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($riskCheckbox = check_number($_GET["riskCheckbox"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($lastModifyTimeBegin = check_range_begin($_GET["lastModifyTimeBegin"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($lastModifyTimeEnd = check_range_end($_GET["lastModifyTimeEnd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($createTimeBegin = check_range_begin($_GET["createTimeBegin"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($createTimeEnd = check_range_end($_GET["createTimeEnd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   // #002
   if(($encryptCheckbox = check_encrypt($_GET["encryptCheckbox"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      //echo SYMBOL_ERROR;
      echo $encryptCheckbox;
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
   
   //----- check TRIAL or not -----
   $str_query1 = "select * from customer where GUID = '$GUID'";
   if ($result = mysqli_query($link, $str_query1))
   {
      $row = mysqli_fetch_assoc($result);
      if (mysqli_num_rows($result) > 0)
      {
         $status = $row["status"];
         mysqli_free_result($result);
         unset($row);
      }
      else
         $status = TRIAL;
   }
   else
      $status = TRIAL;

   if ($status == TRIAL)
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   
   //----- query -----
   $str_query1 = "
      select *
      from riskCategory
      where GUID = '" . $GUID . "'"; 
   
   //2.get default extreme setting from DB
   $flag = 0;
   if ($result = mysqli_query($link, $str_query1))
   {
      if ($row = mysqli_fetch_assoc($result))
      {
         $flag = 1;
         $risk_extreme_type = $row["extreme_type"];
         $risk_extreme_type_num = $row["extreme_type_num"];
         $risk_extreme_threshold = $row["extreme"];
         $risk_high_threshold = $row["high"];
         $risk_low_threshold = $row["low"];
         
         // #003 begin 
         $type8_enable = $row["type8_enable"];
         if ($type8_enable == 1)
            $type8_name = $row["type8_name"];
         // #003 end
      }
      mysqli_free_result($result);
      unset($row);

      if ($flag == 0)  //使用系統預設值
      {
         $str_query = "
            select * 
            from riskCategory 
            where GUID = '" . DEFAULT_GUID . "'";
         if ($result = mysqli_query($link, $str_query))
         {
            $row = mysqli_fetch_assoc($result);
            $risk_extreme_type = $row["extreme_type"];
            $risk_extreme_type_num = $row["extreme_type_num"];
            $risk_extreme_threshold = $row["extreme"];
            $risk_high_threshold = $row["high"];
            $risk_low_threshold = $row["low"];
            mysqli_free_result($result);
            unset($row);
         }
         else
         {
            if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo DB_ERROR;  
         }
      }

      $risk_check_type = '0,1,2,3,4,5,6,7';  //高中低判別種類  #003
      $arr_identity_type = explode(',',$risk_check_type);

      /////////////////////
      // yaoan 20120511 add
      /////////////////////
      
      $timestamp = date('U');
      $pid = getmypid();

      $work_path = dirname(__FILE__) . "/search_work"; 

      if(!file_exists($work_path)){
         if(mkdir("$work_path",0755,true) == FALSE){
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }
      }

      $iFound_file = "$work_path/iFound.${timestamp}_$pid";

      if(($f = fopen($iFound_file,"w")) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }

      fwrite($f,"$GUID\n");
      fwrite($f,"$riskCheckbox\t$risk_low_threshold\t$risk_high_threshold\t$risk_extreme_threshold\t$risk_extreme_type_num\t$risk_extreme_type\t$type8_enable\t$type8_name\n");  // #003
      fwrite($f,"$risk_check_type\n");
      fwrite($f,"$encryptCheckbox\n"); // #002
      if (!get_magic_quotes_gpc()) 
      {
         if ($computerName != "")
            $computerName = "and hostname like '%" . mysql_real_escape_string($computerName) . "%'";
         if ($employeeName != "")
            $employeeName = "and employee_name = '" . mysql_real_escape_string($employeeName) . "'";
         if ($fileName != "")
            $fileName = "and filepath like '%" . mysql_real_escape_string($fileName) . "%'";
         if ($departName != "")
            $departName = "and department = '" . mysql_real_escape_string($departName) . "'";
      }
      else 
      {
         if ($computerName != "")
            $computerName = "and hostname like '%" . $computerName . "%'";
         if ($employeeName != "")
            $employeeName = "and employee_name = '" . $employeeName . "'";
         if ($fileName != "")
            $fileName = "and filepath like '%" . $fileName . "%'";
         if ($departName != "")
            $departName = "and department = '" . $departName . "'";
      }

      //create time range
      if ($createTimeBegin != '' && $createTimeEnd != '')
      {
         $createTimeRenge = "(t.create_time between '$createTimeBegin' and '$createTimeEnd') and";
      }
      else if ($createTimeBegin == '' && $createTimeEnd != '')
      {
         $createTimeRenge = "(t.create_time < '$createTimeEnd') and";
      }
      else if ($createTimeBegin != '' && $createTimeEnd == '')
      {
         $createTimeRenge = "(t.create_time > '$createTimeBegin') and";
      }
      else if ($createTimeBegin == '' && $createTimeEnd == '')
      {
         $createTimeRenge = "";
      }

      if($lastModifyTimeBegin != ''){
         $lmtime_begin = date('U',strtotime($lastModifyTimeBegin));
      }
      else{
         $lmtime_begin = 0;
      }

      if($lastModifyTimeEnd != ''){
         $lmtime_end = date('U',strtotime($lastModifyTimeEnd));
      }
      else if($lmtime_begin != 0){
         $lmtime_end = date('U');
      }
      else{
         $lmtime_end = 0;
      }

      fwrite($f,"$lmtime_begin\t$lmtime_end\n");

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

      /////////////////////
      // prepare the SQL command and query DB
      // #001, a user can only see some records of his departments
      ////////////////

      if($login_level == 1){
         $sql = "
            select XMLID,create_time,hostname,domain_name,employee_name,department,count0,count1,count2,count3,count4,count5,count6
            from identityFound as iFound
            where iFound.GUID = '$GUID' and status = 0 $computerName $employeeName $departName and
            iFound.create_time = (
               select max(t.create_time)
               from identityFound as t
               where t.GUID = iFound.GUID and
               $createTimeRenge
               t.hostname = iFound.hostname and
               t.domain_name = iFound.domain_name and
               t.login_name = iFound.login_name and
               t.department = iFound.department and
               t.employee_name = iFound.employee_name and
               t.status = iFound.status
            )
            ";
      }
      else if($login_level == 2){
         $sql = "
            select XMLID,create_time,hostname,domain_name,employee_name,department,count0,count1,count2,count3,count4,count5,count6
            from identityFound as iFound
            where iFound.GUID = '$GUID' and status = 0 $computerName $employeeName $departName and
            iFound.create_time = (
               select max(t.create_time)
               from identityFound as t
               where t.GUID = iFound.GUID and
               $createTimeRenge
               t.hostname = iFound.hostname and
               t.domain_name = iFound.domain_name and
               t.login_name = iFound.login_name and
               t.department = iFound.department and
               t.employee_name = iFound.employee_name and
               t.status = iFound.status
            ) and department in ($dept_list)
            ";
      }
      if($result = mysqli_query($link, $sql)){

         while($row = mysqli_fetch_assoc($result)){

            $xmlid = $row["XMLID"];
            $create_time = $row["create_time"];
            $hostname = $row["hostname"];
            $domain_name = $row["domain_name"];
            $login_name = $row["employee_name"];
            $dept = $row["department"];

            $pdata_flag = 0;

            foreach ($arr_identity_type as $type){
               if($row["count$type"] > 0){
                  $pdata_flag = 1;
                  break;
               }
            }
            
            if($pdata_flag){
               fwrite($f,"$xmlid\t$create_time\t$hostname\t$domain_name\t$login_name\t$dept\n");
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

      fclose($f);
      
      // 呼叫 perl, 會產生 result file 
      system("$working_path/search.pl $iFound_file");
      
      $result_file = "$work_path/result.${timestamp}_$pid";

      if(!file_exists($result_file)){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }

      if(($lines = @file($result_file)) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }

      unlink($result_file);

      // 第一行 是 match count
      $match_count = $lines[0];

      // 取得 topn
      $topn = count($lines) - 1;

      // 逐行 get 
      for($n = 1; $n <= $topn; ++$n){

         $search_num[] = $n;
         $fileID[] = 0;

         $line = trim($lines[$n]);
         $line = 
         list(
            $xmlID[],
            $computer_name[],
            $employee_name[],
            $department[],
            $risk_type[],
            $last_modify[],
            $filetype[],
            $type_count_num[],
            $type_found[],
            $filepath[]
         ) = explode("\t",$line);

         // #004 add
         $filepath[$n-1] = str_replace(" ", "&nbsp;", $filepath[$n-1]);
      }

      ////////////////
      // yaoan end add
      ////////////////
   }
   else
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo DB_ERROR;
      return;
   }


   //4.return string of the refreshed Pages
   //----- Print Search Pages -----
   $return_string = "";
   $page_default_no = 1;
   $page_size = PAGE_SIZE;
   $row_number = $match_count;
   
   $return_string = $return_string . "<div id=\"sResultTitle\" class=\"sResultTitle\">查詢結果 : 共有 <span>" 
                                   . number_format($row_number) 
                                   . "</span> 筆檔案符合查詢條件</div>";
   if ($row_number > SEARCH_SIZE)
      $row_number = SEARCH_SIZE;
   $page_num = (int)(($row_number - 1) / $page_size + 1);
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">"
                                   . "<input type=\"hidden\" id=search_no value=$row_number>"
                                   . "<input type=\"hidden\" name=search_page_no value=1>"
                                   . "<input type=\"hidden\" name=search_page_size value=" . $page_size . ">";
   if ($page_num > 1)
   {
      for ($i = 0; $i < $page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"search_page";
         if ($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=search_page_begin_no_" . ($i + 1) . " OnClick=clickSearchPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span class=\"btn expandSR\" OnClick=\"expandSearchContentFunc();\">顯示過長內文</span>"
                                   . "</div>";                   
   
   //----- Print Search Tables -----
   //----- If No Data -----
   if ($row_number == 0)
   {
      $return_string = $return_string . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                      . "<colgroup>"
                                      . "<col class=\"num\"/>"
                                      . "<col class=\"comName\"/>"
                                      . "<col class=\"name\"/>"
                                      . "<col class=\"department\"/>"
                                      . "<col class=\"level\"/>"
                                      . "<col class=\"lastUpdate\"/>"
                                      . "<col class=\"fileType\"/>"
                                      . "<col class=\"pAmount\"/>"
                                      . "<col class=\"pType\"/>"
                                      . "<col class=\"path\"/>"
                                      . "<col class=\"action\"/>"
                                      . "</colgroup>"
                                      . "<tr>"
                                      . "<th>編號</th>"
                                      . "<th>電腦名稱</th>"
                                      . "<th>員工姓名</th>"
                                      . "<th>部門</th>"
                                      . "<th>風險等級</th>"
                                      . "<th>最後修改</th>"
                                      . "<th>檔案類型</th>"
                                      . "<th>個資數量</th>"
                                      . "<th>個資種類</th>"
                                      . "<th>檔案路徑</th>"
                                      . "<th>動作</th>"
                                      . "</tr>"
                                      . "<tr>"
                                      . "<td colspan=\"11\" class=\"empty\">請輸入上方查詢條件，並點選\"開始查詢\"</td>"
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
            $return_string = $return_string . "<div id=\"search_page" . $page_no . "\" ";
            if ($page_no == 1)
               $return_string = $return_string . "style=\"display:block;\"";
            else
               $return_string = $return_string . "style=\"display:none;\"";
            $return_string = $return_string . ">"
                                            . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                            . "<colgroup>"
                                            . "<col class=\"num\"/>"
                                            . "<col class=\"comName\"/>"
                                            . "<col class=\"name\"/>"
                                            . "<col class=\"department\"/>"
                                            . "<col class=\"level\"/>"
                                            . "<col class=\"lastUpdate\"/>"
                                            . "<col class=\"fileType\"/>"
                                            . "<col class=\"pAmount\"/>"
                                            . "<col class=\"pType\"/>"
                                            . "<col class=\"path\"/>"
                                            . "<col class=\"action\"/>"
                                            . "</colgroup>"
                                            . "<tr>"
                                            . "<th>編號</th>"
                                            . "<th>網域名稱/電腦名稱</th>"
                                            . "<th>員工姓名</th>"
                                            . "<th>部門</th>"
                                            . "<th>風險等級</th>"
                                            . "<th>最後修改</th>"
                                            . "<th>類型</th>"
                                            . "<th>個資數量</th>"
                                            . "<th>個資種類</th>"
                                            . "<th>檔案路徑</th>"
                                            . "<th>動作</th>"
                                            . "</tr>";
            /*                                
            $return_string = $return_string . ">"
                                            . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                            . "<colgroup>"
                                            . "<col class=\"num\"/>"
                                            . "<col class=\"comName\"/>"
                                            . "<col class=\"name\"/>"
                                            . "<col class=\"department\"/>"
                                            . "<col class=\"level\"/>"
                                            . "<col class=\"lastUpdate\"/>"
                                            . "<col class=\"fileType\"/>"
                                            . "<col class=\"pAmount\"/>"
                                            . "<col class=\"pType\"/>"
                                            . "<col class=\"path\"/>"
                                            . "<col class=\"action\"/>"
                                            . "</colgroup>"
                                            . "<tr>"
                                            . "<th>編號</th>"
                                            . "<th>網域名稱/電腦名稱</th>"
                                            . "<th>員工姓名</th>"
                                            . "<th>部門</th>"
                                            . "<th>風險等級</th>"
                                            . "<th>最後修改</th>"
                                            . "<th>檔案類型</th>"
                                            . "<th>個資數量</th>"
                                            . "<th>"
                                            . "<span class=\"fixP\">個資種類 "
                                            . "<a class=\"typeDesBtn\" OnMouseOver=\"showTypeDis();\" OnMouseOut=\"hideTypeDis();\">[?]</a>"
                                            . "<span class=\"typeDes\" style=\"display:none;\">"
                                            . "<ul>"
                                            . "<div>個資種類說明 : </div>"
                                            . "<li>N=姓名</li>"
                                            . "<li>T=市話號碼</li>"
                                            . "<li>M=手機號碼</li>"
                                            . "<li>A=地址</li>"
                                            . "<li>E=電子郵件地址</li>"
                                            . "<li>I=身分證號碼</li>"
                                            . "<li>C=信用卡</li>"
                                            . "</ul>"
                                            . "</span>"
                                            . "</span>"
                                            . "</th>"
                                            . "<th>檔案路徑</th>"
                                            . "<th>動作</th>"
                                            . "</tr>";
            */                                
         }
         if ($page_count < $page_size)
         {
            $return_string = $return_string . "<tr>"
                                            . "<td>$search_num[$i]</td>"
                                            . "<td><span id=\"content_computer_name$search_num[$i]\" class=\"comName fixWidth\">$computer_name[$i]</span></td>"
                                            . "<td><span id=\"content_employee_name$search_num[$i]\" class=\"name\">$employee_name[$i]</span></td>"
                                            . "<td><span id=\"content_department$search_num[$i]\" class=\"department fixWidth\">$department[$i]</span></td>"
                                            . "<td>$risk_type[$i]</td>";
            if($risk_type[$i] == "未知")
            {
               $return_string = $return_string . "<td><span id=\"content_last_modify$search_num[$i]\">$last_modify[$i]</span></td>"
                                               . "<td><span id=\"content_filetype$search_num[$i]\">$filetype[$i]</span></td>"
                                               . "<td><span id=\"content_type_count_num$search_num[$i]\">" . $type_count_num[$i] . "</span></td>"
                                               . "<td><span id=\"content_type_found$search_num[$i]\">$type_found[$i]</span></td>"
                                               . "<td><span id=\"content_filepath$search_num[$i]\" class=\"path fixWidth\">$filepath[$i]</span></td>"
                                               . "<td>-</td>"
                                               . "</tr>";
            }
            else
            {
               $return_string = $return_string . "<td><span id=\"content_last_modify$search_num[$i]\">$last_modify[$i]</span></td>"
                                               . "<td><span id=\"content_filetype$search_num[$i]\">$filetype[$i]</span></td>"
                                               . "<td><span id=\"content_type_count_num$search_num[$i]\">" . number_format($type_count_num[$i]) . "</span></td>"
                                               . "<td><span id=\"content_type_found$search_num[$i]\">$type_found[$i]</span></td>"
                                               . "<td><span id=\"content_filepath$search_num[$i]\" class=\"path fixWidth\">$filepath[$i]</span></td>"
                                               . "<td><a class=\"readFile\" OnClick=\"openContent($search_num[$i], $xmlID[$i],$type_count_num[$i]);\">觀看<br>內容</a></td>"
                                               . "</tr>";
            }
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
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
   
   //----- Print Search Pages -----
   if ($page_num > 1)
   {
      for ($i = 0; $i < $page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"search_page";
         if ($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=search_page_end_no_" . ($i + 1) . " OnClick=clickSearchPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "</div>";
   echo $return_string;
   return;

?>
