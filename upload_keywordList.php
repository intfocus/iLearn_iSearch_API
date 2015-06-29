<?php
//////////////////////////////////
// 20130603, Odie, Modified from upload_computerList.php
//           Process the user-uploaded keyword list and store it to DB
//////////////////////////////////

$GUID_DIR_PATH_PREFIX = '/usr/local/www/apache22/data/upload_old';
$CSV_FILE_NAME = 'keyword.csv';

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
   define(DISPLAY_TEMPLATE, "/usr/local/www/apache22/data/upload_keywordListResult.html");
}
else{
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

////////////////////////
// get guid from session
////////////////////////

session_start();
if(!session_is_registered("GUID")){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

if ($_SESSION["GUID"] == ""){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
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
      //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
      displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
      return;
   }
}

///////////////////////////////////
// copy upload csv file to guid dir
///////////////////////////////////

$ucs2_content = file_get_contents($_FILES["csvfile"]["tmp_name"]);
if($ucs2_content == FALSE){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

$utf8_body = ucs2_to_utf8($ucs2_content);

if($utf8_body == FALSE){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

if(!copy($_FILES["csvfile"]["tmp_name"],"$guid_dir_path/$CSV_FILE_NAME")){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

//////////////////////////
// connect to mysql server
//////////////////////////

$link = @mysqli_connect(DB_HOST,ADMIN_ACCOUNT,ADMIN_PASSWORD,CONNECT_DB);
if(!$link){
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

/////////////////////////
// transfer the keyword list to regular expresion
/////////////////////////

$lines = explode("\r\n", trim($utf8_body));
/*
$regexp = "(^|[\\w\\s\\b\"]|[^\\x{4e00}-\\x{9fff}])\\K((?i)";
foreach($lines as $line){
   $regexp .= "$line|";
}
$regexp = rtrim($regexp, "|");
$regexp .= ")(?=$|[\\w\\s\\b\"]|[^\\x{4e00}-\\x{9fff}])";
*/

$keyword_string = "";
foreach($lines as $line){
   $keyword_string .= "$line|";
}
$keyword_string = rtrim($keyword_string, "|");
$regexp = "(^|\\s|(?=[\\x{4e00}-\\x{9fff}]))\\K((?i)". $keyword_string. ")((?<=[\\x{4e00}-\\x{9fff}])|(?=$|\\s))";
//$regexp = "(^|\\s)\\K((?i)". $keyword_string. ")(?=$|\\s)";

/////////////////////////
// update DB
/////////////////////////

if(!get_magic_quotes_gpc())
   $regexp = mysql_real_escape_string($regexp);

$insert_cmd = "UPDATE customer SET keyword1='$regexp' WHERE GUID='$guid'";

if(!mysqli_query($link, $insert_cmd)){
   mysqli_close($link);
   sleep(DELAY_SEC);
   //echo "¤W¶Ç¥¢±Ñ¡C(error:-" . -__LINE__ . ")";
   displayResultPage("ä¸Šå‚³å¤±æ•—(error:-" . -__LINE__ . ")");
   return;
}

if($link){
   mysqli_close($link);
}

//echo "¤W¶Ç¦¨¥\¨Ã¤w§ó·s¸ê®Æ";
displayResultPage("ä¸Šå‚³æˆåŠŸä¸¦å·²æ›´æ–°è³‡æ–™");

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
// 1. $content ¥²¶·¬O ucs2 little endian ½s½X¥B«e­±¦³¥[ 2Bytes BOM => FF FE
// 2. return Âà¦¨ utf-8 «áªº content body
///////////////////////////////////////////////////////////////////////////
function ucs2_to_utf8($ucs2_content){

   $UCS2_BOM = "\xff\xfe";
   $BIG5_TITLE = "ÃöÁä¦r\r\n";

   $UCS2_TITLE = iconv('BIG-5','UCS-2LE',$BIG5_TITLE);
   if($UCS2_TITLE == FALSE){
      return FALSE;
   }

   /////////////////////////////////////
   //  ¤ñ¹ï ucs2_content head ¦³¨S¦³¥¿½T
   /////////////////////////////////////

   $head_len = strlen($UCS2_BOM) + strlen($UCS2_TITLE);
   $head = substr($ucs2_content,0,$head_len);
   if($head != "$UCS2_BOM$UCS2_TITLE"){
      return FALSE;
   }

   /////////////////////
   // ±N ucs2 Âà¦¨ utf-8
   /////////////////////
   $ucs2_body = substr($ucs2_content,$head_len);

   $utf8_body = iconv('UCS-2LE','UTF-8',$ucs2_body);
   if($utf8_body == FALSE){
      return FALSE;
   }

   return $utf8_body;

}

?>
