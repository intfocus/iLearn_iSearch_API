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
      echo DB_ERROR;                
      return;
   }
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "read") && strcmp($check_str, "write"))
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
   
   function check_email($check_str)
   {
      $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
      if(!preg_match($pattern,$check_str))
         return SYMBOL_ERROR;
      return SUCCESS;
   }
   
   //get data from client
   $cmd;
   $UserId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($UserId = check_number($_GET["UserId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   
   
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if ($cmd == "read") // Load
   {
      // 产生 $privilegesList
      $privilegesList = "";
      $str_query1 = "Select * from privileges where UserId=$UserId";
      //echo $str_query1;
      if($result = mysqli_query($link, $str_query1))
      {
         while ($row = mysqli_fetch_assoc($result))
         {
            $func_id = "," . $row["FunctionId"] . ",";
            $privilegesList = $privilegesList . $func_id;
         }
      }
      
      $str_query1 = "Select * from Users where UserId=$UserId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);

         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $UserId = $row["UserId"];
            $UserName = $row["UserName"];
            $EmployeeId = $row["EmployeeId"];
            $UserEmail = $row["Email"];
            $DeptId = $row["DeptId"];
            $CanApprove = $row["CanApprove"];
            $JobGrade = $row["JobGrade"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "用户权限修改";
            if ($Status == 1)
               $TitleStr = "用户权限修改";
         }
         else
         {
            $UserId = 0;
            $UserName = "";
            $EmployeeId = "";
            $UserEmail = "";
            $DeptId = 1;
            $CanApprove = 0;
            $JobGrade = "";
            $TitleStr = "用户权限新增";
            $Status = 0;
         }
      }
   }
   else // Update
   {
      // Delete all the privileges with $UserId
      $str_query1 = "delete from privileges where UserId=$UserId";
      mysqli_query($link, $str_query1);
      
      // Insert privileges one by one for $UserId
      $privilegesList = $_GET["privilegesList"];
      $tmp = explode(",", $privilegesList);
      $tmp_count = count($tmp);
      for ($i=0;$i<$tmp_count;$i++)
      {
         $str_query1 = "Insert into privileges (UserId,FunctionId,Status,CreatedUser,CreatedTime,EditUser,EditTime)" .
            "VALUES($UserId,$tmp[$i],0,$user_id,now(),$user_id,now());";
         mysqli_query($link, $str_query1);
      }
      echo "0";
      return;
   }
?>

<!DOCTYPE html>
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
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<!-- for tree view -->
<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
<link href="../css/datepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="../css/timepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="../js/date-timepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel="stylesheet" type="text/css" href="../css/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="../css/themes/icon.css">
<link rel="stylesheet" type="text/css" href="../css/demo.css">
<link rel="stylesheet" type="text/css" href="../css/css/style.css">

<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<!-- End of tree view -->
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->

<title>武田 - 用户页面</title>
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
   var palstr = "<?php echo $privilegesList; ?>";
   var palstr1 = palstr.substring(1,palstr.length-1);
   var palstr_array = palstr1.split(",,");
   for(var i=0; i<palstr_array.length;i++)
   {
      var palcheck_array=document.getElementsByName("privilegesList");
      for(var j=0;j<palcheck_array.length;j++)
      {
         if(palcheck_array[j].value==palstr_array[i])
         {         
            palcheck_array[j].checked=true;
         }
      }
   }
}
//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyPrivilegesContent(UserId)
{
   var rusult="";
   var check_array=document.getElementsByName("privilegesList");
   for(var i=0;i<check_array.length;i++)
   {
      if(check_array[i].checked==true)
      {
         rusult=rusult + check_array[i].value + ",";
      }
   }
   rusult = rusult.substring(0,rusult.length-1);
   
   str = "cmd=write&UserId=" + UserId + "&privilegesList=" + encodeURIComponent(rusult);
   url_str = "../Admin/Privileges_modify.php?";

   //alert(url_str + str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "GET",
      url: url_str + str,
      cache: false,
      success: function(res)
      {
         //alert("Data Saved: " + res);
         if (res.match(/^-/))  //failed
         {
            alert("用户后台权限管理修改失败 " + res);
         }
         else  //success
         {
            alert("用户后台权限管理修改成功，请重新登陆");
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

<body Onload="loaded();" style="padding-top:62px!important; background:#fff;">
<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand hidden-sm" href="/uatui/index.php" onclick="_hmt.push(['_trackEvent', 'navbar', 'click', 'navbar-首页'])">武田学习与工作辅助平台</a>
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
  <li class="active"><?php echo $TitleStr; ?></li>
</ol>


<div id="content">
<form class="cmxform form-horizontal tasi-form searchField" id="commentForm" method="get" action="#" novalidate="novalidate">
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">用户名称：</label>
		<div class="col-lg-7">
			<Input type=text readonly="true" class=" form-control" name=UserNameModify size=50 value="<?php echo $UserName;?>">
        </div>
    </div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">工号：</label>
		<div class="col-lg-7">
			<Input type=text readonly="true" class=" form-control" name=EmployeeId size=50 value="<?php echo $EmployeeId;?>">
        </div>
    </div>

	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">用户邮箱：</label>
		<div class="col-lg-7">
			<Input type=text readonly="true" class=" form-control" name=UserEmailModify size=50 value="<?php echo $UserEmail;?>">
        </div>
    </div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">后台功能列表：</label>
		<div class="col-lg-7">
			

      <!-- 显示所有的 function_name with functionId 以及目前这个 user 有哪些权限 开始-->
<?php      
      $str_query1 = "Select * from functions where FunctionType=0" ;
      if ($result = mysqli_query($link, $str_query1))
      {
         while ($row = mysqli_fetch_assoc($result))
         {
            $func_name = $row["FunctionName"];
            $func_id = $row["FunctionId"];
			
			?>
			<label class="cr-styled">
            <?php echo "<Input type=checkbox name='privilegesList' value='$func_id'> "; ?>
			<i class="fa"></i> 
		      <?php echo $func_name ?>
			</label>
		<?php
         }
      }
?>


      <!-- 显示所有的 function_name with functionId 以及目前这个 user 有哪些权限 结束-->
         
        </div>
    </div>
	<div class="form-group">
		<label class="control-label col-lg-2">　</label>
		<div class="col-lg-7">
		<input class="btn btn-success" name="modifyPrivilegesButton" type="button" value="保存" OnClick="modifyPrivilegesContent(<?php echo $UserId;?>)">
		</div>
	</div>
  </form>
</div>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->