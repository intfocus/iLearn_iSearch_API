<?php

   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("Courseware_ERROR", -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo Courseware_ERROR;
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
   
   $datasyz = array();
   $datacpmc = array();
   class StuFunction{
      public $functionId;
      public $functionName;
      public $createdTime;
   }
   
   $str_functionsyz="select FunctionId, FunctionName, CreatedTime from functions where FunctionType=1 and Status=1";
   if($rs = mysqli_query($link, $str_functionsyz)){
      while($row = mysqli_fetch_assoc($rs)){
         $syz = new StuFunction();
         $syz->functionId = $row["FunctionId"];
         $syz->functionName = $row["FunctionName"];
         $syz->createdTime = $row["CreatedTime"];
         array_push($datacpmc,$syz);
      }
   }
   
   $str_functioncpmc="select FunctionId, FunctionName, CreatedTime from functions where FunctionType=2 and Status=1";
   if($rs = mysqli_query($link, $str_functioncpmc)){
      while($row = mysqli_fetch_assoc($rs)){
         $cpmc = new StuFunction();
         $cpmc->functionId = $row["FunctionId"];
         $cpmc->functionName = $row["FunctionName"];
         $cpmc->createdTime = $row["CreatedTime"];
         array_push($datasyz,$cpmc);
      }
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
   $CoursewareId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($CoursewareId = check_number($_GET["CoursewareId"])) == SYMBOL_ERROR)
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
      $str_query1 = "Select * from Coursewares where CoursewareId=$CoursewareId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);         
         
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $CoursewareId = $row["CoursewareId"];
            $CoursewareName = $row["CoursewareName"];
            $CoursewareDesc = $row["CoursewareDesc"];
            $PAList = $row["PAList"];
            $ProductList = $row["ProductList"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "文档修改 (只允许修改文档标题及文档说明)";
            if ($Status == 1)
               $TitleStr = "文档查看 (上架状态无法修改)";
            $CoursewareFile = $row["CoursewareFile"];
         }
         else
         {
            $CoursewareId = 0;
            $CoursewareName = "";
            $CoursewareTitle = "";
            $CoursewareDesc = "";
            $PAList = "";
            $ProductList = "";
            $CoursewareType = 1;
            $TitleStr = "文档新增";
            $Status = 0;
            $CoursewareFile = "";
         }
      }
   }
   else if ($CoursewareId == 0) // Insert
   {
      $CoursewareName = $_GET["CoursewareName"];
      $CoursewareCode = $_GET["CoursewareCode"];
      $PAList = $_GET["PAList"] == "" ? "All":$_GET["PAList"];
      $ProductList = $_GET["ProductList"] == ""?"All":$_GET["ProductList"];
      $ParentId = $_GET["ParentId"];
      $str_query1 = "Insert into Coursewares (CoursewareName,CoursewareCode,ParentId,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime,Status)" 
                  . " VALUES('$CoursewareName','$CoursewareCode',$ParentId,'$PAList','$ProductList',$user_id,now(),$user_id,now(),1)" ;
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
      $CoursewareName = $_POST["CoursewareName"];
      $CoursewareDesc = $_POST["CoursewareDesc"];
      $PAList = $_POST["PAList"];
      $ProductList = $_POST["ProductList"];
      //TODO EditUser=UserId
      $str_query1 = "Update Coursewares set CoursewareName='$CoursewareName', CoursewareDesc='$CoursewareDesc', PAList = '$PAList', ProductList = '$ProductList', EditUser=$user_id, EditTime=now() where CoursewareId=$CoursewareId";
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
<title>武田 - 文档页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<Script Language=JavaScript>
function checkCoursewareTypeModify() {
   CoursewareFile = document.getElementsByName("CoursewarePathModify")[0].value;
   var pos1 = CoursewareFile.lastIndexOf('/');
   var pos2 = CoursewareFile.lastIndexOf('\\');
   var pos  = Math.max(pos1, pos2)
   if(pos>=0)
      CoursewareFile = CoursewareFile.substring(pos+1);
   pos3 = CoursewareFile.lastIndexOf('.');
   extension = CoursewareFile.substring(pos3+1);
   
   if(extension == "pdf"){
      document.getElementById("FilePath").disabled = false;
      return;
   }
   else{
      document.getElementById("FilePath").disabled = true;
   }
   
   if(extension == "mp4"){
      document.getElementById("FilePath").disabled = false;
      return;
   }
   else{
      document.getElementById("FilePath").disabled = true;
   }
   
   if(extension == "zip"){
      document.getElementById("FilePath").disabled = false;
      return;
   }
   else{
      document.getElementById("FilePath").disabled = true;
   }
}

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
   var palstr = "<?php echo $PAList; ?>";
   var palstr1 = palstr.substring(1,palstr.length-1);
   var palstr_array = palstr1.split(",,");
   for(var i=0; i<palstr_array.length;i++)
   {
      var palcheck_array=document.getElementsByName("palist");
      for(var j=0;j<palcheck_array.length;j++)
      {
         if(palcheck_array[j].value==palstr_array[i])
         {         
            palcheck_array[j].checked=true;
         }
      }
   }
   
   var plstr = "<?php echo $ProductList; ?>";
   var plstr1 = plstr.substring(1,plstr.length-1);
   var plstr_array = plstr1.split(",,");
   for(var m=0; m<plstr_array.length;m++)
   {
      var plcheck_array=document.getElementsByName("productlist");
      for(var n=0;n<plcheck_array.length;n++)
      {
         if(plcheck_array[n].value==plstr_array[m])
         {         
            plcheck_array[n].checked=true;
         }
      }
   }
}

