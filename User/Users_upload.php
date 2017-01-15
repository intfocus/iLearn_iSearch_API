<?php
   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
   if (file_exists(FILE_NAME)) {
      include (FILE_NAME);
   } else {
      sleep(DELAY_SEC);
      $resultStr = FILE_ERROR . " " . __LINE__;
   }
   
   try {
      // TODO: 从 Session 里面拿到 login_name + user_id
      session_start();
      if (isset($_SESSION["GUID"]) == "" || isset($_SESSION["username"]) == "") {
         session_write_close();
         sleep(DELAY_SEC);
         header("Location:" . $web_path . "main.php?cmd=err");
         exit();
      }
   } catch(exception $ex) {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:" . $web_path . "main.php?cmd=err");
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
   $result;
   //query result
   $result1;
   $row;
   //result data array
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
   define("ILLEGAL_CHAR", "'-;<>");
   //illegal char
   define("UPLOAD_FILE_NAME", "upload.pdf");
   
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
   if (!$link)//connect to server failure
   {
      sleep(DELAY_SEC);
      $resultStr = "文档上传失败 - " . -__LINE__;
   }
   
   //----- Check command -----
   function check_command($check_str) {
      if (strcmp($check_str, "uploadFile")) {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   //----- Check number -----
   function check_number($check_str) {
      if (!is_numeric($check_str)) {
         return SYMBOL_ERROR;
      }
      if ($check_str < 0) {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   $resultStr = "上传成功，文档格式转换需要数分钟";
?>

<!DOCTYPE html>
<html lang="zh-CN">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
      <meta http-equiv="Pragma" content="no-cache">
      <meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
      <link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
      <link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
      <link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
      <link rel="stylesheet" type="text/css" href="../css/OSC_layout_new.css">
      <link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
      <!-- for tree view -->
      <!-- End of tree view -->
      <!--[if lt IE 10]>
      <script type="text/javascript" src="lib/PIE.js"></script>
      <![endif]-->

      <!-- Bootstrap core CSS -->
      <link href="../newui/css/bootstrap.min.css" rel="stylesheet">
      <link href="../newui/css/bootstrap-reset.css" rel="stylesheet">

      <!--Animation css-->
      <link href="../newui/css/animate.css" rel="stylesheet">

      <!--Icon-fonts css-->
      <link href="../newui/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="../newui/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

      <!--Morris Chart CSS -->
      <link rel="stylesheet" href="../newui/assets/morris/morris.css">

      <!-- sweet alerts -->
      <link href="../newui/assets/sweet-alert/sweet-alert.min.css" rel="stylesheet">
	  

      <!-- Custom styles for this template -->
      <link href="../newui/css/style.css" rel="stylesheet">
      <link href="../newui/css/helper.css" rel="stylesheet">
      <link href="../newui/css/style-responsive.css" rel="stylesheet" />
	  <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
	  <link href="../css/datepicker.css" media="all" rel="stylesheet" type="text/css" />
	  <link href="../css/timepicker.css" media="all" rel="stylesheet" type="text/css" />
	  <link href="../js/date-timepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
	  <link rel="stylesheet" type="text/css" href="../css/css/style.css">
	  
	  <!--<script type="text/javascript" src="../js/bootstrap.min.js"></script>-->
	  <!--<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>-->
		
      <title>武田 - 名单上传</title>
      <!-- BEG_ORISBOT_NOINDEX -->
      <Script Language=JavaScript>
         function loaded() {
      
         }
      </Script>
      <!--Step15 新增修改页面    起始 -->
   </head>
   <body Onload="loaded();">
      <!--Main Content Start -->
      <div class="" id="content">
         <!-- Header -->
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
         <!-- Header Ends -->
         <!-- Page Content Start -->
         <!-- ================== -->
   
         <div class="wraper container-fluid">
            <div class="page-title" style="margin-top: 50px;"> 
                <h3 class="title">上传名单</h3> 
            </div>
      
            <!-- Basic Form Wizard -->
            <div class="row">
               <div class="col-md-12">
                  <div class="panel panel-default">
                     <div class="panel-body"> 
                        <table class="searchField" border="0" cellspacing="0" cellpadding="0">
                           <form enctype="multipart/form-data" action="check_user_upload.php" method="POST" enctype="multipart/form-data">
                              <tr>
                                 <th>选取上传文档：</th>
                                 <td>
                                    <Input type=file size=50 name="fileToUpload" class="form-control"/>
                                 </td>
                              </tr>
                              <tr>
                                 <th colspan="2" class="submitBtns">
                                       <input type="submit" value="上传文档" name="submit"  class="btn btn-purple">&nbsp;&nbsp;<a href="http://tsa-china.takeda.com.cn/uat/public/User.xlsx" value="名单模板下载">名单模板下载</a>
                                 </th>
                              </tr>      
                           </Form>
                        </table>
                     </div>  <!-- End panel-body -->
                  </div> <!-- End panel -->
               </div> <!-- end col -->
            </div> <!-- End row -->
         </div>
         <!-- Page Content Ends -->
         <!-- ================== -->
         <!-- Footer Start -->
         <footer class="footer">
            2015 © Takeda.
         </footer>
         <!-- Footer Ends -->
      </div>
      <!-- Main Content Ends -->

      <!--<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>-->
      <script type="text/javascript" src="../lib/jquery.min.js"></script>
      <script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
      <script type="text/javascript" src="../js/OSC_layout.js"></script>
   </body>
</html>