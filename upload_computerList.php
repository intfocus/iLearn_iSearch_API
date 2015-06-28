<?php
//////////////////////////////////
// 20130313, Phantom  
//    (1) Transform lower to upper 
//    (2) dash to underscore
//    (3) Beautify the result page
// 
// 20130430, Phantom+Odie
//    (1) Will insert/update department table
//    (2) If dept already exist => modify status=1
//    (3) If dept is not exist => Add to DB with status=1
// 
// 20130617, Odie, #003
//    (1) Fix Bug 35087, if the format of the list is incorrect (return false in ucs2_to_utf8),
//        do not replace the original list
//    (2) Add the choice of overwriting or merging with the original list when uploading a new list
//    (3) If merge, rewrite the computer list with all the computers in DB for download
//    (4) Rearrange the workflow as a whole transaction.
//        The modification in DB and the replace of the list will be applied only if everything works correctly.
//
// 20131031, Odie, #004
//    (1) Fix the terrible encodings of this file => both UTF-8 and BIG-5 exist in this file!
//        Convert all of the Chinese word to UTF-8
//    (2) Fix the bug that if the "department" column of the uploaded file is kept empty, then the field of
//        will also be empty, and cause error when generating EXCEL report
//        => If department is empty, then save it to "部門"
//    (3) Add the trim process for user's input information. Trim the begin and end space, tab of a string
//    (4) Fix the bug if the user click the upload button without choosing a file, a warning message of PHP 
//        function file_get_contents( ) will be printed on the result page
// 
// 20131031, Odie, #005
//    (1) Convert email address to lowercase when inserting it into DB (the modification is due to moeaidb
//        server lacks of the ability to send email with UPPER case email address) 
//////////////////////////////////


$GUID_DIR_PATH_PREFIX = '/usr/local/www/apache22/data/upload_old';
$CSV_FILE_NAME = 'list.csv';

define(DB_CONF,"/usr/local/www/apache22/DB.conf");
define(DELAY_SEC,3);

////////////////////////////////////////
// Read connect information from DB.conf
////////////////////////////////////////

