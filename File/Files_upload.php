<?php

   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      $resultStr = FILE_ERROR . " " . __LINE__;
      goto errexit;
   }
   $login_name = "Phantom";

   //query          
   $link;
   $db_host;
   $admin_account;
   $admin_password;
   $connect_db;
   $str_query;
   $str_query1;
   $result;                 //query result
   $result1;
   $row;                    //result data array
   $row1;
   $row_number;
   $refresh_str;
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
   define("UPLOAD_FILE_NAME","upload.pdf");

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      $resultStr =  "文档上传失败 - " . -__LINE__;
      goto errexit;
   }
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "uploadFile"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str < 0)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $FileId;
   
   ///////////////////////////////////
   // 1.get information from client 
   ///////////////////////////////////
   if(($cmd = check_command($_POST["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      $resultStr = SYMBOL_ERROR_CMD . " " . __LINE__;
      goto errexit;
   }
   if(($FileId = check_number($_POST["FileId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      $resultStr = SYMBOL_ERROR . " " . __LINE__;
      goto errexit;
   }
   if(($CategoryId = check_number($_POST["CategoryId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      $resultStr = SYMBOL_ERROR . " " . __LINE__;
      goto errexit;
   }
   $CategoryFilePath = $_POST["CategoryFilePath"];
   $FileTitleModify = $_POST["FileTitleModify"];
   $FileDescModify = $_POST["FileDescModify"];
   $FileTypeModify = $_POST["FileTypeModify"];
   $FileName = $_POST["FileName"];
   
   ///////////////////////////////////
   // 2. 取得 FileId
   //       先 insert 再取得 FileId
   ///////////////////////////////////
   $str_query1 = "Insert into Files (FileName, FileTitle, FileDesc, CategoryId, FilePath, SmallGifPath, PageNo, FileType, 
      Status, CreatedUser, CreatedTime, EditUser, EditTime) VALUES('$FileName','$FileTitleModify','$FileDescModify',$CategoryId,'$CategoryFilePath','',0,
      $FileTypeModify,0,1,now(),1,now());";
   mysqli_query($link,$str_query1);
   //echo $str_query1;
   //return;
   $FileId = mysqli_insert_id($link);
   $str_query2 = "update Files set FilePath = '" . $CategoryFilePath . "/" . $FileId . "' where FileId = $FileId";
   mysqli_query($link,$str_query2);
   if ($FileId == 0) // 取得失败
   {
      sleep(DELAY_SEC);
      $resultStr = "文档上传失败 - " . -__LINE__;
      goto errexit;
   }
   
   ///////////////////////////////////
   // 3. 产生 $CategoryFliePath\$FileId 目录
   //    复制到 $CategoryFliePath\$FileId 目录下
   ///////////////////////////////////
   $total_file_path = $CategoryFilePath . "/" . $FileId;
   $total_file_path = strtr($total_file_path,"/","\\");
   if(!file_exists($total_file_path)) // 建立目录
      system("mkdir $total_file_path");

   if(!copy($_FILES["FilePathModify"]["tmp_name"],"$total_file_path/$FileId.pdf"))
   {
      sleep(DELAY_SEC);
      $resultStr = "文档上传失败 - " . -__LINE__;
      goto errexit;
   }
   
   ///////////////////////////////////
   // 4. 写入 $pdf2image_temp
   //    写入 $pdf2image_bin, FileId, FilePath
   $fp = fopen($pdf2image_temp,"a+");
   if (!$fp)
   {
      sleep(DELAY_SEC);
      $resultStr = "文档上传失败 - " . -__LINE__;
      goto errexit;
   }
   fprintf($fp,"%s,%s,%s\n",$pdf2image_bin,$FileId,$CategoryFilePath);
   fclose($fp);
   
   $resultStr = "上传成功，文档格式转换需要数分钟";
   
errexit:   
   $resultStr = "[" . $FileName . "] " . $resultStr;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="../css/OSC_layout.css">
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../lib/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<!-- for tree view -->
<link rel="stylesheet" type="text/css" href="../css/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="../css/themes/icon.css">
<link rel="stylesheet" type="text/css" href="../css/demo.css">
<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<!-- End of tree view -->
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<title>武田 - 部门页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<Script Language=JavaScript>
function loaded() {
   
}
</Script>
<!--Step15 新增修改页面    起始 -->
</head>
<body Onload="loaded();">
<div id="header">
   <form name=logoutform action=logout.php>
   </form>
   <span class="global">使用者 : <?php echo $login_name ?>
      <font class="logout" OnClick="click_logout();">登出</font>&nbsp;
   </span>
   <span class="logo"></span>
</div>
<div id="banner">
   <span class="bLink first"><span>后台功能名称</span><span class="bArrow"></span></span>
   <span class="bLink company"><span>上传结果</span><span class="bArrow"></span></span>
</div>
<div id="content">
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>上传文档结果：</th>
         <td><?php echo $resultStr;?></td>
      </tr>
   </table>
</div>
</body>
</html>