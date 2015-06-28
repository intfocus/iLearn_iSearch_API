<?php
/**********************************************
 *uploadAction.php
 *0. query identityFound by entryid, guid, host, domain 
 *   if not found, delete the uploaded file and return failed 
 *1. if found 
 *    1.1 get $xmlid and XMLFilePath ==> 20xxxx/xmlid-GUID.xml, retrive the first 6 chars => $yyyymm
 *    1.2 construct path ==> /usr/local/www/apache22/data/upload_old/$guid/$yyyymm/$xmlid.action
 *    1.3 move the uploaded file to the path of 1.2 
 *    1.4 Retrun success
 *
 *  2013/01/22 Phantom + Odie 
 *
 *  2013/08/09 #001 Phantom+Odie,   如果 entryID=100 掃到一半關機, 然後過了 weekend 繼續掃描, 因為 entryID=100 已經被判斷成 expired, 會直接拿到 entryID=102.
 *                                  最後的上傳結果會把最大的 entryID(102) 變成 status=1 or status=4, identityFound 裡面記錄的也是 entryID=102 上傳成功.
 *                                  但是 XML 裡面的 entryID 還是 100, PMarkerReader 打開 XML 永遠會拿 entryID=100 來詢問
 *                                  因為假設 PMarkerReader.exe 永遠只會上傳最新掃描的 action file, 所以可以直接找最大的那筆 entryID in identityFound table
 *  2015/02/09 #002 Odie            It is too dangerous to write log in the data directory, which can be seen easily and may cause security problem.
 *                                  Rewrite the debugger part using KLogger and move the log to /usr/local/www/apache22/logs
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

   // # 002
   include_once("/usr/local/www/apache22/KLogger.php");
   $log = new KLogger("/usr/local/www/apache22/logs/uploadAction.log", KLogger::INFO);

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
   $filename;          //get filepath from client
   $link;              //connect to mysql
   $str_query;         //query command string
   $str_update;        //update command string
   $str_insert;        //insert command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array

   //----- log -----
   date_default_timezone_set(TIME_ZONE);
   $log->LogInfo(json_encode($_POST));
   
   //----- Check receive information from client -----
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
      $log->LogError("symbol error[6.00]");
      return;
   }

   //--------------------------------------------------------------------------
   //----- 0. query identityFound by entryid, guid, host, domain 
   //         if not found, delete the uploaded file and return failed 
   //--------------------------------------------------------------------------

   /* #001 comment out
   $str_query = "
      select XMLID,XMLFilePath 
      from identityFound 
      where entryID = $entryid 
         and GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name'";
   */
   //#001, 直接找最大的那筆 XMLID, 不再管 entryID 到底是 100 or 102.
   $str_query = "
      select XMLID,XMLFilePath 
      from identityFound 
      where GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name'
         and entryID >= $entryid
      order by XMLID DESC";
   if($result = mysqli_query($link, $str_query))   //query success 
   {
      if(mysqli_num_rows($result) > 0)
      {
         //-----------------------------
         // 1. If found 
         //    1.1 get $xml_id and XMLFilePath ==> 20xxxx/xmlid-GUID.xml, retrive the first 6 chars => $yyyymm
         //-----------------------------
         $row = mysqli_fetch_assoc($result);
         $xml_id = $row["XMLID"];
         $xml_file_path = $row["XMLFilePath"];
         $yyyymm = substr($xml_file_path,0,6);
         
         mysqli_free_result($result);    //free useless result
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }

         //-----------------------------
         //    1.2 construct path ==> /usr/local/www/apache22/data/upload_old/$guid/$yyyymm/$xml_id.action
         //-----------------------------
         $dest = "/upload_old/$guid/$yyyymm/$xml_id.action";

         //-----------------------------
         //    1.3 move the uploaded file to the path of 1.2
         //-----------------------------
         $str_copy = $_FILES["filename"]["tmp_name"] . "," . WORKING_PATH . $dest;
         $fileSize = filesize($_FILES["filename"]["tmp_name"]);
         if(copy($_FILES["filename"]["tmp_name"], WORKING_PATH . $dest))
         {
            unlink($_FILES["filename"]["tmp_name"]);

            $log->LogInfo("Action file already upload[entryID=$entryid,XMLID=$xml_id]");

            //-----------------------
            //    1.4 return Success
            //-----------------------
            echo UPLOAD_SUCCESS;

            return;
         } 
         else
         {
            unlink($_FILES["filename"]["tmp_name"]);
            sleep(DELAY_SEC);
            echo UPLOAD_FAILED;
            $log->LogError("upload failed[10.00], [$str_copy] fileSize[$fileSize bytes]");

            return;
         }
      }
      else
      {
         unlink($_FILES["filename"]["tmp_name"]);
         mysqli_free_result($result);    //free useless result
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         sleep(DELAY_SEC);
         echo DB_ERROR;
         $log->LogError("One of entryid, guid, hostname, domain_name is not found in identityFound");

         return;
      }
   }
   else  //query failure
   {
      unlink($_FILES["filename"]["tmp_name"]);
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo NO_MATCHING_DATA;
      $log->LogError("db error[14.00]");

      return;
   }
?>
