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
   define("FILE_PATH", $file_path);

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
      if(strcmp($check_str, "uploadCourseware"))
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
   if(($FileId = check_number($_POST["CoursewareId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      $resultStr = SYMBOL_ERROR . " " . __LINE__;
      goto errexit;
   }
   $CoursewareNameModify = $_POST["CoursewareNameModify"];
   $CoursewareDescModify = $_POST["CoursewareDescModify"];
   $PAList = $_POST["PAListValue"];
   $ProductList = $_POST["ProductListValue"];
   $CoursewareFile = $_POST["CoursewareFile"];
   $extensions = explode('.',$CoursewareFile);
   $escount = count($extensions)-1;
   $extension = $extensions[$escount];
   ///////////////////////////////////
   // 2. 取得 FileId
   //       先 insert 再取得 FileId
   ///////////////////////////////////
   $str_query1 = "Insert into coursewares (CoursewareName, CoursewareDesc, PAList, ProductList, Status, CreatedUser, CreatedTime, EditUser, EditTime, CoursewareFile) 
   VALUES('$CoursewareNameModify','$CoursewareDescModify','$PAList','$ProductList',-1,$user_id,now(),$user_id,now(),'$CoursewareFile');";

   mysqli_query($link,$str_query1);
   $FileId = mysqli_insert_id($link);
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
   $total_file_path = FILE_PATH . "/coursepacket";
   $total_file_path = strtr($total_file_path,"/","\\");
   if(!file_exists($total_file_path)) // 建立目录
      system("mkdir $total_file_path");

   if(!copy($_FILES["CoursewarePathModify"]["tmp_name"],"$total_file_path/$FileId." . $extension))
   {
      sleep(DELAY_SEC);
      $resultStr = "文档上传失败 - " . -__LINE__;
      goto errexit;
   }else{
      $resultStr = "文档上传成功";
      $str_query2 = "update coursewares set Status = 1 where CoursewareId = $FileId";
      mysqli_query($link,$str_query2);
      goto errexit;
   }

   mysqli_close($link);
errexit:
   $resultStr = "[" . $CoursewareFile . "] " . $resultStr;
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