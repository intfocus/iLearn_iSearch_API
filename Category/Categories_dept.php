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
   
   $TitleStr = "添加部门";
   
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
   
   //get data from client
   $cmd;
   $NewId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($CategoryId = check_number($_GET["CategoryId"])) == SYMBOL_ERROR)
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
      $str_query1 = "Select DeptList, CategoryName from Categories where CategoryId=$CategoryId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $DeptList = $row["DeptList"];
			$CategoryName = $row["CategoryName"];
         }
         else
         {
            $DeptList = "All";
			$CategoryName = $row["CategoryName"];
         }
      }
	  $TitleStr = $TitleStr . "(" . $CategoryName . ")";
   }
   else // Update
   {
      $DeptList = $_POST["DeptList"];
      $str_query1 = "Update Categories set DeptList='$DeptList' where CategoryId=$CategoryId";
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
<title>武田 - 分类页面</title>
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
   window.setTimeout("expandToDept()", 2000);
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyCategoriesContent(CategoryId)
{
   DeptList = getCheckedDept();
   
   url_str = "Categories_dept.php?cmd=write&CategoryId=" + CategoryId;

   // alert(url_str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str,
      data:{
         DeptList:DeptList
      },
      cache: false,
      dataType: 'json',
      success: function(res)
      {
         //alert("Data Saved: " + res);
         res = String(res);
         if (res.match(/^-\d+$/))  //failed
         {
            alert(MSG_OPEN_CONTENT_ERROR);
         }
         else  //success
         {
            alert("新增部门成功！");
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
		<label for="cname" class="control-label col-lg-2">有权限部门：</label>
		<div class="col-lg-7">
            <div>
               <a id=displayExpandToDeptButton href="#" class="easyui-linkbutton" onclick="expandToDept()">显示当前所属部门</a>
            </div>
            <div class="easyui-panel" style="padding:5px">
               <ul id="depttree" class="easyui-tree" data-options="url:'<?php echo $web_path ?>Dept_tree_load.php',method:'get',animate:true,checkbox:true"></ul>
            </div>
            <script type="text/javascript">
               function expandToDept(){
                  $('#depttree').tree('collapseAll');
                  $('#displayExpandToDeptButton').hide();
                  var dlstr = "<?php echo $DeptList; ?>";
                  var dlstr1 = dlstr.substring(1,dlstr.length-1);
                  var dlstr_array = dlstr1.split(",,");
                  for(var m=0; m<dlstr_array.length;m++)
                  {
					 try{
                        var node = $('#depttree').tree('find',Number(dlstr_array[m]));
                        $('#depttree').tree('check', node.target);
					 }
					 catch(e)
					 {}
                  }
                  $('#depttree').tree('expandToDept', node.target);
                  
               }
               
               function getCheckedDept(){
                  var nodes = $('#depttree').tree('getChecked');
                  var s = '';
                  for(var i=0; i<nodes.length; i++){
                     if (s != '') s += ',,';
                     s += nodes[i].id;
                  }
                  return ',' + s + ',';
               }
            </script>    
</div>
</div>   
	  <div class="form-group">
		<label class="control-label col-lg-2">　</label>
		<div class="col-lg-7">
		<input class="btn btn-success" name="modifyCoursePacketsButton" type="button" value="保存" OnClick="modifyCategoriesContent(<?php echo $CategoryId;?>)">
		</div>
	</div>
  </form>
</div>
</div>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->