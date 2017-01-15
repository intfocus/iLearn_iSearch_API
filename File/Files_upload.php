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
   
   try{
      // TODO: 从 Session 里面拿到 login_name + user_id
      session_start();
      if (isset($_SESSION["GUID"]) == "" || isset($_SESSION["username"]) == "")
      {
         session_write_close();
         sleep(DELAY_SEC);
         header("Location:". $web_path . "main.php?cmd=err");
         exit();
      }
   }
   catch(exception $ex)
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:". $web_path . "main.php?cmd=err");
      exit();
   }
   
   $user_id = $_SESSION["GUID"];
   $login_name = $_SESSION["username"];
   // $login_name = "Phantom";
   // $user_id = 1;
   $current_func_name = "iSearch";
   session_write_close();

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
   $FileSize = 0;
   
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
      $FileTypeModify,0,$user_id,now(),$user_id,now());";
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

   if ($FileTypeModify == 1 || $FileTypeModify ==2){   
      if(!copy($_FILES["FilePathModify"]["tmp_name"],"$total_file_path/$FileId.pdf"))
      {
         sleep(DELAY_SEC);
         $resultStr = "文档上传失败 - " . -__LINE__;
         goto errexit;
      }
   }elseif ($FileTypeModify == 4){
      if(!copy($_FILES["FilePathModify"]["tmp_name"],"$total_file_path/$FileId.zip"))
      {
         sleep(DELAY_SEC);
         $resultStr = "文档上传失败 - " . -__LINE__;
         goto errexit;
      }
      $FileSize = filesize("$total_file_path/$FileId.zip");
      $str_query3 = "update Files set ZipSize = $FileSize where FileId = $FileId";
      mysqli_query($link,$str_query3);
   }else{
      if(!copy($_FILES["FilePathModify"]["tmp_name"],"$total_file_path/$FileId.mp4"))
      {
         sleep(DELAY_SEC);
         $resultStr = "文档上传失败 - " . -__LINE__;
         goto errexit;
      }
	  $FileSize = filesize("$total_file_path/$FileId.mp4");
      $str_query4 = "update Files set ZipSize = $FileSize where FileId = $FileId";
      mysqli_query($link,$str_query4);
   }
   
   ///////////////////////////////////
   // 4. 写入 $pdf2image_temp
   //    写入 $pdf2image_bin, FileId, FilePath
   if ($FileTypeModify == 1 || $FileTypeModify == 2 || $FileTypeModify == 3){
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
   }
   else{
      $resultStr = "文档上传成功!";
   }
   
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
<link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
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

<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
<link href="../css/datepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="../css/timepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="../js/date-timepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel="stylesheet" type="text/css" href="../css/css/style.css">

<script type="text/javascript" src="../js/bootstrap.min.js"></script>
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
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand hidden-sm" href="/uat/index.php" onclick="_hmt.push(['_trackEvent', 'navbar', 'click', 'navbar-首页'])">武田学习与工作辅助平台</a>
    </div>
    <div class="navbar-collapse collapse" role="navigation">
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown text-center">
                	
							   <form name="logoutform" action="logout.php">
							   </form>
			<a class="dropdown-toggle" href="#" aria-expanded="false">
				<i class="fa fa-user"></i>
				<span class="username">使用者 : <?php echo $login_name ?> </span> <!--<span class="caret"></span>-->
			</a>
			<!--<ul class="dropdown-menu extended pro-menu fadeInUp animated" tabindex="5003" style="overflow: hidden; outline: none;">
				<li><a href="javascript:void(0)" onclick="click_logout();"><i class="fa fa-sign-out"></i> 退出</a></li>
			</ul>-->
		</li>
		</ul>
    </div>
  </div>
</div>

<div class="container">
<ol class="breadcrumb">
  <li class="active">后台功能名称</li>
  <li class="active">上传结果</li>
</ol>
<div id="content">   
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">上传文档结果：</label>
		<div class="col-lg-7"><?php echo $resultStr;?></div>
	</div>
</div>
</div>
</body>
</html>