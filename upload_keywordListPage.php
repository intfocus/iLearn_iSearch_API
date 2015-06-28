<?php

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
}
else{
   sleep(DELAY_SEC);
   echo "(error:-" . -__LINE__ . ")";
   return;
}

////////////////////////
// get guid from session
////////////////////////

session_start();
if(!session_is_registered("GUID")){
   sleep(DELAY_SEC);
   echo "(error:-" . -__LINE__ . ")";
   return;
}

if ($_SESSION["GUID"] == ""){
   sleep(DELAY_SEC);
   echo "(error:-" . -__LINE__ . ")";
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
      echo "(error:-" . -__LINE__ . ")";
      return;
   }
}

///////////////////////////////////
// If not exist list.csv
// copy computerList.csv file to upload_old/guid dir
///////////////////////////////////
$list_file_path = "$guid_dir_path/$CSV_FILE_NAME";
$list_file_url = "/p-marker//upload_old/$guid/$CSV_FILE_NAME";
if(!file_exists($list_file_path))
{
   system("cp /usr/local/www/apache22/data/KeywordList.csv $list_file_path");
}

?>

<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<title>Openfind P-Marker 關鍵字清單上傳</title>
<link rel="stylesheet" type="text/css" href="lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="css/OSC_layout.css">
<link type="text/css" href="lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/login.css">
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/OSC_layout.js"></script>
<script type="text/javascript" src="js/css3pie.js"></script>
<script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
<script type="text/javascript" src="openflashchart/js/json/json2.js"></script>
</head>
<body>
<div id="header">
   <span class="logo"></span>
</div>
<div id="banner">
   <span class="bLink first"><span>關鍵字清單製作與上傳</span><span class="bArrow"></span></span>
</div>
<div class="listUploadW">
   <div class="title">步驟一</div>
   <div class="content"><a href=<?php echo $list_file_url?>>下載關鍵字清單表格</a></div>
   <div class="title">步驟二</div>
   <div class="content">
      請使用 excel 開啟空白清單表格，填入貴單位自訂之檔案掃瞄關鍵字。<br>
      請勿修改原本之檔案格式與編碼方式，並且保留原表頭，以避免上傳後 P-Marker 無法辨認格式。
   </div>
   <div class="title">步驟三</div>
   <div class="content">
      上傳關鍵字清單
      <form enctype="multipart/form-data" action="upload_keywordList.php" method="POST" class="listUploadForm">
         <input name="csvfile" type="file"><br>
         <a class="btn_submit_new upload"><input type="submit" value="檔案上傳"></a>
      </form>
   </div>
</div>
<div class="declaration">© 2013 Openfind Information Technology, Inc. All rights reserved.<br>版權所有 網擎資訊軟體股份有限公司</div>
</body>
</html>
