<?php
/**********************************************
 *finishScan.php
 * 1. query by entryid, guid, host, domain, and status = waiting or dropped
 *    1.1 if status = dropped then update customer table, remain = remain - 1.
 *    1.2 update Entry table, status = completed and update upload_time.
 *    1.3 update or insert identityFound (2013/01/03 modified, for status query should be 完成, not 掃描中
 *    1.4 Retrun success
 *
 * 2. If no macthing data, logged.
 *
 *  2012/12/25 for 工業局客製化, By Phantom
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(DELAY_SEC, 3);                                   //delay reply
   define(FILE_ERROR, -4);
   //----- Read connect information from DB.conf -----
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
   define(TIME_ZONE, "Asia/Taipei");
   define(ILLEGAL_CHAR, "'-;<>");                          //illegal char
   define(STR_LENGTH, 64);
   define(WORKING_PATH, $working_path);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(UPLOAD_SUCCESS, 0);
   define(DB_ERROR, -1);
   define(EMPTY_REMAIN, -2);
   define(SYMBOL_ERROR, -3);
   define(UPLOAD_FAILED, -5);
   define(NO_MATCHING_DATA, -6);
   //status
   define(DELETED, -1);
   define(WAITING, 0);
   define(COMPLETED, 1);
   define(DROPPED, 2);

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
   //----- Check domain -----
   function check_domain($check_str)
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

      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   $guid;              //get guid from client
   $entryid;           //get entryid from client
   $hostname;          //get hostname from client
   $domain_name;       //get domain from client
   $ip;                //get ip from client
   $mac;               //get mac from client
   $status;            //status of result
   $link;              //connect to mysql
   $str_query;         //query command string
   $str_update;        //update command string
   $str_insert;        //insert command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array

   //----- log -----
   date_default_timezone_set(TIME_ZONE);
   $now_time = date("Y-m-d H:i:s");
   $logFile = fopen("upload.log", "a");
   $logData = "[$now_time] (no upload result) guid = [" . $_GET["GUID"] . "]EntryID = [" . $_GET["EntryID"] .
      "]hostname = [" . $_GET["hostname"] . "]domain_name = [" . $_GET["domain_name"] . "]";
   
   //----- Check receive information from client -----
   if(($guid = check($_GET["GUID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_GET["GUID"];
      $logData = $logData . "symbol error[" . __LINE__ . "][$guid]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }
   if(($entryid = check($_GET["EntryID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_GET["EntryID"];
      $logData = $logData . "symbol error[" . __LINE__ . "]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }
   if(($hostname = check($_GET["hostname"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_GET["hostname"];
      $logData = $logData . "symbol error[" . __LINE__ . "]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }
   if(($domain_name = check_domain($_GET["domain_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_GET["domain_name"];
      $logData = $logData . "symbol error[" . __LINE__ . "]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB); 
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;
      echo "," . __LINE__;
      $logData = $logData . "db error[" . __LINE__ . "]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }

   //------------------------------------------------------------------------------------
   //----- 1. query by entryid, guid, host, domain, and status = waiting or dropped -----
   //------------------------------------------------------------------------------------

   $str_query = "
      select status,entryID 
      from entry 
      where GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name' 
         and ( status = " . WAITING . " or status = " . DROPPED . " ) order by entryID DESC";
   if($result = mysqli_query($link, $str_query))   //query success
   {
      $count = mysqli_num_rows($result);
      $row = mysqli_fetch_assoc($result);
      $status = $row["status"];
      /////////////////////////////////////////////
      // for 經濟部奇怪 bug, entryID 有舊的(七月份) 跟 新的(十二月份), 7月份的有掃描沒上傳, 12月份重新派送之後直接拿舊資料上傳, 結果改到前面的 entryID 結果 
      // 只好用鋸箭法, 不論回傳的是哪個 entryID, 一律只更新最新的 (waiting or dropped) entryID.
      //
      // 2012/12/26 By Phantom+Jeffrey
      /////////////////////////////////////////////
      if($count > 0)
         $entryid = $row["entryID"];
      mysqli_free_result($result);    //free useless result
      if($count > 0)   //have matching data
      {
         //------------------------------------------------------
         //----- 1.1 status is dropped, remain = remain - 1 -----
         //------------------------------------------------------

         if($status == DROPPED)
         {
            $str_update = "
               update customer 
               set remain = remain - 1 
               where GUID = '$guid'";
            if(!mysqli_query($link, $str_update))   //update remain failure
            {
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               sleep(DELAY_SEC);
               echo DB_ERROR;
               $logData = $logData . "db error[" .  __LINE__ . "]\n";
               fwrite($logFile, $logData);
               fclose($logFile);

               return;
            }
         }
         
         //-------------------------------------------------------------
         //----- 1.2 set status = completed and update upload_time -----
         //-------------------------------------------------------------
         
         $str_update = "
            update entry 
            set status = " . COMPLETED  . ", upload_time = '$now_time' 
            where entryID = $entryid"; 
         if(!mysqli_query($link, $str_update))   //update entry status failure
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo DB_ERROR;
            $logData = $logData . "db error[" . __LINE__ . "]\n";
            fwrite($logFile, $logData);
            fclose($logFile);

            return;
         }

         //------------------------------
         //----- 1.3 update or insert identityFound (2013/01/03 modified, for status query should be 完成, not 掃描中
         //------------------------------
         $ip = $_GET["ip"];
         $loginInfo = $_GET["loginInfo"]; 
         $department = $_GET["department"];
         $mac = $_GET["mac"];
         $employeeEmail = $_GET["employeeEmail"];
         $employeeName = $_GET["employeeName"];

         $str_insert = "
            Insert identityFound (EntryID,create_time,GUID,IP,hostname,domain_name,login_name,department,mac_address,status,
               nFile,nFound,count0,count1,count2,count3,count4,count5,count6,count7,employee_email,employee_name,start_time,end_time,total_file) 
            VALUES($entryid,'$now_time','$guid','$ip','$hostname','$domain_name','$loginInfo','$department','$mac',0,
               0,0,0,0,0,0,0,0,0,0,'$employeeEmail','$employeeName','$now_time','$now_time',0)";        

         if(!mysqli_query($link, $str_insert))   //Insert identityFound failure
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo DB_ERROR;
            $logData = $logData . "Insert failed " . __LINE__ . "[$str_insert]\n";
            fwrite($logFile, $logData);
            fclose($logFile);

            return;
         }
         
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }

         //------------------------------
         //----- 1.4 Return success -----
         //------------------------------

         echo UPLOAD_SUCCESS; 
         $logData = $logData . "upload success[" . __LINE__ . "]\n";
         fwrite($logFile, $logData);
         fclose($logFile);

         return;
      }
      else   //doesn't have matching data
      {
         //------------------------------
         //----- 2. No Matching data, logged. 
         //------------------------------
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         //----- delete the upload file ----- 
         unlink($_FILES["filename"]["tmp_name"]);
         sleep(DELAY_SEC);
         echo NO_MATCHING_DATA;
         $logData = $logData . "no matching data[" . __LINE__ . "]\n";
         fwrite($logFile, $logData);
         fclose($logFile);

         return;
      }
   }
   else   //query failure
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;
      $logData = $logData . "db error[" . __LINE__ . "]\n";
      fwrite($logFile, $logData);
      fclose($logFile);

      return;
   }
?>