if(file_exists(DB_CONF)){
   include_once(DB_CONF);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(DISPLAY_TEMPLATE, "/usr/local/www/apache22/data/upload_computerListResult.html");
}
else{
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

////////////////////////
// get guid from session
////////////////////////

session_start();
if(!session_is_registered("GUID")){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

if ($_SESSION["GUID"] == ""){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

$guid = $_SESSION["GUID"];

session_write_close();

///////////////////////////////////
// if dir doesn't exist mkdir first 
///////////////////////////////////

$guid_dir_path = "$GUID_DIR_PATH_PREFIX/$guid";
if(!file_exists($guid_dir_path)){
   //if(mkdir("$guid_dir_path",0777,true) == FALSE){
   if(system("mkdir -p -m 0774 $guid_dir_path")){
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}

// #003, check the process value
if(strcmp($_POST["process"], "overwrite") == 0){
   $process = 1;
}
else if(strcmp($_POST["process"], "merge") == 0){
   $process = 2;
}
else{
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

///////////////////////////////////
// copy upload csv file to guid dir
///////////////////////////////////

// #003, change the workflow
// (1) get the content of the file
// (2) convert it to utf8 and check the format(header)
// (3) if success, then update the DB
// (3) if success, then copy the file to destination (replace the origin)

$ucs2_content = @file_get_contents($_FILES["csvfile"]["tmp_name"]);
if($ucs2_content == FALSE){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

$utf8_body = ucs2_to_utf8($ucs2_content);

if($utf8_body == FALSE){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

//////////////////////////
// connect to mysql server
//////////////////////////

$link = @mysqli_connect(DB_HOST,ADMIN_ACCOUNT,ADMIN_PASSWORD,CONNECT_DB);
if(!$link){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

$start_cmd = "start transaction";
$rollback_cmd = "rollback";
$commit_cmd = "commit";

// start transaction
if(!mysqli_query($link, $start_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

/////////////////////////////////
// if overwrite, delete computeList table first
/////////////////////////////////

if($process == 1){
   $delete_cmd = "
      delete from computerList
      where GUID = '$guid'
   ";

   if(!mysqli_query($link, $delete_cmd)){
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}

////////////////////////////////////////////
// parse each line insert computerList table
// utf8_body 結尾的 \r\n 記得去掉
////////////////////////////////////////////
$lines = explode("\r\n",trim($utf8_body, "\r\n"));

foreach($lines as $line){

   list($domain_name,$hostname,$department,$employee_name,$employee_email) = explode("\t",$line);

   // #004 begin
   $domain_name = trim($domain_name);
   $hostname = trim($hostname);
   $department = trim($department);
   $employee_name = trim($employee_name);
   $employee_email = trim($employee_email);
   $employee_email = strtolower($employee_email);  //#005 add
   // #004 end

   //20130313, Phantom, transform domain_name and hostname, (1) toUpper (2) dash => underscore
   $domain_name = strtoupper($domain_name);
   $hostname = strtoupper($hostname);
   $hostname = str_replace("-","_",$hostname);

   $domain_name = mysqli_real_escape_string($link, $domain_name);
   $hostname = mysqli_real_escape_string($link, $hostname);
   $department = mysqli_real_escape_string($link, $department);
   $employee_name = mysqli_real_escape_string($link, $employee_name);
   $employee_email = mysqli_real_escape_string($link, $employee_email);

   if(strcmp($domain_name, "") == 0 || strcmp($hostname, "") == 0)
      continue;

   // #004 begin
   if(strcmp($department, "") == 0)
      $department = "部門";
   // #004 end

   $insert = false;
   
   // #003
   
   // if merge, check if this record is already in DB
   if($process == 2){
      $query_cmd = "
         select * from computerList 
         where GUID='$guid' and hostname='$hostname' and domain_name='$domain_name'
         ";
      if($result = mysqli_query($link, $query_cmd)){
         if(mysqli_num_rows($result) > 0){   // if in DB, check if it needs change
            $row = mysqli_fetch_assoc($result);
            $row_department = $row["department"];
            $row_employee_name = $row["employee_name"];
            $row_employee_email = $row["employee_email"];
            // if nothing to change, continue, else update DB
            if(strcmp($row_department, $department) != 0 || strcmp($row_employee_name, $employee_name) != 0 || strcmp($row_employee_email, $employee_email) != 0){
               $update_cmd = "
                  update computerList set department='$department',employee_name='$employee_name',employee_email='$employee_email'
                  where GUID='$guid' and hostname='$hostname' and domain_name='$domain_name'
                  ";
               if(!mysqli_query($link, $update_cmd)){
                  mysqli_query($link, $rollback_cmd);
                  mysqli_close($link);
                  sleep(DELAY_SEC);
                  displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
                  return;
               }           
            }
            else{
               continue;
            }
         }
         else{ // if not in DB, then insert it 
            $insert = true;
         }
      }  // end of if($result = mysqli_query($link, $query_cmd))
      else{
         mysqli_query($link, $rollback_cmd);
         mysqli_close($link);
         sleep(DELAY_SEC);
         displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
         return;
      }
   }  // end of if($process == 2)
   
   // if overwrite or this record is not in DB
   if($process == 1 || $insert == true){
      $insert_cmd = "
         INSERT INTO computerList(
            GUID,hostname,domain_name,department,employee_name,employee_email,status 
         )
         VALUES (
            '$guid','$hostname','$domain_name','$department','$employee_name','$employee_email','0'
         )
      ";

      if(!mysqli_query($link, $insert_cmd)){
         mysqli_query($link, $rollback_cmd);
         mysqli_close($link);
         sleep(DELAY_SEC);
         displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
         return;
      }
   }
}

/////////////////////////////
// replace the old file list
/////////////////////////////

if($process == 1){
   if(!copy($_FILES["csvfile"]["tmp_name"],"$guid_dir_path/$CSV_FILE_NAME")){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}
else if($process == 2){
   $tmp_file_name = tempnam($guid_dir_path, "tmp");
   $fp = fopen($tmp_file_name, "wb");
   if(!$fp){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
   // write the header of the list
   $UCS2_BOM = "\xff\xfe";
   $UTF8_TITLE = "網域名稱\t電腦名稱\t所屬部門\t使用者姓名\te-mail\r\n";

   $UCS2_TITLE = iconv('UTF-8','UCS-2LE',$UTF8_TITLE);
   $UCS2_HEADER = $UCS2_BOM. $UCS2_TITLE;
   fwrite($fp, $UCS2_HEADER);

   $query_cmd = "
      select * from computerList where GUID='$guid'
      ";
   if($result = mysqli_query($link, $query_cmd)){
      while($row = mysqli_fetch_assoc($result)){
         $domain_name = $row["domain_name"];
         $hostname = $row["hostname"];
         $department = $row["department"];
         $employee_name = $row["employee_name"];
         $employee_email = $row["employee_email"];
         $record = "$domain_name\t$hostname\t$department\t$employee_name\t$employee_email\r\n";
         $UCS2_record = iconv('UTF-8','UCS-2LE',$record);
         fwrite($fp, $UCS2_record);
      }
   }
   else{
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      unlink($tmp_file_name);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
   if($fp){
      fclose($fp);
      $fp = 0;
   }
   if(!rename($tmp_file_name, "$guid_dir_path/$CSV_FILE_NAME")){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      unlink($tmp_file_name);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}

mysqli_query($link, $commit_cmd);

//////////////////////////////
// update department table (bitwise flag)
// flag=1 means manually add
// flag=2 means added by computer list
// flag=1+2=3 means manually first, then added by computer list later.
//
// (1) Delete all the dept with the same GUID and flag=2
// (2) Update all the dept set flag=1 where flag=3
// (3) Update department set flag=3 if there is same dept_name with new computer list dept_name
// (4) Insert department with flag=2 with new computer list dept_name
/////////////////////////////

////////////////////////////////////////////////////////////
// (1) Delete all the dept with the same GUID and flag=2
////////////////////////////////////////////////////////////
$update_cmd = "delete from department where GUID='$guid' and flag=2";

if(!mysqli_query($link, $update_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

////////////////////////////////////////////////////////////
// (2) Update all the dept set flag=1 where flag=3
////////////////////////////////////////////////////////////
$update_cmd = "update department set flag=1 where GUID='$guid' and flag=3"; 

if(!mysqli_query($link, $update_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

////////////////////////////////////////////////////////////
// (3) Update department set flag=3 if there is same dept_name with new computer list dept_name
////////////////////////////////////////////////////////////
$update_cmd = "
   update department set flag=3 where GUID='$guid'
      and dep_name in (select distinct department from computerList where GUID='$guid') 
";

if(!mysqli_query($link, $update_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

////////////////////////////////////////////////////////////
// (4) Insert department with flag=2 with new computer list dept_name
////////////////////////////////////////////////////////////
$update_cmd = "
   Insert into department (GUID,dep_name,flag) 
      select distinct GUID,department,2 from computerList C 
         where GUID='$guid' and not exists (select * from department D where GUID='$guid' and D.dep_name=C.department)
";

if(!mysqli_query($link, $update_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

/////////////////////////////
// update identityFound table
/////////////////////////////

$update_cmd = "
   update identityFound,computerList
   set identityFound.department = computerList.department,
   identityFound.employee_name = computerList.employee_name,
   identityFound.employee_email = computerList.employee_email
   where identityFound.hostname = computerList.hostname AND 
   identityFound.domain_name = computerList.domain_name AND
   identityFound.GUID = '$guid'
";

if(!mysqli_query($link, $update_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

if($link){
   mysqli_close($link);
}

displayResultPage("上傳成功並已更新資料");

// 20130313, Phantom add (to beautify the result page)
function displayResultPage($display_content){
   if(file_exists(DISPLAY_TEMPLATE))
   {
      if(!@($str_display_content = file_get_contents(DISPLAY_TEMPLATE)))
      {
         sleep(DELAY_SEC);
         echo __LINE__;

         return;
      }
      $str_display_content = str_replace("\$\$content\$\$", $display_content, $str_display_content);
      echo $str_display_content;
   }
   else
   {
      echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
      echo "</head><body>";
      echo $display_content;
      echo "</body></html>";
   }
}

///////////////////////////////////////////////////////////////////////////
// 1. $content 必須是 ucs2 little 編碼且前面有加 2Bytes BOM => FF FE
// 2. return 轉成 utf-8 後的 content body
///////////////////////////////////////////////////////////////////////////
function ucs2_to_utf8($ucs2_content){

   $UCS2_BOM = "\xff\xfe";
   $UTF8_TITLE = "網域名稱\t電腦名稱\t所屬部門\t使用者姓名\te-mail\r\n";

   $UCS2_TITLE = iconv('UTF-8','UCS-2LE',$UTF8_TITLE);
   if($UCS2_TITLE == FALSE){
      return FALSE;
   }

   /////////////////////////////////////
   //  比對 ucs2_content head 有沒有正確
   /////////////////////////////////////

   $head_len = strlen($UCS2_BOM) + strlen($UCS2_TITLE);
   $head = substr($ucs2_content,0,$head_len);
   if($head != "$UCS2_BOM$UCS2_TITLE"){
      return FALSE;
   }

   /////////////////////
   // 將 ucs2 轉成 utf-8
   /////////////////////
   $ucs2_body = substr($ucs2_content,$head_len);

   $utf8_body = iconv('UCS-2LE','UTF-8',$ucs2_body);
   if($utf8_body == FALSE){
      return FALSE;
   }

   return $utf8_body;

}

?>