//***Step23 PAList and ProductList begin
function PAListStr(){
   var rusult="";
   var check_array=document.getElementsByName("palist");
   for(var i=0;i<check_array.length;i++)
   {
      if(check_array[i].checked==true)
      {
         rusult=rusult+"," + check_array[i].value + ",";
      }
   }
   return rusult;
}

function ProductListStr(){
   var rusult="";
   var check_array=document.getElementsByName("productlist");
   for(var i=0;i<check_array.length;i++)
   {
       if(check_array[i].checked==true)
       {         
          rusult=rusult+"," + check_array[i].value + ",";
       }
   }
   return rusult;
}
//***Step23 PAList and ProductList end


//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyCoursewaresContent(CoursewareId)
{
   CoursewareName = document.getElementsByName("CoursewareNameModify")[0].value.trim();
   CoursewareDesc = document.getElementsByName("CoursewareDescModify")[0].value.trim();
   
   var PAList = PAListStr();
   var ProductList = ProductListStr();
   
   if (CoursewareName.length == 0 || CoursewareDesc.length == 0)
   {
      alert("课件名称及课件备注不可为空白");
      return;
   }
   
   // 如果 CoursewareId == 0 ==> 代表新增, 走 submit 的方式
   if (CoursewareId == 0)
   {
      CoursewareFile = document.getElementsByName("CoursewarePathModify")[0].value;
      if (CoursewareFile.length == 0)
      {
         alert("课件附件不可为空白");
         return;
      }
	   var pos1 = CoursewareFile.lastIndexOf('/');
	   var pos2 = CoursewareFile.lastIndexOf('\\');
      var pos  = Math.max(pos1, pos2)
   	if(pos>=0)
   	  CoursewareFile = CoursewareFile.substring(pos+1);
      document.getElementsByName("CoursewareFile")[0].value = CoursewareFile;
      document.getElementsByName("PAListValue")[0].value = PAList;
      document.getElementsByName("ProductListValue")[0].value = ProductList;
      document.getElementsByName("uploadCoursewareForm")[0].submit();
      return;
   }
   
   // Else 代表 Update, 走 AJAX 方式
   str = "cmd=write&CoursewareName=" + encodeURIComponent(CoursewareName) + 
         "&CoursewareDesc=" + encodeURIComponent(CoursewareDesc) + "";
   url_str = "../Courseware/Coursewares_modify.php?cmd=write&CoursewareId=" + CoursewareId;

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
         CoursewareName:CoursewareName,
         CoursewareDesc:CoursewareDesc,
         PAList:PAList,
         ProductList:ProductList
      },
      cache: false,
      dataType: 'json',
      success: function(res)
      {
         //alert("Data Saved: " + res);
         res = String(res);
         if (res.match(/^-/))  //failed
         {
            alert(MSG_OPEN_CONTENT_ERROR + res);
         }
         else  //success
         {
            alert("文档新增/修改成功，页面关闭后请自行刷新");
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
<!--<div id="banner">
   <span class="bLink first"><span>后台功能名称</span><span class="bArrow"></span></span>
   <span class="bLink company"><span><?php echo $TitleStr; ?></span><span class="bArrow"></span></span>
</div>-->

<div class="container">
<ol class="breadcrumb">
  <li class="active">后台功能名称</li>
  <li class="active"><?php echo $TitleStr; ?></li>
</ol>
<div id="content">
      <form class="cmxform form-horizontal tasi-form searchField" enctype="multipart/form-data" name="uploadCoursewareForm" action="../Courseware/Coursewares_upload.php" method="POST">
         <Input type="hidden" name="CoursewareId" value="0" />
         <Input type="hidden" name="cmd" value="uploadCourseware" />
         <Input type="hidden" name="PAListValue" value="" />
         <Input type="hidden" name="ProductListValue" value="" />
<?php
   if ($CoursewareId > 0)
   {
?>   
      
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">上传文件名称：</label>
		<div class="col-lg-7">
			<Input type=text size=50 readonly="true" value="<?php echo $CoursewareFile;?>">
		</div>
	</div>
<?php
   }
   else
   {
      echo "<Input type=hidden name=CoursewareFile>";
   }
?>
      
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">课件名称：</label>
		<div class="col-lg-7">
			<Input type=text class=" form-control" name="CoursewareNameModify" size=50 value="<?php echo $CoursewareName;?>">
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">适应症：</label>
		<div class="col-lg-7">
			
<?php
for($i=0; $i<count($datasyz); $i++)
{
   $syz = $datasyz[$i];
?>
			<label class="cr-styled">
				<input  value="<?php echo $syz->functionId ?>" type="checkbox"  name="palist">
				<i class="fa"></i> 
				<?php echo $syz->functionName ?>
			</label>
<?php
}
?>

		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">产品名称：</label>
		<div class="col-lg-7">
			
<?php
for($i=0; $i<count($datacpmc); $i++)
{
   $cpmc = $datacpmc[$i];
?>
           
			<label class="cr-styled">
				<input  value="<?php echo $cpmc->functionId ?>" type="checkbox"  name="productlist">
				<i class="fa"></i> 
				<?php echo $cpmc->functionName ?>
			</label>
<?php
}
?>
		</div>
	</div>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">课件备注：</label>
		<div class="col-lg-7">
			<Textarea name="CoursewareDescModify" class=" form-control" rows=30 cols=100><?php echo $CoursewareDesc;?></Textarea>
		</div>
	</div>
<?php
   if ($CoursewareId == 0)
   {
?>
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2">选取上传课件：</label>
		<div class="col-lg-7">
			<Input type="File" class=" form-control" size=50 name="CoursewarePathModify" onchange="checkCoursewareTypeModify();"/>
		</div>
	</div>
<?php
   }
?>      
<?php
   if ($Status != 1)
   {
?>       
	<div class="form-group ">
		<label for="cname" class="control-label col-lg-2"></label>
		<div class="col-lg-7">
			
               <input id="FilePath" name="modifyCoursewaresButton" class="btn btn-success" type="button" value="保存 <?php if ($CoursewareId > 0) echo "(只允许修改文档标题及文档说明)"; ?>" OnClick="modifyCoursewaresContent(<?php echo $CoursewareId;?>)">  
		</div>
	</div>     
<?php
   }
?>
      </Form>
   </div>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->