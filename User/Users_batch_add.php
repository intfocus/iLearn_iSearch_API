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
   session_start();
   if ($_SESSION["GUID"] == "" || $_SESSION["username"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      //header("Location:". $web_path . "main.php?cmd=err");
      $return_string = "<div id=\"sResultTitle\" class=\"sResultTitle\">Session 已经过期，请重新登录！</div>";
      echo $return_string;
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
   if ($cmd == "read") // 准备 batch new 画面
   {
   }
   else if ($cmd == "write") // Batch Insert
   {
      $newUsersBatchInput = $_POST["newUsersBatchInput"];
      $DeptId = $_POST["DeptId"];
      // 1. 按照 \n 切开
      $tmp = explode("\n", $newUsersBatchInput);
      $tmp_count = count($tmp);
      // 2. 按照 工号,姓名,Email 取出, 放入 Array
      $result = Array();
      for ($i=0;$i<$tmp_count;$i++)
      {
         $ret = explode(',',$tmp[$i]);
         if (count($ret) != 4 || strlen($ret[0])==0 || strlen($ret[1])==0 || strlen($ret[2])==0 || strlen($ret[3])==0)
         {
            echo "-- 第" . ($i+1) . "笔数据格式错误 -- " . $tmp[$i];
            return;
         }
         if (check_email($ret[2]) != SUCCESS)
         {
            echo "-- 第" . ($i+1) . "笔数据 Email 格式错误 -- " . $tmp[$i];
            return;
         }
         Array_Push($result,$ret);
      }
      // 3. Transaction begin
      mysqli_autocommit ($link, FALSE);      
      // 4. For each row in Array, Insert, Rollback if failed
      $result_count = count($result);
      for ($i=0;$i<$result_count;$i++)
      {
         $EmployeeId = $result[$i][0];
         $UserName = $result[$i][1];
         $UserEmail = $result[$i][2];
         $UserWId = $result[$i][3];
         $sql_str = "Insert into Users (UserName,Email,EmployeeId,DeptId,Status,CanApprove,JobGrade,CreatedUser,CreatedTime,EditUser,EditTime,UserWId)" .
            " VALUES('$UserName','$UserEmail','$EmployeeId',$DeptId,1,0,1,$user_id,now(),$user_id,now(),'$UserWId');";
         if (!mysqli_query($link, $sql_str))
         {
            mysqli_rollback($link);
            if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            $ErrMsg = "第" . ($i+1) . "笔数据新增失败 -- $EmployeeId,$UserName,$UserEmail,$UserWId";
            echo "-- " . $ErrMsg;
            return;
         }
      }
      // 5. Commit
      mysqli_commit($link);
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo "0";
      return;
   }
   else
   {
      echo -__LINE__;
      return;
   }
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
   window.setTimeout("expandTo()",2000);
}
//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyUsersContent()
{
   alert("OK");
   newUsersBatchInput = document.getElementsByName("newUsersBatchInput")[0].value.trim();
   DeptId = getSelectedId();
   
   //str = "cmd=write&newUsersBatchInput=" + encodeURIComponent(newUsersBatchInput) + "&DeptId=" + DeptId;
   url_str = "../User/Users_batch_add.php?cmd=write";

   //alert(url_str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str,
      data:{
         newUsersBatchInput:newUsersBatchInput,
         DeptId:DeptId,
      },
      cache: false,
      dataType: 'json',
      success: function(res)
      {
         res = String(res);
         //alert("Data Saved: " + res);
         if (res.match(/^-/))  //failed
         {
            alert("错误信息："+res);
         }
         else  //success
         {
            alert("用户批次新增成功，页面关闭后请自行刷新");
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
<body Onload="loaded();" style="padding-top: 62px !important; background: rgb(255, 255, 255);">
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
  <li class="active">批次上传用户</li>
</ol>
<div id="content">
	<form class="cmxform form-horizontal tasi-form searchField" id="commentForm" method="get" action="#" novalidate="novalidate">
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-10" style="text-align:left; margin-bottom:5px;">批次上传内容：(一行一笔数据，数据格式为 工号,姓名,Email)：</label>
		<div class="col-lg-7">
			<textarea style="height:250px;" class="form-control " id="ccomment" name="newUsersBatchInput">工号1,姓名1,Email1
工号2,姓名2,Email2</textarea>
		</div>
	</div>
	<div class="form-group ">
		<label for="curl" class="control-label col-lg-10" style="text-align:left; margin-bottom:5px;">选择部门：</label>
		<div class="col-lg-7">

	<div class="easyui-panel" style="padding:5px">
		<ul id="tt" class="easyui-tree" data-options="url:'<?php echo $web_path ?>Dept_tree_load.php',method:'get',animate:true"></ul>
	</div>
	<script type="text/javascript">
      function expandTo(){
		 var node = $('#tt').tree('find',1);
		 $('#tt').tree('expandTo', node.target).tree('select', node.target);
         $('#displayExpandToButton').hide();
		 $('#tt').tree('collapseAll');
	  }
      function getSelectedId(){
			var node = $('#tt').tree('getSelected');
			if (node){
            return node.id;
			}
         else
            return 0;
		}
	</script>         
 

		</div>
	</div>
	  <div class="form-group">
		<div class="col-lg-7">
		<input class="btn btn-success" name="modifyUsersButton" type="button" value="保存" OnClick="modifyUsersContent()">
		</div>
	</div>
</form>

</div>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->