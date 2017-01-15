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
   $UserId = $user_id;

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
   if ($cmd == "read") // Load,
   {
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
			$WinId = $row["UserWId"];
            $UserEmail = $row["Email"];
            $DeptId = $row["DeptId"];
            $CanApprove = $row["CanApprove"];
            $JobGrade = $row["JobGrade"];
			$UserSuper = $row["UserSuper"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
			$CheckInTime =  $row["CheckInTime"] == null ? '' : date("Y/m/d",strtotime($row["CheckInTime"]));
            $UserArea = $row["UserArea"];
            $UserPosition = $row["UserPosition"];
            $UserParent = $row["UserParent"];
			$UserParentId = $row["UserParentId"];
			$UserProduct = $row["UserProduct"];
            $TitleStr = "用户修改";
            if ($Status == 1)
               $TitleStr = "用户查看 (上架状态无法修改)";
         }
         else
         {
            $UserId = 0;
            $UserName = "";
            $EmployeeId = "";
			$WinId = "";
            $UserEmail = "";
            $DeptId = 1;
            $CanApprove = 0;
			$UserSuper = "";
            $JobGrade = "";
            $TitleStr = "用户新增";
            $Status = 0;
			$CheckInTime = "";
            $UserArea = "";
            $UserPosition = "";
            $UserParent = "";
			$UserParentId = "";
			$UserProduct = "";
         }
      }
   }
   else if ($UserId == 0) // Insert
   {
      $UserName = $_POST["UserName"];
      $EmployeeId = $_POST["EmployeeId"];
	  $WinId = $_POST["WinId"];
      $UserEmail = $_POST["UserEmail"];
      $CanApprove = $_POST["CanApprove"];
	  $UserSuper = $_POST["UserSuper"];
      $DeptId = $_POST["DeptId"];
	  $CheckInTime = "'" . $_POST["CheckInTime"] . "'";
	  if($CheckInTime == "''")
		 $CheckInTime = "NULL";
      $UserArea = $_POST["UserArea"];
      $UserPosition = $_POST["UserPosition"];
      $UserParent = $_POST["UserParent"];
	  $UserParentId = $_POST["UserParentId"];
	  $UserProduct = $_POST["UserProduct"];
      $JobGrade = 1;
      if (check_email($UserEmail) != SUCCESS)
      {
         echo "-- Email 格式错误 -- $UserEmail";
         return;
      }
      $str_query1 = "Insert into Users (UserName,EmployeeId,Email,DeptId,CanApprove,JobGrade,UserWId,CreatedUser,CreatedTime,EditUser,EditTime,Status,UserSuper,CheckInTime,UserArea,UserPosition,UserParent,UserParentId,UserProduct)" 
                  . " VALUES('$UserName','$EmployeeId','$UserEmail',$DeptId,$CanApprove,$JobGrade,'$WinId',$user_id,now(),$user_id,now(),1,$UserSuper,$CheckInTime,'$UserArea','$UserPosition','$UserParent','$UserParentId','$UserProduct')" ;
      if(mysqli_query($link, $str_query1))
      {
         echo "0";
         return;
      }
      else
      {
         echo -__LINE__ . $str_query1;
         return;
      }
   }
   else // Update
   {
      $UserName = $_POST["UserName"];
      $EmployeeId = $_POST["EmployeeId"];
	  $WinId = $_POST["WinId"];
      $UserEmail = $_POST["UserEmail"];
      $CanApprove = $_POST["CanApprove"];
	  $UserSuper = $_POST["UserSuper"];
      $DeptId = $_POST["DeptId"];
	  //$CheckInTime = $_GET["CheckInTime"];
	  $CheckInTime = "'" . $_POST["CheckInTime"] . "'";
	  if($CheckInTime == "''")
		 $CheckInTime = "NULL";
      $UserArea = $_POST["UserArea"];
      $UserPosition = $_POST["UserPosition"];
      $UserParent = $_POST["UserParent"];
	  $UserParentId = $_POST["UserParentId"];
	  $UserProduct = $_POST["UserProduct"];
      $JobGrade = 1;
      if (check_email($UserEmail) != SUCCESS)
      {
         echo "-- Email 格式错误 -- $UserEmail";
         return;
      }
      //TODO EditUser=UserId
      $str_query1 = "Update Users set UserName='$UserName', EmployeeId='$EmployeeId', UserWId='$WinId', CheckInTime = $CheckInTime,  Email='$UserEmail', DeptId=$DeptId, CanApprove=$CanApprove, JobGrade=$JobGrade, EditTime=now(), UserSuper = $UserSuper,UserArea='$UserArea',UserPosition='$UserPosition',UserParent='$UserParent',UserParentId='$UserParentId',UserProduct='$UserProduct' where UserId=$UserId";
      if(mysqli_query($link, $str_query1))
      {
         echo "0";
         return;
      }
      else
      {
         echo -__LINE__ . $str_query1;
         return;
      }
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
<script type="text/javascript" src="../lib/jquery.min.js"></script>
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
   $('#tt').tree('collapseAll');
   window.setTimeout("expandTo()",1000);
}
//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyUsersContent(UserId)
{
   UserName = document.getElementsByName("UserNameModify")[0].value.trim();
   EmployeeId = document.getElementsByName("EmployeeId")[0].value.trim();
   WinId = document.getElementsByName("WinId")[0].value.trim();
   UserEmail = document.getElementsByName("UserEmailModify")[0].value.trim();
   CheckInTime = document.getElementsByName("CheckInTimeModify")[0].value.trim();
   UserArea = document.getElementsByName("UserArea")[0].value.trim();
   UserPosition = document.getElementsByName("UserPosition")[0].value.trim();
   UserParent = document.getElementsByName("UserParent")[0].value.trim();
   UserParentId = document.getElementsByName("UserParentId")[0].value.trim();
   UserProduct = document.getElementsByName("UserProduct")[0].value.trim();
   DeptId = getSelectedId();
   
   var searchUsersRadio = 0;
   if (document.getElementById("searchUsersRadio1").checked == true)
   {
      searchUsersRadio = 1; 
   }
   
   var searchUserSupersRadio = 0;
   if (document.getElementById("searchUserSupersRadio1").checked == true)
   {
      searchUserSupersRadio = 1; 
   }
   
   if (UserName.length == 0 || EmployeeId.length == 0 || UserEmail.length == 0)
   {
      alert("用户名称，工号，及用户邮箱不可为空白");
      return;
   }
   
   if (CheckInTime.length > 0)
   {
      if (CheckInTime.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(CheckInTime))
      {
         alert("日期格式必须为 yyyy/mm/dd " + CheckInTime);
         return;
      }
   }
  
   str = "cmd=write&UserId=" + UserId;
   url_str = "../User/Users_modify.php?";

   //alert(url_str + str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str + str,
	  data:{
         UserName:UserName,
         EmployeeId:EmployeeId,
         WinId:WinId,
         UserSuper:searchUserSupersRadio,
		 CheckInTime:CheckInTime,
		 UserArea:UserArea,
		 UserPosition:UserPosition,
		 UserParent:UserParent,
		 UserEmail:UserEmail,
		 CanApprove:searchUsersRadio,
		 DeptId:DeptId,
		 UserParentId:UserParentId,
		 UserProduct:UserProduct
      },
      cache: false,
	  dataType: 'json',
      success: function(res)
      {
         //alert("Data Saved: " + res);
		 res = String(res);
         if (res.match(/^-/))  //failed
         {
            alert("用户新增/修改失败 " + res);
         }
         else  //success
         {
            alert("用户新增/修改成功，页面关闭后请自行刷新");
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
  <li class="active"><?php echo $TitleStr; ?></li>
</ol>
<div id="content">
<form class="cmxform form-horizontal tasi-form searchField" id="commentForm" method="get" action="#" novalidate="novalidate">
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">用户名称：</label>
		<div class="col-lg-7">
			<input class=" form-control"  name="UserNameModify" size=50 value="<?php echo $UserName;?>" type="text">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">工号：</label>
		<div class="col-lg-7">
			<Input class=" form-control" type=text name=EmployeeId size=50 value="<?php echo $EmployeeId;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">用户WinID：</label>
		<div class="col-lg-7">
			<Input class=" form-control" type=text name=WinId size=50 value="<?php echo $WinId;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">用户邮箱：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserEmailModify size=50 value="<?php echo $UserEmail;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">区域：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserArea size=50 value="<?php echo $UserArea;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">岗位：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserPosition size=50 value="<?php echo $UserPosition;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">产品：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserProduct size=50 value="<?php echo $UserProduct;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">上级主管：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserParent size=50 value="<?php echo $UserParent;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">上级工号：</label>
		<div class="col-lg-7">
			<Input class="form-control" type=text name=UserParentId size=50 value="<?php echo $UserParentId;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">员工入职日期：</label>
		<div class="col-lg-7">
			<input class="form-control date form_datetime" id="from0" type="text" name="CheckInTimeModify" value="<?php echo $CheckInTime;?>"/>
			
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">是否为审批者 ：</label>
		<div class="col-lg-7">
			<div class="radio-inline">
				<label class="cr-styled" for="searchUsersRadio1">
					<input type="radio" id="searchUsersRadio1" name="CanA" value="option1" <?php if ($CanApprove==1) echo "checked"; ?>> 
					<i class="fa"></i>
					是
				</label>
			</div>
			<div class="radio-inline">
				<label class="cr-styled" for="searchUsersRadio2">
					<input type="radio" id="searchUsersRadio2" name="CanA" value="option1" <?php if ($CanApprove==0) echo "checked"; ?>> 
					<i class="fa"></i>
					否
				</label>
			</div>
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">是否为超级管理者 ：</label>
		<div class="col-lg-7">
			<div class="radio-inline">
				<label class="cr-styled" for="searchUserSupersRadio1">
					<input type="radio" id="searchUserSupersRadio1" name="USuper" value="option1" <?php if ($UserSuper==1) echo "checked"; ?>> 
					<i class="fa"></i>
					是
				</label>
			</div>
			<div class="radio-inline">
				<label class="cr-styled" for="searchUserSupersRadio2">
					<input type="radio" id="searchUserSupersRadio2" name="USuper" value="option1" <?php if ($UserSuper==0) echo "checked"; ?>> 
					<i class="fa"></i>
					否
				</label>
			</div>
		
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">选择部门：</label>
		<div class="col-lg-7">
			
	<div>
		<a id=displayExpandToButton href="#" class="easyui-linkbutton" onclick="expandTo()">显示当前所属部门</a>
	</div>
	<div class="easyui-panel" style="padding:5px">
		<ul id="tt" class="easyui-tree" data-options="url:'<?php echo $web_path ?>Dept_tree_load.php',method:'get',animate:true"></ul>
	</div>
	<script type="text/javascript">
		function collapseAll(){
			$('#tt').tree('collapseAll');
		}
		function expandAll(){
			$('#tt').tree('expandAll');
		}
		function expandTo(){
			var node = $('#tt').tree('find',<?php echo $DeptId; ?>);
			$('#tt').tree('expandTo', node.target).tree('select', node.target);
			$('#displayExpandToButton').hide();
			$('#tt').tree('collapseAll');
		}
		function getSelected(){
			var node = $('#tt').tree('getSelected');
			if (node){
				var s = node.text;
				if (node.attributes){
					s += ","+node.attributes.p1+","+node.attributes.p2;
				}
				//alert(s);
            return s;
			}
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
	<?php
   if ($Status != 1)
   {
?>         
	  <div class="form-group">
		<label class="control-label col-lg-2">　</label>
		<div class="col-lg-7">
		<input class="btn btn-success" name="modifyUsersButton" type="button" value="保存" OnClick="modifyUsersContent(<?php echo $UserId;?>)">
		</div>
	</div>
<?php
   }
?>  
	
</form>

</div>

</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->