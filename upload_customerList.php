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
//
// #000  20140917    Phantom              File created
//    模仿 upload_computerList.php 寫成 upload_customerList.php 做安泰分行 excel 上傳管理
//
// #001 modified by Odie   2014/10/08
//      1. 若安泰conf存在，新增之前先找出System Admin remain還有多少，如果夠才新增，而且會從Admin remain扣除相對應的次數。
//         若安泰conf不存在，就直接新增。
//
// #002 modified by Odie   2014/11/18
//      1. #001中，新增分行必須填寫剩餘次數，並且由 Admin 的次數扣除，但因實際操作會有一些問題，所以拿掉這個限制
//      2. 修改邏輯上的錯誤
//      3. 在 user input 加上 mysqli_real_escape_string()
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
   define(DISPLAY_TEMPLATE, "/usr/local/www/apache22/data/upload_customerListResult.html");
   require_once('/usr/local/www/apache22/web/guid.php');
}
else{
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

//////////////////////////////
// #001 Check 安泰銀行 conf 檔案是否存在 
//////////////////////////////

define(ANTIE_FILE_NAME, "/usr/local/www/apache22/entie.conf");
if (file_exists(ANTIE_FILE_NAME))
   $entieFlag = 1;
else
   $entieFlag = 0;

////////////////////////
// get guid from session
////////////////////////

session_start();
if(!session_is_registered("GUID") || !session_is_registered("GUID_ADM")){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

if ($_SESSION["GUID"] == "" || $_SESSION["GUID_ADM"] == ""){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

$GUID_ADM = $_SESSION["GUID_ADM"];

session_write_close();

///////////////////////////////////
// if dir doesn't exist mkdir first 
///////////////////////////////////

$guid_dir_path = "$GUID_DIR_PATH_PREFIX/$GUID_ADM";
if(!file_exists($guid_dir_path)){
   //if(mkdir("$guid_dir_path",0777,true) == FALSE){
   if(system("mkdir -p -m 0774 $guid_dir_path")){
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}

///////////////////////////////////
// copy upload csv file to guid dir
///////////////////////////////////

$ucs2_content = @file_get_contents($_FILES["csvfile"]["tmp_name"]);
if($ucs2_content == FALSE){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
   return;
}

$utf8_body = ucs2_to_utf8($ucs2_content);

if($utf8_body == FALSE){
   sleep(DELAY_SEC);
   displayResultPage("上傳失敗(error:-" . __LINE__ . ")，檔案編碼錯誤");
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

////////////////////////////////////////////
// parse each line, insert/modify customer table 
// utf8_body 結尾的 \r\n 記得去掉
////////////////////////////////////////////
$lines = explode("\r\n",trim($utf8_body, "\r\n"));   // #002 ，只拿結尾"\r\n"
$resultMsg = "";

foreach($lines as $line){

   list($name,$login_name,$password,$validcode,$contact_email) = explode("\t",$line);

   $name = trim($name);
   $login_name = trim($login_name);
   $password = trim($password);
   $validcode = trim($validcode);
   $contact_email = strtolower(trim($contact_email));

   ///////////////
   // Validation
   ///////////////
   if ($name == "")
   {
      $resultMsg = $resultMsg . "分行名稱錯誤，不能為空白<br/>";
      continue;
   }

   $len = strlen($login_name);
   if ($len < 3 || $len > 31 || ereg("^[A-Za-z0-9_\.]+$", $login_name) == false)     // #002，放寬帳號 regular expression 的限制
   {
      //$resultMsg = $resultMsg . "$name -- 分行管理者名稱[$login_name]格式錯誤，必須為 3-31 位英文字母<br/>";
      $resultMsg = $resultMsg . "$name -- 分行管理者名稱[$login_name]格式錯誤，必須為3~31個字元，接受英文、數字、符號_及符號. < br/>";
      continue;
   }

   $len = strlen($password);
   if ($len < 8 || $len > 30)
   {
      $resultMsg = $resultMsg . "$name -- 分行管理者密碼[$password]格式錯誤，長度必須為 8-30<br/>";
      continue;
   }
   $password = hash('md5', $password);

   $len = strlen($validcode);
   if ($len < 6 || $len > 12 || strpbrk($validcode, "-'"))
   {
      $resultMsg = $resultMsg . "$name -- 用戶端盤點密碼[$validcode]格式錯誤，長度必須為 6-12，且不能有 - 以及 '<br/>";
      continue;
   }

   if (ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $contact_email) == false)
   {
      $resultMsg = $resultMsg . "$name -- 分行管理者Email[$contact_email]格式錯誤<br/>";
      continue;
   }
   
   $name = mysqli_real_escape_string($link, $name);
   $login_name = mysqli_real_escape_string($link, $login_name);
   $password = mysqli_real_escape_string($link, $password);
   $validcode = mysqli_real_escape_string($link, $validcode);
   $contact_email = mysqli_real_escape_string($link, $contact_email);

   // 這裡是用分行名稱作為識別，而非login_name
   $sql = "select * from customer where name='$name'";
   if ($result = mysqli_query($link, $sql))
   {
      $row = mysqli_num_rows($result);
      if ($row == 0) //不存在, insert new
      {
         // Generate GUID
         $guidclass = new Guid($login_name,"127.0.0.1");
         $GUID = $guidclass->toString();

         // Insert customer
         $sql = "Insert customer (GUID,name,login_name,password,validcode,contact_email,status)
                 VALUES('$GUID','$name','$login_name','$password','$validcode','$contact_email',1)";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊新增失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }

         // Insert riskCategory
         $sql = "Insert into riskCategory (GUID,low,high,extreme,extreme_type_num,extreme_type) VALUES('$GUID',5,20,20,2,'0,1,2,3,4,5,6')";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊新增失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }

         // Update riskCategory (copy setting from GUID_ADM)
         $sql = "Update riskCategory t1, riskCategory t2
                  set t1.low=t2.low, t1.high=t2.high, t1.extreme=t2.extreme, t1.extreme_type_num=t2.extreme_type_num, t1.extreme_type=t2.extreme_type
                  where t1.GUID='$GUID' AND t2.GUID='$GUID_ADM'";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊新增失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }

         // Update customer (copy setting from GUID_ADM) 
         $sql = "Update customer t1, customer t2
                   set t1.uploadMask=t2.uploadMask, t1.netDisk=t2.netDisk, t1.removableDisk=t2.removableDisk,
                     t1.conf=t2.conf, t1.expressEnable=t2.expressEnable, t1.expressTimeout=t2.expressTimeout
                   where t1.GUID='$GUID' AND t2.GUID='$GUID_ADM'";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊新增失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }
         
         // Update customer (copy setting from GUID_ADM) 
         // #001, set expire_time from GUID_ADM 
         $sql = "update customer t1, customer t2 set t1.expire_time = t2.expire_time where t1.GUID = '$GUID' and t2.GUID = '$GUID_ADM'";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊新增失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }

         // create dir
         $guid_dir_path= "$GUID_DIR_PATH_PREFIX/$GUID";
         if (!file_exists($guid_dir_path))
         {
            if(system("mkdir -p -m 0774 $guid_dir_path"))
            {
               $resultMsg = $resultMsg . "$name -- 系統目錄建立失敗<br/>";
               continue;
            }
         }

         // copy systemScanDir.txt
         $tmpCmd = "cp /usr/local/www/apache22/data/upload_old/$GUID_ADM/systemScanDir.txt" .
                      " /usr/local/www/apache22/data/upload_old/$GUID/systemScanDir.txt";
         system($tmpCmd);

         // copy whitelist.txt
         $tmpCmd = "cp /usr/local/www/apache22/data/upload_old/$GUID_ADM/whitelist.txt" .
                      " /usr/local/www/apache22/data/upload_old/$GUID/whitelist.txt";
         system($tmpCmd);

         $resultMsg = $resultMsg . "$name -- 分行新增成功<br/>";
      }
      else // name 已經存在, update
      {
         $row = mysqli_fetch_array($result);
         $status = (int)$row["status"];
         if ($status != 1) // #002 status 是 1 才更新，若不是1，傳回"新增失敗"
         {
            //$resultMsg = $resultMsg . "$name -- 分行資訊更新失敗(error: $status, -" . __LINE__ . "<br/>";
            $resultMsg = $resultMsg . "$name -- 分行新增失敗(error: $status, -" . __LINE__ . "<br/>";
            continue;
         }
         $GUID = $row["GUID"];

         // Update customer
         $sql = "Update customer set login_name='$login_name',password='$password',
                                     validcode='$validcode',contact_email='$contact_email'
                  where GUID='$GUID'";
         if (!mysqli_query($link, $sql))
         {
            $resultMsg = $resultMsg . "$name -- 分行資訊更新失敗(error:-" . __LINE__ . "<br/>";
            continue;
         }

         $resultMsg = $resultMsg . "$name -- [$sql] 分行更新成功<br/>";
      }
   }
   else
   {
      mysqli_close($link);
      sleep(DELAY_SEC);
      displayResultPage("上傳失敗(error:-" . __LINE__ . ")");
      return;
   }
}

if($link){
   mysqli_close($link);
}

displayResultPage($resultMsg);

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

   // #001, change the title for the CSV table
   $UTF8_TITLE = "分行名稱\t分行管理者帳號\t分行管理者密碼\t用戶端盤點密碼\t分行管理者Email\r\n";
      

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
      //displayResultPage($head . "<BR>" . "$UCS2_BOM$UCS2_TITLE");
      //exit();
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
