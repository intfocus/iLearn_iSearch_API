<?php
/*************************************************
 *createReport.php
 *1. Check receive information from client
 *2. Query customer information
 *3. Print to file
 *   3.1 create file folder
 *   3.2 generate HTML code
 *   3.3 print to file (original.tmpl)
 *   3.4 generate mht code 
 *   3.5 print to file (.mht)
 *   3.6 embedded picture in html
 *   3.7 print to file (.html)
 *   3.8 generate word code 
 *   3.9 print to file (.doc)
 *4. Insert to DB
 *5. Fork child process to generate excel, and raw data
 *6. Create refresh page string and return
 *  2012/02/15 Jeffrey Chan
 *
 * #001  Jeffrey  2012/04/11  
 *       escape report_name 
 * #002  Odie     2013/04/26
 *       To support new feature: mutli-level admin
 *       1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
 *          admin => 1
 *          user  => 2
 *       2. If user, restrict the departments he can see
 * #003  Odie     2013/09/11
 *       Add the 8th type of data
 *       1. Change the interface of genHtmlCode(), add the parameter for the name of the 8th type
 *************************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   define(GEN_HTML_CODE_PATH, "$working_path/genHtmlCode.php");
   define(GEN_TRIAL_HTML_CODE_PATH, "$working_path/genTrialHtmlCode.php");   
   define(ENCODE_PICTURE, "$working_path/encodePicture.php");
   define(CONVERT_TO_MHT, "$working_path/convertToMht.php");
   define(CONVERT_TO_WORD, "$working_path/convertToWord.php");
   define(EXEC_BACKGROUND_JOB, "$working_path/execBackgroundJob.php");
   define(REFRESH_REPORT_PATH, "$working_path/refreshReportPages.php");
   define(WORKING_LINK, $working_link);
   define(REPORT_LINK, $report_link);
   define(ILLEGAL_CHAR, "'-;<>");                              //illegal char
   define(ILLEGAL_CHAR_FOLDER_NAME, ".");                   
   define(TIME_ZONE, "Asia/Taipei");
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(REPORT_PATH, $report_path);                          //from DB.conf   
   define(REPORT_NAME, $report_file_name);                     //default report name
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(REPORT_NAME_LENGTH, 255);
   define(STR_LENGTH, 50);
   define(FOLDER_NAME_LENGTH, 20);
   define(ZIP_BIN, $zip_bin_path);
   define(ORIGINAL_HTML_NAME, "originalHtml");
   define(DEBUG, 0);
   //define(DEBUG, 1);
   //xml status
   define(XML_COMPLETED, 0);  //xml is completed
   //report status
   define(AVAILABLE, 0);
   define(DELETED, -1);   
   //return value
   define(FILE_ERROR, -3);
   define(SYMBOL_ERROR, -2);
   //check get info length
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
   //----- Check folder and name -----
   function check_folder_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > FOLDER_NAME_LENGTH)
      {
         
         return SYMBOL_ERROR;
      }
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR_FOLDER_NAME) == true)
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
   //----- Check datetime  -----
   function check_datetime($check_str)
   {
      //----- check empty string -----
      if(trim($check_str) == "")
      {

         return SYMBOL_ERROR;
      }
      //----- format mm/dd/yy to yyyy-mm-dd -----
      date_default_timezone_set(TIME_ZONE);
      if(($check_str = strtotime($check_str)) == "")
      {
         //----- str to time failure -----

         return SYMBOL_ERROR;
      }
      $check_str = date("Y-m-d H:i:s", $check_str);

      return $check_str; 
   }
   //----- Check report range begin -----
   function check_range_begin($check_str)
   {
      //----- check empty string -----
      if(trim($check_str) == "")
      {

         return SYMBOL_ERROR;
      }
      //----- format begin range mm/dd/yy to yyyy-mm-dd 00:00:00 -----
      date_default_timezone_set(TIME_ZONE);
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
      //----- check empty string -----
      if(trim($check_str) == "")
      {

         return SYMBOL_ERROR;
      }
      //----- format end range mm/dd/yy to yyyy-mm-dd 23:59:59 -----
      date_default_timezone_set(TIME_ZONE);

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
      //----- check number -----
      if(!is_numeric($check_str))
      {

         return SYMBOL_ERROR; 
      }
   
      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   //get from client
   $guid;
   $report_name;
   $create_time;
   $last_modified_time;
   $fileFolder;
   $fileName;
   $nExtremeFile;
   $nExtremeData;
   $nHighFile;
   $nHighData;
   $nMediumFile;
   $nMediumData;
   $nLowFile;
   $nLowData;
   $identity_type;
   $range_begin;
   $range_end;
   
   //customer information
   $customer_name; 
   //risk category
   $risk_low;              //low from risk category table
   $risk_high;             //high from risk category table
   $risk_extreme;          //extreme from risk category table
   $risk_extreme_type_num; //extreme type number from risk category table
   $risk_extreme_type;     //extreme type from risk category table
   $type8_enable;          // #003
   $type8_name;            // #003

   $file_numbers;
   $data_numbers; 
   $report_begin;
   $report_end;
   $computer_numbers;
   $privacy_computer_numbers;
   $arr_extreme_type;
   $arr_identity_type;
   $arr_sum_risk_count;
   $arr_extreme_owner;
   $arr_high_owner; 
   $arr_extreme_detail;
   $arr_high_detail;
   $arr_execBackgroundJob;
   $htmlName;
   $trialHtmlName;
   $mhtName;
   $trialMhtName;
   $wordName; 
   $reportPath;
   $htmlStr;
   //added by Dylan 20120306
   $trialHtmlStr;
   $mhtStr;
   //added by Dylan 20120306
   $trialMhtStr;
   $wordStr;
   $str_temp;
   $str_extreme_type;
   $str_identity_type;     //query string of identity type 
   $str_sum_identity_type;

   $str_query;
   
   //////////////////////////////////////////////////////////////////////////
   //----------------------------------------------------
   //----- 1. Check receive information from client -----
   //----------------------------------------------------
   
   //----- session check -----
   if(!DEBUG)
   {
      // #002, add checking $_SESSION["loginLevel"] and $_SESSION["loginName"]
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

   if(($report_name = check_report_name($_GET["report_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($create_time = check_datetime($_GET["create_time"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($last_modified_time = check_datetime($_GET["last_modified_time"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($fileFolder = check_folder_name($_GET["fileFolder"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($fileName = check_folder_name($_GET["fileName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nExtremeFile = check_number($_GET["nExtremeFile"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nExtremeData = check_number($_GET["nExtremeData"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nHighFile = check_number($_GET["nHighFile"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nHighData = check_number($_GET["nHighData"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nMediumFile = check_number($_GET["nMediumFile"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nMediumData = check_number($_GET["nMediumData"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nLowFile = check_number($_GET["nLowFile"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(($nLowData = check_number($_GET["nLowData"])) == SYMBOL_ERROR)
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
   date_default_timezone_set(TIME_ZONE);  
   $report_begin = date("Y/m/d", strtotime($range_begin));
   $report_end = date("Y/m/d", strtotime($range_end));
   $file_numbers = $nExtremeFile + $nHighFile + $nMediumFile + $nLowFile;
   if(DEBUG)
   {
      echo "<h1>information:</h1>$guid<br>$report_name<br>$create_time<br>$last_modified_time<br>
         $fileFolder<br>$fileName<br>$nExtremeFile<br>$nExtremeData<br>$nHighFile<br>$nHighData<br>
         $nMediumFile<br>$nMediumData<br>$nLowFile<br>$nLowData<br>$identity_type<br>$range_begin<br>
         $range_end<br>";
      echo "<h1>report begin:</h1>$report_begin<br><h1>report end</h1>$report_end<br>";
      echo "<h1>file numbers:</h1><br>$file_numbers<br><br>";
   }

   //////////////////////////////////////////////////////////////////////////
   //-----------------------------------------
   //----- 2. Query customer information -----
   //-----------------------------------------
 
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- query customer information -----
   $str_query = "
      select name
      from customer 
      where GUID = '$guid'";
   if($result = mysqli_query($link, $str_query))   //query customer information success
   {
      $row = mysqli_fetch_assoc($result);
      $customer_name = $row["name"];
      mysqli_free_result($result);
      unset($row);
   }
   else  //query customer information failure
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
         $type8_enable = $row["type8_enable"];              // #003
         $type8_name = $row["type8_name"];                  // #003
         mysqli_free_result($result);    //free useless result
         unset($row);    //clean array
      }
      //----- riskCategory doesn't have this GUID
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

   //----- split identity type and put into an array -----
   $arr_identity_type = explode(",", $identity_type);

   /////////////////////
   // yaoan 20120510 add
   /////////////////////

   $reportPath = REPORT_PATH . "/$guid/$fileFolder";

   if(($f = fopen("$reportPath/desc","r")) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   // get computer_numbers and privacy_comuter_numbers 

   if(($line = fgets($f)) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   $line = trim($line);
   list($computer_numbers, $privacy_computer_numbers) = explode("\t",$line);

   // get identity type data count and count all

   if(($line = fgets($f)) == FALSE){
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   fclose($f);

   $line = trim($line);
   $type_count = explode("\t",$line);
   $type_count_index = 0;
   $data_numbers = 0;

   foreach($arr_identity_type as $type){
      $arr_sum_risk_count[$type] = $type_count[$type_count_index];
      $data_numbers += $type_count[$type_count_index]; 
      $type_count_index++;
   }

   // get top 10 extreme file

   $top10_file =  "$reportPath/top10_extreme.file";
   if(file_exists($top10_file) == TRUE){

      if(($lines = @file($top10_file)) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      } 

      $count = 0; 
      foreach($lines as $line){

         $line = trim($line);
         list(
            $arr_extreme_owner[$count]['department'],
            $arr_extreme_owner[$count]['hostname'],
            $arr_extreme_owner[$count]['domain_name'],
            $arr_extreme_owner[$count]['login_name'],
            $arr_extreme_owner[$count]['totalFile'],
            $arr_extreme_owner[$count]['nFile']
         ) = explode("\t",$line);

         $count++;

      }

   }

   // get top 10 high file

   $top10_file =  "$reportPath/top10_high.file";
   if(file_exists($top10_file) == TRUE){

      if(($lines = @file($top10_file)) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      } 

      $count = 0; 
      foreach($lines as $line){

         $line = trim($line);
         list(
            $arr_high_owner[$count]['department'],
            $arr_high_owner[$count]['hostname'],
            $arr_high_owner[$count]['domain_name'],
            $arr_high_owner[$count]['login_name'],
            $arr_high_owner[$count]['totalFile'],
            $arr_high_owner[$count]['nFile']
         ) = explode("\t",$line);

         $count++;

      }

   }
   
   // get top 10 extreme data 

   $top10_file =  "$reportPath/top10_extreme.data";
   if(file_exists($top10_file) == TRUE){

      if(($lines = @file($top10_file)) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      } 

      $count = 0; 
      foreach($lines as $line){

         $line = trim($line);
         list(
            $arr_extreme_detail[$count]['department'],
            $arr_extreme_detail[$count]['hostname'],
            $arr_extreme_detail[$count]['domain_name'],
            $arr_extreme_detail[$count]['login_name'],
            $arr_extreme_detail[$count]['filePath'],
            $arr_extreme_detail[$count]['fileType'],
            $arr_extreme_detail[$count]['nFound']
         ) = explode("\t",$line);

         $count++;

      }

   }
   
   // get top 10 high data

   $top10_file =  "$reportPath/top10_high.data";
   if(file_exists($top10_file) == TRUE){

      if(($lines = @file($top10_file)) == FALSE){
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      } 

      $count = 0; 
      foreach($lines as $line){

         $line = trim($line);
         list(
            $arr_high_detail[$count]['department'],
            $arr_high_detail[$count]['hostname'],
            $arr_high_detail[$count]['domain_name'],
            $arr_high_detail[$count]['login_name'],
            $arr_high_detail[$count]['filePath'],
            $arr_high_detail[$count]['fileType'],
            $arr_high_detail[$count]['nFound']
         ) = explode("\t",$line);

         $count++;

      }

   }

   ////////////////
   // yaoan end add
   ////////////////

   //////////////////////////////////////////////////////////////////////////
   //----------------------------
   //----- 3. Print to file -----
   //----------------------------
  
   //----- generate HTML code  ----- 
   if(file_exists(GEN_HTML_CODE_PATH))
   {
      include_once(GEN_HTML_CODE_PATH);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $htmlStr = genHtmlCode($report_name, $create_time, $customer_name, $report_begin, $report_end, $computer_numbers, $arr_identity_type,
      $privacy_computer_numbers, $file_numbers, $data_numbers, $arr_sum_risk_count, $arr_extreme_type, $risk_extreme_type_num, $risk_extreme, $risk_high, $risk_low,
      $arr_extreme_owner, $arr_high_owner, $arr_extreme_detail, $arr_high_detail, $type8_enable, $type8_name);    // #003, add $type8_enable and $type8_name
   if($htmlStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $wordStr = $htmlStr;
   $mhtStr = $htmlStr;
   //added by Dylan 20120306   
   //----- generate Trial HTML code  ----- 
   if(file_exists(GEN_TRIAL_HTML_CODE_PATH))
   {
      include_once(GEN_TRIAL_HTML_CODE_PATH);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $trialHtmlStr = genTrialHtmlCode($report_name, $create_time, $customer_name, $report_begin, $report_end, $computer_numbers, $arr_identity_type,
      $privacy_computer_numbers, $file_numbers, $data_numbers, $arr_sum_risk_count, $arr_extreme_type, $risk_extreme_type_num, $risk_extreme, $risk_high, $risk_low,
      $arr_extreme_owner, $arr_high_owner, $arr_extreme_detail, $arr_high_detail, WORKING_LINK);
   if($trialHtmlStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $trialMhtStr = $trialHtmlStr;   
   //----- print to file (original tmpl) -----
   chdir($reportPath);  //change directory
   $originalHtmlName = ORIGINAL_HTML_NAME . ".tmpl";
   if(!file_put_contents("$reportPath/$originalHtmlName",$htmlStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //added by Dylan 20120306   
   //----- print to file (original trial tmpl) -----
   chdir($reportPath);  //change directory
   $originalTrialHtmlName = ORIGINAL_HTML_NAME . "_trial" . ".tmpl";
   if(!file_put_contents("$reportPath/$originalTrialHtmlName",$trialHtmlStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }   
   //----- generate mht code -----
   if(file_exists(CONVERT_TO_MHT))
   {
      include(CONVERT_TO_MHT);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   
   chdir($reportPath);  //change directory
   $mhtStr = getMhtDocument($mhtStr,"");
   //added by Dylan 20120306   
   $trialMhtStr = getMhtDocument($trialMhtStr,"");   
   if($mhtStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //added by Dylan 20120306  
   if($trialMhtStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- print to file (.mht) -----
   $mhtName = REPORT_NAME . ".mht";
   //added by Dylan 20120306   
   $trialMhtName = REPORT_NAME . "_trial" . ".mht";   
   if(!file_put_contents("$reportPath/$mhtName",$mhtStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //added by Dylan 20120306   
   if(!file_put_contents("$reportPath/$trialMhtName",$trialMhtStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }   
   //----- embedded picture in html -----
   if(file_exists(ENCODE_PICTURE))
   {
      include(ENCODE_PICTURE);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   chdir($reportPath);  //change directory
   $htmlStr = getPicDocument($htmlStr,"");
   //added by Dylan 20120306   
   $trialHtmlStr = getPicDocument($trialHtmlStr,"");
   if($htmlStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //added by Dylan 20120306
   if($trialHtmlStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }   
   //----- print to file (.html) -----
   $htmlName = REPORT_NAME . ".html";
   if(!file_put_contents("$reportPath/$htmlName",$htmlStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //added by Dylan 20120306   
   $trialHtmlName = REPORT_NAME . "_trial" . ".html";
   if(!file_put_contents("$reportPath/$trialHtmlName",$trialHtmlStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }   
   //----- generate word code -----
   if(file_exists(CONVERT_TO_WORD))
   {
      include(CONVERT_TO_WORD);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   chdir($reportPath);  //change directory
   $wordStr = getWordDocument($wordStr,"");
   if($wordStr == FILE_ERROR)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- print to file (.doc) -----
   $wordName = REPORT_NAME . ".doc";
   if(!file_put_contents("$reportPath/$wordName",$wordStr))   
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }

   //////////////////////////////////////////////////////////////////////////
   //---------------------------
   //----- 4. Insert to DB -----
   //---------------------------

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   //----- set escape -----
   // #001 added begin
   if (!get_magic_quotes_gpc()) 
   {
      $report_name = mysql_real_escape_string($report_name);      
   }
   // #001 added end
   //----- Insert to DB -----
   $str_insert = "
      insert into report (GUID, report_name, create_time, last_modified_time, fileFolder, fileName, nExtremeFile, 
         nHighFile, nMediumFile, nLowFile, nExtremeData, nHighData, nMediumData, nLowData, identity_type, range_begin, range_end, computer_numbers, owner_name)
      values('$guid', '$report_name', '$create_time', '$last_modified_time', '$fileFolder', '$fileName', $nExtremeFile,
         $nHighFile, $nMediumFile, $nLowFile, $nExtremeData, $nHighData, $nMediumData, $nLowData, '$identity_type', '$range_begin', '$range_end', $computer_numbers, '$login_name')";
   if(!mysqli_query($link, $str_insert))   //insert report failure
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
   //----- Query report information -----
   $str_query = "
      select reportID
      from report
      where fileFolder = '$fileFolder'
      ";
   if($result = mysqli_query($link, $str_query))   //query report information success
   {
      $row = mysqli_fetch_assoc($result);
      $reportID = $row["reportID"];
      mysqli_free_result($result);
      unset($row);
   }
   else   //query report information failure
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
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }

   //-----------------------------------------------------------------
   //----- 5. Fork child process to generate excel, and raw data ----- 
   //-----------------------------------------------------------------
   
   //----- generate excel -----
   $arr_execBackgroundJob["customer_name"] = $customer_name;
   $arr_execBackgroundJob["report_path"] = "$reportPath";
   $arr_execBackgroundJob["report_name"] = REPORT_NAME;
   $arr_execBackgroundJob["report_id"] = $reportID;
   $arr_execBackgroundJob["working_link"] = REPORT_LINK;
   
   if(file_exists(EXEC_BACKGROUND_JOB))
   {
      include_once(EXEC_BACKGROUND_JOB);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if(execBackgroundJob($arr_execBackgroundJob) < 0)
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return; 
   }

   //////////////////////////////////////////////////////////////////////////
   //----------------------------------------------------
   //----- 6. Create refresh page string and return -----
   // 2012/02/10 by Billy
   //----------------------------------------------------

   if(file_exists(REFRESH_REPORT_PATH))
   {
      include_once(REFRESH_REPORT_PATH);
   }
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;
      
      return;
   }
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   $refresh_report_str = refreshReportPages($link, $guid, $login_level, $login_name);
   if ($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   echo $refresh_report_str;

   return;
?>
