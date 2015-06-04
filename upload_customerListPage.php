<?php

////////////////////////
// #000, 20140917, created by Phantom 
//       上傳 Custmer 清單 ( Insert or Overwrite )
// 
////////////////////////

$GUID_DIR_PATH_PREFIX = '/usr/local/www/apache22/data/upload_old';
$CSV_FILE_NAME = 'customerList.csv';

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
if(!session_is_registered("GUID") || !session_is_registered("GUID_ADM")){
   sleep(DELAY_SEC);
   echo "(error:-" . -__LINE__ . ")";
   return;
}

if ($_SESSION["GUID"] == "" || $_SESSION["GUID_ADM"] == ""){
   sleep(DELAY_SEC);
   echo "(error:-" . -__LINE__ . ")";
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
      echo "(error:-" . -__LINE__ . ")";
      return;
   }
}

///////////////////////////////////
// If not exist customerList.csv
// copy customerList.csv file to upload_old/GUID_ADM dir
///////////////////////////////////
$list_file_path = "$guid_dir_path/$CSV_FILE_NAME";
$list_file_url = "/p-marker/upload_old/$GUID_ADM/$CSV_FILE_NAME";
if(!file_exists($list_file_path))
{
   system("cp /usr/local/www/apache22/data/customerList.csv $list_file_path");
}

?>

<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<title>Openfind P-Marker 系統管理者 - 分行清單上傳</title>
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
   <span class="bLink first"><span>系統管理者 - 分行清單製作與上傳</span><span class="bArrow"></span></span>
</div>
<div class="listUploadW">
   <div class="title">步驟一</div>
   <div class="content"><a href=<?php echo $list_file_url?>>下載分行清單表格</a></div>
   <div class="title">步驟二</div>
   <div class="content">
      請利用步驟一下載的 excel 檔案，填入分行清單。<br />
		注意：<br />
		1. 請勿修改原本之檔案格式<br />
		2. 請保留原表頭，以避免上傳後 P-Marker 無法辨認格式<br />
		3. 該 excel 檔案編碼必須為 Unicode<br />
		為確保囊括 Big5 不支援的中文字，因此在儲存檔案時出現如下視窗，請按「是(Y)」<br />
		<br />
		<img src="images/customer_list_excel_instruction.png" border="0" /><br />
   </div>
   <div class="title">步驟三</div>
   <div class="content">
      上傳分行清單<br />
      <form enctype="multipart/form-data" action="upload_customerList.php" method="POST" class="listUploadForm">
         <input name="csvfile" type="file"><br /><br />
         <a class="btn_submit_new upload"><input type="submit" value="檔案上傳"></a>
      </form>
   </div>
   <div class="title">說明</div>
   <div class="content">
      系統管理者可製作並上傳「分行清單」:<br/><br/>
      <ul style="list-style-type:circle;">
         <li style="margin-left:3em">不存在的分行名稱將新增資料<br /><br /></li>
         <li style="margin-left:3em">已經存在的分行名稱將覆蓋(分行管理者帳號、分行管理者密碼、用戶端盤點密碼、分行管理者 Email)<br/><br/></li>
         <li style="margin-left:3em">分行管理者帳號不可重複，否則該分行將新增 / 更新失敗</li>
      </ul>
   </div>
</div>
<div class="declaration">© 2014 Openfind Information Technology, Inc. All rights reserved.<br>版權所有 網擎資訊軟體股份有限公司</div>
</body>
</html>
