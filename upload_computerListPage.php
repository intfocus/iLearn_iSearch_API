<?php

////////////////////////
// #001, 20130619, modified by Odie
//       上傳清單可以選擇要覆蓋或合併舊有的資料
////////////////////////

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
$list_file_url = "/p-marker/upload_old/$guid/$CSV_FILE_NAME";
if(!file_exists($list_file_path))
{
   system("cp /usr/local/www/apache22/data/ComputerList.csv $list_file_path");
}

?>

<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<title>Openfind P-Marker 資產清單上傳</title>
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
   <span class="bLink first"><span>用戶端電腦清單製作與上傳</span><span class="bArrow"></span></span>
</div>
<div class="listUploadW">
   <div class="title">步驟一</div>
   <div class="content"><a href=<?php echo $list_file_url?>>下載用戶端電腦清單表格</a></div>
   <div class="title">步驟二</div>
   <div class="content">
      請利用步驟一下載的 excel 檔案，填入貴單位需要個資掃描之電腦清單。<br />
		注意：<br />
		1. 請勿修改原本之檔案格式<br />
		2. 請保留原表頭，以避免上傳後 P-Marker 無法辨認格式<br />
		3. 該 excel 檔案編碼必須為 Unicode<br />
		為確保囊括 Big5 不支援的中文字，因此在儲存檔案時出現如下視窗，請按「是(Y)」<br />
		<br />
		<img src="images/computer_list_excel_instruction.png" border="0" /><br />
   </div>
   <div class="title">步驟三</div>
   <div class="content">
      上傳用戶端電腦清單<br />
      <form enctype="multipart/form-data" action="upload_computerList.php" method="POST" class="listUploadForm">
         <input name="csvfile" type="file"><br /><br />
         <!-- #001 -->
         上傳方式： <br />
         <input type="radio" name="process" value="merge" checked> 將資料更新至系統，不影響其它已存在的人員資料。 <br />
         <input type="radio" name="process" value="overwrite"> 將資料上傳至系統，並<span style="color:red;font-weight:bold;">刪除</span>其它已存在的人員資料。 <br />
         <a class="btn_submit_new upload"><input type="submit" value="檔案上傳"></a>
      </form>
   </div>
   <div class="title">說明</div>
   <div class="content">
      管理者可製作並上傳「用戶端電腦清單」 -- 此清單定義了欲盤點電腦的基本資訊，如：電腦持有人員名稱、部門、電腦名稱…等。<br />
      <img src="/images/computer_list.png" border="0" /><br /><br />
      P-Marker Cloud 依據管理者定義的「用戶端電腦清單」，提供以下功能：<br /><br />
      <ul style="list-style-type:circle;"><li style="margin-left:3em">當清單內的電腦完成掃描後，系統將自動以管理者指定的部門、人員名稱，套用到這些已盤點的記錄中，有助於提高盤點結果的正確性。（不在清單內的電腦掃描結果，僅能顯示用戶端自行輸入的部門及姓名）<br /><br /></li>
         <li style="margin-left:3em">清單內電腦若未執行個資盤點，「用戶管理」頁面可搜尋出這些電腦並標示為「未實施」狀態。</li>
      </ul>
   </div>
</div>
<div class="declaration">© 2013 Openfind Information Technology, Inc. All rights reserved.<br>版權所有 網擎資訊軟體股份有限公司</div>
</body>
</html>
