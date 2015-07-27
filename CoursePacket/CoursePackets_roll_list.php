<?php
   require_once("CoursePackets_utility.php");

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
      echo FILE_ERROR;
      return;
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

   //return value
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);

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
   $UserId;

   //query
   $link;

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   if (($coursepacket_id = check_number($_GET["CoursePacketId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   $userarray = array();
   
   function get_employ_id_from_user_id($userids)
   {
      if(strlen($userids)>0){
         $userids = substr($userids,1);
         $userids = substr($userids,0,-1);
         $userids = str_replace(",,",",",$userids);
      }
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;
         die("连接DB失败");
      }
      
      $str_query = "select * from users where UserId in ($userids)";
      if($result = mysqli_query($link, $str_query)){
         while($row = mysqli_fetch_assoc($result)){
            echo $row["EmployeeId"] . "\n";
         }
         return;
      }
      else
      {
         echo DB_ERROR;
         die("操作资料库失败");
      }
   }
   
   
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
		
<title>武田 - 上传考试用户页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<Script Language=JavaScript>
function lockFunction(obj, n)
{
   if (g_defaultExtremeType[n] == 1)
      obj.checked = true;
} 

function click_logout()  //log out
{
   document.getElementsByName("logoutform")[0].submit();
}

function loaded()
{  
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyUsersContent(coursepacket_id)
{
   newUsersBatchInput = document.getElementsByName("newUsersBatchInput")[0].value.trim();
   users_id = newUsersBatchInput.match(/[^\r\n]+/g);
   
   if (users_id)
   {
      for (i=0; i<users_id.length; i++)
      {
         users_id[i] = trim_start_end(users_id[i]);
         if (!users_id[i].match(/^[0-9|a-zA-Z]+$/))
         {
            alert(users_id[i] + "有非数字及英文之字元");
            return;
         }
      }
   }
   else
   {
      confirm("没有输入员工号时，会刪除此考卷的已有名单。确定刪除？");
   }

   url_str = "CoursePackets_update_roll.php";
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str,
      data: {
               "coursepacket_id": coursepacket_id,
               "users_id[]": users_id,
            },
      cache: false,
      success: function(res)
      {
         if (res.match(/^-\d+$/))
         {
            alert("新增 / 删除课程包用户名单失败");
         }
         else if (res != 0)  //failed
         {
            alert(res);
         }
         else  //success
         {
            alert("用户批次新增 / 删除成功，页面关闭后请自行刷新");
            window.close();
         }
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}
</Script>
<!--Step15 新增修改页面    起始 -->
</head>
<body Onload="loaded();">
        <!--Main Content Start -->
        <div class="" id="content">
            
            <!-- Header -->
            <header class="top-head container-fluid">
                <button type="button" class="navbar-toggle pull-left">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                
                
                <!-- Left navbar -->
                <nav class=" navbar-default hidden-xs" role="navigation">
                    <ul class="nav navbar-nav">

                        <li><a href="#"><?php echo date('Y-m-d',time()); ?></a></li>
                    </ul>
                </nav>
                
                <!-- Right navbar -->
                <ul class="list-inline navbar-right top-menu top-right-menu">  

                    <!-- user login dropdown start-->
                    <li class="dropdown text-center">
              	
						   <input type="hidden" id="userid" value="<?php echo $user_id ?>" />
						   <form name=logoutform action=logout.php>
						   </form>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-user"></i>
                            <span class="username"><?php echo $login_name ?> </span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu extended pro-menu fadeInUp animated" tabindex="5003" style="overflow: hidden; outline: none; display:none;">
                            <li><a href="javascript:void(0)" OnClick="click_logout();"><i class="fa fa-sign-out"></i> 退出</a></li>
                        </ul>
                    </li>
                    <!-- user login dropdown end -->       
                </ul>
                <!-- End right navbar -->

            </header>
            <!-- Header Ends -->


            <!-- Page Content Start -->
            <!-- ================== -->

            <div class="wraper container-fluid">
                <div class="page-title"> 
                    <h3 class="title">批次上传课程包用户</h3> 
                </div>

                <!-- Basic Form Wizard -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body"> 

   <div id="exam_id" style="disply:none;"></div>
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>批次上传：(一行一笔数据，数据格式为 "工号"。直接在表格上修改考试人员异动)</th>
      </tr>
      <tr>
         <td><Textarea name=newUsersBatchInput rows=30 cols=100 placeholder="员工号 如 E99999"><?   
            // read roll
         $str_query = "select * from CoursePacket where CoursePacketId=$coursepacket_id AND Status=".ACTIVE;
         if($result = mysqli_query($link, $str_query)){
            $row_number = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            $userids = $row["UserList"];
            get_employ_id_from_user_id($userids);
         }?></Textarea></td>        
      </tr>   
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyUsersContent"><input name="modifyUsersButton" type="button" value="保存" OnClick="modifyUsersContent(<?echo $coursepacket_id?>)"></a>
         </th>
      </tr>        
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

<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<script type="text/javascript" src="../lib/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<script type="text/javascript" src="../js/utility.js"></script>

</body>
</html>
<!--Step15 新增修改页面    结束 -->