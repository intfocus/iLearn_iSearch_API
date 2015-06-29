<?php
/**********************************************
 *uploadEntryID.php
 *0. query by entryid, guid, host, domain and status = completed
 *   if found then return success
 *Query Entry table by GUID, entryID, hostname, domain_name
 *    and status = waiting or dropped.
 *1.if found 
 *    1.1 process the uploaded file.
 *    1.2 if status = dropped then update customer table, remain = remain - 1.
 *    1.3 update Entry table, status = completed and update upload_time.
 *    1.4 Retrun success
 *2.not found => delete the uploaded file.
 *
 *  2012/01/10 Jeffrey Chan
 *
 *  #001 20130408 modified by Odie
 *    1. Add the status in the table "entry":
 *       status 3 => parse fail (unlikely to happen now)
 *       status 4 => wait for parse (.enc is uploaded, and wait for xml2sql.pl to parse)
 *    2. Change the status from "completed" to "wait for parse" when upload is completed (completed -> wait for parse)
 *
 *  #002 20130902 modified by Odie
 *    Fix the bug for MAC-locked version
 *    => if status == DROPPED, and then the scan completed, remain will be decreased.
 *       This causes bug in MAC-locked version
 *  #003 20150209 modified by Odie
 *    Rewrite the debugger part using KLogger and move the log to /usrocal/www/apache22/logs
 *    It is too dangerous to write log in the data directory, which can be seen easily and may cause security problem.
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php");   //#002
   define(DELAY_SEC, 3);                                   //delay reply
   define(FILE_ERROR, -4);
   //----- Read connect information from DB.conf -----
   //#002, also include config function
   if(file_exists(FILE_NAME) && file_exists(CONFIGFUNCTION_PHP))
   {
      include(FILE_NAME);
      include(CONFIGFUNCTION_PHP);
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

   // #002 modify
   define(WAITING_UPLOAD, 0);

   define(COMPLETED, 1);
   define(DROPPED, 2);

   // #002 add
   define(PARSE_FAIL, 3);
   define(WAITING_PARSE, 4);
   
   // #003
   include_once("/usr/local/www/apache22/KLogger.php");
   $log = new KLogger("/usr/local/www/apache22/logs/uploadEntryID.log", KLogger::INFO);

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
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "upload"))
      {

         return SYMBOL_ERROR;
      }

      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   $cmd;               //get command from client
   $guid;              //get guid from client
   $entryid;           //get entryid from client
   $hostname;          //get hostname from client
   $domain_name;       //get domain from client
   $filename;          //get filepath from client
   $count;             //result count
   $status;            //status of result
   $link;              //connect to mysql
   $str_query;         //query command string
   $str_update;        //update command string
   $str_insert;        //insert command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array

   //----- set current time -----
   date_default_timezone_set(TIME_ZONE);
   $now_time = date("Y-m-d H:i:s");

   //----- log -----
   $log->LogInfo(json_encode($_POST));

   //----- Check receive information from client -----
   if(($cmd = check_command($_POST["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_POST["cmd"];
      $log->LogError("symbol error[1.00]");
      return;
   }
   if(($guid = check($_POST["GUID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_POST["GUID"];
      $log->LogError("symbol error[2.00]");
      return;
   }
   if(($entryid = check($_POST["EntryID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_POST["EntryID"];
      $log->LogError("symbol error[3.00]");
      return;
   }
   if(($hostname = check($_POST["hostname"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_POST["hostname"];
      $log->LogError("symbol error[4.00]");
      return;
   }
   if(($domain_name = check_domain($_POST["domain_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      echo "," . __LINE__ . "," . $_POST["domain_name"];
      $log->LogError("symbol error[5.00]");
      return;
   }
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB); 
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;
      echo "," . __LINE__;
      $log->LogError("db error[6.00]");
      return;
   }

   //--------------------------------------------------------------------------
   //----- 0. query by entryid, guid, host, domain and status = completed -----
   //         if found then return success
   //--------------------------------------------------------------------------
   
   // #001 modified, the last line of the SQL query
   //  from "status = completed" to "status = completed or waiting_parse"

   $str_query = "
      select status
      from entry
      where entryID = $entryid 
         and GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name' 
         and ( status = " . COMPLETED. " or status = ". WAITING_PARSE. " )"
         ;
   if($result = mysqli_query($link, $str_query))   //query success 
   {
      if(mysqli_num_rows($result) > 0)
      {
         mysqli_free_result($result);    //free useless result
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         echo UPLOAD_SUCCESS; 
         $log->LogInfo("Success, Already Upload[13.00]");
         return;
      }
   }
   else  //query failure
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;
      $log->LogError("db error[14.00]");
      return;
   }

   //------------------------------------------------------------------------------------
   //----- 1. query by entryid, guid, host, domain, and status = waiting or dropped -----
   //------------------------------------------------------------------------------------
   
   // #001 modified, the last line of SQL query
   // from "waiting" to "waiting_upload"
   
   $str_query = "
      select status,entryID 
      from entry 
      where GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name' 
         and ( status = " . WAITING_UPLOAD . " or status = " . DROPPED . " ) order by entryID DESC";
   $log->LogDebug($str_query);
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
      $entryid = $row["entryID"];
      mysqli_free_result($result);    //free useless result
      if($count > 0)   //have matching data
      {
         //-----------------------------------------
         //----- 1.1 process the uploaded file -----
         //-----------------------------------------
         
         $str_copy = $_FILES["filename"]["tmp_name"] . "," . WORKING_PATH . "/upload/" . $_FILES["filename"]["name"];
         $fileSize = filesize($_FILES["filename"]["tmp_name"]);
         if(copy($_FILES["filename"]["tmp_name"], WORKING_PATH . "/upload/" . $_FILES["filename"]["name"]))
         {
            unlink($_FILES["filename"]["tmp_name"]);
         } 
         else
         {
            sleep(DELAY_SEC);
            echo UPLOAD_FAILED;
            $log->LogError("upload failed[10.00], [$str_copy] fileSize[$fileSize bytes]");
            return;
         }

         //------------------------------------------------------
         //----- 1.2 status is dropped, remain = remain - 1 -----
         //------------------------------------------------------

         if($status == DROPPED)
         {
            // #002 begin, add the check of conf_mac
            // if conf_mac = 0, then remain - 1;
            // if conf_mac = 1, remain doesn't change
            $conf_mac = 0;
            if($conf_arr = get_all_config_name_and_value($link, $guid))
            {
               $conf_mac = (int)$conf_arr["MAC"];
            }
            else // cannot find the table conf, return DB_ERROR
            {
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               sleep(DELAY_SEC);
               echo DB_ERROR;

               return;
            }
            if($conf_mac == 0)   // if conf_mac == 0, remain = remain - 1
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
                  $log->LogError("db error[7.00]");
   
                  return;
               }
            }
            // #002 end
         }
         //-------------------------------------------------------------
         //----- 1.3 set status = wait for parse and update upload_time -----
         //-------------------------------------------------------------

         // #001 modified, the second line of SQL query
         // from "completed" to "waiting_parse"
         
         $str_update = "
            update entry 
            set status = " . WAITING_PARSE  . ", upload_time = '$now_time' 
            where entryID = $entryid";
         $log->LogDebug($str_update);
         if(!mysqli_query($link, $str_update))   //update entry status failure
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo DB_ERROR;
            $log->LogError("db error[8.00]");

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
         $log->LogInfo("upload success[9.00]");

         return;
      }
      else   //doesn't have matching data
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         //----- delete the upload file ----- 
         unlink($_FILES["filename"]["tmp_name"]);
         sleep(DELAY_SEC);
         echo NO_MATCHING_DATA;
         $log->LogError("no matching data[11.00]");

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
      $log->LogError("db error[12.00]");

      return;
   }
?>
