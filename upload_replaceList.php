<?php
   //////////////////////////////////
   // 2013/11/29, created by Odie, modified from upload_computerList.php
   // This file is customized for Taisugar
   //    1. parse the uploaded csv file
   //    2. replace "hostname", "domain_name" and "employee_name" in three tables "entry", "identityFound" and "computerList" 
   //
   //////////////////////////////////

   $GUID_DIR_PATH_PREFIX = '/usr/local/www/apache22/data/upload_old';
   $CSV_FILE_NAME = 'taisugar.csv';

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


   ////////////////////////////////////////////
   // parse each line and put them in an array
   // utf8_body 結尾的 \r\n 記得去掉
   ////////////////////////////////////////////

   $arr_replace = array();
   $lines = explode("\r\n",$utf8_body);
   $count = 0;

   foreach($lines as $line){

      list($old_employee_name,$old_domain_name,$old_hostname,$new_employee_name,$new_domain_name,$new_hostname) = explode("\t",$line);

      $old_employee_name = trim($old_employee_name);
      $old_domain_name = trim($old_domain_name);
      $old_hostname = trim($old_hostname);
      $new_employee_name = trim($new_employee_name);
      $new_domain_name = trim($new_domain_name);
      $new_hostname = trim($new_hostname);

      $old_domain_name = strtoupper($old_domain_name);
      //$old_hostname = strtoupper($old_hostname);
      $new_domain_name = strtoupper($new_domain_name);
      //$new_hostname = strtoupper($new_hostname);
      $old_hostname = str_replace("-","_",$old_hostname);
      $new_hostname = str_replace("-","_",$new_hostname);

      if(strcmp($old_domain_name, "") == 0 || strcmp($old_hostname, "") == 0)
         continue;

      $count++;
      $temp_name = $guid. "_". $count;
      $arr_replace[$temp_name] = array();
      $arr_replace[$temp_name]["old_employee_name"] = $old_employee_name;
      $arr_replace[$temp_name]["old_domain_name"] = $old_domain_name;
      $arr_replace[$temp_name]["old_hostname"] = $old_hostname;
      $arr_replace[$temp_name]["new_employee_name"] = $new_employee_name;
      $arr_replace[$temp_name]["new_domain_name"] = $new_domain_name;
      $arr_replace[$temp_name]["new_hostname"] = $new_hostname;
   }
   
   // start transaction
   if(!mysqli_query($link, $start_cmd)){
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }

   $ret = update_db($link,$arr_replace,"entry",$guid);
   if($ret < 0){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:" . $ret . ")");
      return;
   }

   $ret = update_db($link,$arr_replace,"identityFound",$guid);
   if($ret < 0){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:" . $ret . ")");
      return;
   }
   
   $ret = update_db($link,$arr_replace,"computerList",$guid);
   if($ret < 0){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:" . $ret . ")");
      return;
   }

   $ret = update_db_macAddress($link, $arr_replace, "macAddress", $guid);
   if($ret < 0){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:" . $ret . ")");
      return;
   }

   /////////////////////////////
   // replace the old file list
   /////////////////////////////

   if(!copy($_FILES["csvfile"]["tmp_name"],"$guid_dir_path/$CSV_FILE_NAME")){
      mysqli_query($link, $rollback_cmd);
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
   //   mysqli_query($link, $rollback_cmd);
   
   mysqli_query($link, $commit_cmd);
   if($link){
      mysqli_close($link);
   }
   displayResultPage("上傳成功並已更新資料");
   return;
   
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
         $str_display_content = str_replace("資產清單上傳", "電腦清單替換", $str_display_content);
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
      $UTF8_TITLE = "異動前使用者姓名\t異動前網域名稱\t異動前電腦名稱\t異動後使用者姓名\t異動後網域名稱\t異動後電腦名稱\r\n";

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

   function update_db($link, $arr_replace, $table_name, $guid){

      if(!$link){
         return -__LINE__;
      }

      if(strcmp($table_name,"entry")!=0 && strcmp($table_name,"identityFound")!=0 && strcmp($table_name,"computerList")!=0){
         return -__LINE__;
      }

      ///////////////////////////////////////////
      // 1. Change the current domain_name and hostname into a temp name 
      //    (This is step is to avoid the cycle as following:
      //     A=>B and B=>C, this will make A to C, which is incorrect.
      //     A=>temp1 and B=>temp2, then temp1=>B and temp2=>C
      ///////////////////////////////////////////
      foreach($arr_replace as $temp_name => $arr_value){

         $old_hostname = mysql_real_escape_string($arr_value["old_hostname"]);
         $old_domain_name = mysql_real_escape_string($arr_value["old_domain_name"]);
         $update_cmd = "
            update $table_name set hostname='$temp_name'
            where GUID='$guid' and hostname='$old_hostname' and domain_name='$old_domain_name'";
         if(!mysqli_query($link, $update_cmd)){
            return -__LINE__;
         }           
      }

      ///////////////////////////////////////////
      // 2. Change the temp hostname into new hostname and domain_name 
      ///////////////////////////////////////////
      foreach($arr_replace as $temp_name => $arr_value){

         $old_hostname = mysql_real_escape_string($arr_value["old_hostname"]);
         $old_domain_name = mysql_real_escape_string($arr_value["old_domain_name"]);
         $new_hostname = mysql_real_escape_string($arr_value["new_hostname"]);
         $new_domain_name = mysql_real_escape_string($arr_value["new_domain_name"]);
         $new_employee_name = $arr_value["new_employee_name"]; // need to do strcmp(), so escape it later

         ///////////////////////////////////////////
         // 2.1. If new_employee_name == 作廢 or 調職,
         //      i.  employee_name = 作廢 or 調職
         //      ii. hostname, domain_name not changed
         ///////////////////////////////////////////
         if(strcmp($new_employee_name,"作廢")==0 || strcmp($new_employee_name,"調職")==0){
            $new_employee_name = mysql_real_escape_string($new_employee_name);
            $update_cmd = "
               update $table_name set employee_name='$new_employee_name',hostname='$old_hostname'
               where GUID='$guid' and hostname='$temp_name'";
         
            if(!mysqli_query($link, $update_cmd)){
               return -__LINE__;
            }           
         }
         else{
            ///////////////////////////////////////////
            // 2.2. If new_employee_name is neither 作廢 nor 調職, and either new_hostname or new_domain_name is empty
            //      => employee_name, hostname, domain_name are not changed
            ///////////////////////////////////////////
            if(strcmp($new_hostname, "")==0 || strcmp($new_domain_name, "")==0){
               $update_cmd = "
                  update $table_name set hostname='$old_hostname'
                  where GUID='$guid' and hostname='$temp_name'";
         
               if(!mysqli_query($link, $update_cmd)){
                  return -__LINE__;
               }           
            }
            ///////////////////////////////////////////
            // 2.3. If new_employee_name is neither 作廢 nor 調職, and neither new_hostname nor new_domain_name is empty
            //      i.  employee_name = new_employee_name (if new_employee_name is empty, the employee_name is not changed)
            //      ii. hostname = new_hostname
            //      iii.domain_name = new_domain_name
            ///////////////////////////////////////////
            else{
               if(strcmp($new_employee_name, "")==0){
                  $update_cmd = "
                     update $table_name set hostname='$new_hostname',domain_name='$new_domain_name'
                     where GUID='$guid' and hostname='$temp_name'";
               }
               else{
                  $new_employee_name = mysql_real_escape_string($new_employee_name);
                  $update_cmd = "
                     update $table_name set hostname='$new_hostname',domain_name='$new_domain_name',employee_name='$new_employee_name'
                     where GUID='$guid' and hostname='$temp_name'";
               }
              
               if(!mysqli_query($link, $update_cmd)){
                  return -__LINE__;
               }
            }
         }
      }  // end of foreach($arr_replace as $temp_name => $arr_value){

      return 0;
 
   }  // end of function update_db()
   
   function update_db_macAddress($link, $arr_replace, $table_name, $guid){

      if(!$link){
         return -__LINE__;
      }

      if(strcmp($table_name,"macAddress")!=0){
         return -__LINE__;
      }

      ///////////////////////////////////////////
      // 1. Change the current domain_name and hostname into a temp name 
      //    (This is step is to avoid the cycle as following:
      //     A=>B and B=>C, this will make A to C, which is incorrect.
      //     A=>temp1 and B=>temp2, then temp1=>B and temp2=>C
      ///////////////////////////////////////////
      foreach($arr_replace as $temp_name => $arr_value){

         $old_hostname = mysql_real_escape_string($arr_value["old_hostname"]);
         $old_domain_name = mysql_real_escape_string($arr_value["old_domain_name"]);
         $update_cmd = "
            update $table_name set hostname='$temp_name'
            where GUID='$guid' and hostname='$old_hostname' and domain_name='$old_domain_name'";
         if(!mysqli_query($link, $update_cmd)){
            return -__LINE__;
         }           
      }

      ///////////////////////////////////////////
      // 2. Change the temp hostname into new hostname and domain_name 
      ///////////////////////////////////////////
      foreach($arr_replace as $temp_name => $arr_value){

         $old_hostname = mysql_real_escape_string($arr_value["old_hostname"]);
         $old_domain_name = mysql_real_escape_string($arr_value["old_domain_name"]);
         $new_hostname = mysql_real_escape_string($arr_value["new_hostname"]);
         $new_domain_name = mysql_real_escape_string($arr_value["new_domain_name"]);
         $new_employee_name = $arr_value["new_employee_name"]; // need to do strcmp(), so escape it later

         ///////////////////////////////////////////
         // 2.1. If new_employee_name == 作廢 or 調職,
         //      i.  employee_name = 作廢 or 調職
         //      ii. hostname, domain_name not changed
         ///////////////////////////////////////////
         if(strcmp($new_employee_name,"作廢")==0 || strcmp($new_employee_name,"調職")==0){
            $new_employee_name = mysql_real_escape_string($new_employee_name);
            $update_cmd = "
               update $table_name set hostname='$old_hostname'
               where GUID='$guid' and hostname='$temp_name'";
         
            if(!mysqli_query($link, $update_cmd)){
               return -__LINE__;
            }           
         }
         else{
            ///////////////////////////////////////////
            // 2.2. If new_employee_name is neither 作廢 nor 調職, and either new_hostname or new_domain_name is empty
            //      => employee_name, hostname, domain_name are not changed
            ///////////////////////////////////////////
            if(strcmp($new_hostname, "")==0 || strcmp($new_domain_name, "")==0){
               $update_cmd = "
                  update $table_name set hostname='$old_hostname'
                  where GUID='$guid' and hostname='$temp_name'";
         
               if(!mysqli_query($link, $update_cmd)){
                  return -__LINE__;
               }           
            }
            ///////////////////////////////////////////
            // 2.3. If new_employee_name is neither 作廢 nor 調職, and neither new_hostname nor new_domain_name is empty
            //      i.  employee_name = new_employee_name (if new_employee_name is empty, the employee_name is not changed)
            //      ii. hostname = new_hostname
            //      iii.domain_name = new_domain_name
            ///////////////////////////////////////////
            else{
               if(strcmp($new_employee_name, "")==0){
                  $update_cmd = "
                     update $table_name set hostname='$new_hostname',domain_name='$new_domain_name'
                     where GUID='$guid' and hostname='$temp_name'";
               }
               else{
                  $new_employee_name = mysql_real_escape_string($new_employee_name);
                  $update_cmd = "
                     update $table_name set hostname='$new_hostname',domain_name='$new_domain_name'
                     where GUID='$guid' and hostname='$temp_name'";
               }
              
               if(!mysqli_query($link, $update_cmd)){
                  return -__LINE__;
               }
            }
         }
      }  // end of foreach($arr_replace as $temp_name => $arr_value){

      return 0;
 
   }  // end of function update_db()
      
?>
