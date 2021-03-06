<?php
require_once("Users_utility.php");

require_once("../PHPExcel/Classes/PHPExcel.php");

define("FILE_NAME", "../DB.conf");
define("SUCCESS", 0);
define("DELAY_SEC", 3);
define("FILE_ERROR", -2);

// $login_name = "Phantom";
session_start();
$user_id = $_SESSION["GUID"];
$login_name = $_SESSION["username"];
session_write_close();

if (file_exists(FILE_NAME))
{
   include(FILE_NAME);
}
else
{
   sleep(DELAY_SEC);
   $resultStr = FILE_ERROR . " " . __LINE__;
}

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
//define("TIME_ZONE", "Asia/Shanghai");
//define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
define("UPLOAD_FILE_NAME","upload.pdf");

//timezone
date_default_timezone_set(TIME_ZONE);

// since phpexcel maybe execute very long time, so currently set time limit to 0
set_time_limit(0);
ini_set('memory_limit', '-1');

$target_dir = "uploads/";
$file_type = pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION);
$target_file = $target_dir.time().hash('md5', $_FILES["fileToUpload"]["name"]).".$file_type";

$file_status = new UploadFileStatus();
$file_status->status = UPLOAD_SUCCESS;


 if ($_FILES['fileToUpload']['size'] == 0) {
   $file_status->status = ERR_EMPTY_FILE; 
   array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_EMPTY_FILE));
   goto err_exit;
}

if (file_exists($target_file))
{
   $file_status->status = ERR_FILE_EXIST; 
   array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_FILE_EXIST));
   goto err_exit;
} 

if (file_exists($target_file))
{
   $file_status->status = ERR_FILE_EXIST; 
   array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_FILE_EXIST));
   goto err_exit;
}


if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
{
   $file_status->status = ERR_MOVE_FILE; 
   array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_MOVE_FILE));
   goto err_exit;
}


if (!is_valid_excel_type($target_file))
{
   $file_status->status = ERR_FILE_TYPE; 
   array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_FILE_TYPE));
   goto err_exit;
}

if (($ret = read_excel_and_insert_into_database($target_file,$user_id)) != SUCCESS)
{   
   $file_status->status = $ret;
   if ($ret == ERR_UPDATE_DATABASE)
   {
      array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_UPDATE_DATABASE));
   }
   else if ($ret == ERR_INSERT_DATABASE)
   {
      array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>MSG_ERR_INSERT_DATABASE));
   }
}
  
err_exit:
  
function is_valid_excel_type($file_path)
{
   //only accept excel2007, and 2003 format
   $valid_types = array('Excel2007', 'Excel5');
   
   foreach($valid_types as $type)
   {
      $reader = PHPExcel_IOFactory::createReader($type);
      if ($reader->canRead($file_path))
      {
         return true;
      }
   }
   
   return false;
}

function read_excel_and_insert_into_database($target_file,$userid)
{
   // return {"status":, error:[{"line":"1", "message":"xxx error"},{"line":"", "message":""}, ...]}

   $users = array();

   global $file_status;

   // load file
   try
   {
      $input_file_type = PHPExcel_IOFactory::identify($target_file);
      $reader = PHPExcel_IOFactory::createReader($input_file_type);
      $excel = $reader->load($target_file);
   }
   catch (Exception $e)
   {
      $file_status->status = ERR_FILE_LOAD; 
      array_push($file_status->errors, array("sheet"=>0, "lines"=>0, "message"=>$e->getMessage()));
      return $file_status->status;
   }
 
   // parse file
   $sheet_count = $excel->getSheetCount();
   
   for ($cur_sheet=0; $cur_sheet < $sheet_count; $cur_sheet++)
   {
      $sheet = $excel->getSheet($cur_sheet);
      $sheet_title = $sheet->getTitle();
      //print_r($sheet_title);
      if ($sheet_title == "上传名单说明")
      {
         continue;
      }
      // if sheet name is xxxx, skip it
      $highest_row = $sheet->getHighestRow();
      $highest_col = count($file_status->upload_user_syntax);

      $tmp = array();

      for ($col=0; $col<=$highest_col; $col++)
      {
         array_push($tmp, trim($sheet->getCellByColumnAndRow($col, 1)->getValue()));
      }
      if (!is_valid_syntax_import_file($tmp))
      {
         $file_status->status = ERR_FILE_LOAD;
         array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>0, "UserName"=>"", "EmployeeId"=>"", "message"=>MSG_ERR_FILE_CONTENT_SYNTAX));
         return $file_status->status;
      }
      
      for ($row=2; $row<=$highest_row; $row++)
      {
         $tmp = array();
         $functions = array();
         for ($col=0; $col<=$highest_col; $col++)
         {
            array_push($tmp, trim($sheet->getCellByColumnAndRow($col, $row)->getValue()));
         }
         if (is_empty_row($tmp))
         {
            continue;
         }
         
         $cur_user = new UploadUser($tmp);

         if (!is_correct_user_eid_format($cur_user->EmployeeId))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=>MSG_ERR_USER_EID_FORMAT));
         }
         
         
         if (!is_correct_user_name_format($cur_user->UserName))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=>MSG_ERR_USER_NAME_FORMAT));
         }
         
         if (!is_correct_user_email_format($cur_user->Email))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=>MSG_ERR_USER_EMAIL_FORMAT));
         }
         
         if (!is_correct_user_dept_format($cur_user->DeptCode))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=>MSG_ERR_USER_DEPT_FORMAT));
         }
         
         $detp_id = get_dept_id_from_database($cur_user->DeptCode);
         if ($detp_id == ERR_USER_DEPT_NOT_EXIST) 
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=> "$product_name 不存在"));
         }
         else 
         {
             $cur_user->DeptId = $detp_id;
         }
         
         $canapprove = get_canapprove_from_tf($cur_user->CanApprovestr);
         $cur_user->CanApprove = $canapprove;
         
         if (is_user_exist($cur_user))
         {
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "UserName"=>$cur_user->UserName, "EmployeeId"=>$cur_user->EmployeeId, "message"=> "此用户系统中已存在！"));
         }
         else {
            array_push($users, $cur_user);
         }
      }
   }

   if ($file_status->status == UPLOAD_SUCCESS)
   {
      return write_into_database($users,$userid);
   }
   else
   {  
      return $file_status->status;
   }
}

function get_canapprove_from_tf($canapprove)
{
   if ($canapprove == "")
   {
      return 0;
   }
   else if ($canapprove == "是")
   {
      return 1;
   }
   else if ($canapprove == "否")
   {
      return 0;
   }
}

function get_dept_id_from_database($dept_name)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }
   
   $str_query = "Select * from depts where DeptName='$dept_name'";
   if ($result = mysqli_query($link, $str_query))
   {
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         $row = mysqli_fetch_assoc($result);
         return $row["DeptId"];
      }
   }
   return ERR_USER_DEPT_NOT_EXIST;
}

function get_function_id_from_database($func_name)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }
   
   $str_query = "Select * from functions where FunctionName='$func_name'";
   if ($result = mysqli_query($link, $str_query))
   {
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         $row = mysqli_fetch_assoc($result);
         return $row["FunctionId"];
      }
   }
   return ERR_PROB_FUNC_NOT_EXIST;
}

function write_into_database($users,$userid)
{
   foreach ($users as $user)
   {
      $ret = insert_new_user($user,$userid);
       // if (is_problem_exist($problem))
       // {
          // $ret = update_old_problem($problem);
       // }
       // else
       // {
          // $ret = insert_new_problem($problem);
       // }
       if ($ret != SUCCESS)
       {
         return $ret;
       }
   }
   return SUCCESS;
}

function is_user_exist($user)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }
   
   $str_query = "Select * from users where EmployeeId='$user->EmployeeId'";
   if ($result = mysqli_query($link, $str_query))
   {
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         return true;
      }
      else
      {
         return false;
      }
   }
}

function update_old_problem($problem)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }

   $selA = $problem->selections[0];
   $selB = $problem->selections[1];
   $selC = $problem->selections[2];
   $selD = $problem->selections[3];
   $selE = $problem->selections[4];
   $selF = $problem->selections[5];
   $selG = $problem->selections[6];
   $selH = $problem->selections[7];
   
   $str_query = <<<EOD
                Update problems set ProblemType=$problem->type, ProblemSelectA='$selA',
                ProblemSelectB='$selB', ProblemSelectC='$selC',ProblemSelectD='$selD',
                ProblemSelectE='$selE',ProblemSelectF='$selF', ProblemSelectG='$selG',
                ProblemSelectH='$selH', ProblemAnswer='$problem->answer',
                ProblemCategory='$problem->functions_str', ProblemLevel=$problem->level,
                EditTime = now() 
                where ProblemDesc='$problem->desc' AND ProblemMemo='$problem->memo'
EOD;

   if(mysqli_query($link, $str_query))
   {
      return SUCCESS;
   }
   else
   {
      return ERR_UPDATE_DATABASE;
   }   
}

function  insert_new_user($user,$userid)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }
   
   $str_query = <<<EOD
             INSERT INTO users (UserName,Email,DeptId,Status,CanApprove,JobGrade,CreatedUser,CreatedTime,EditUser,EditTime
             ,EmployeeId,UserWId,UserArea,UserPosition,UserProduct,UserParent,UserParentId,DeptCode,CanApprovestr,UserSuper
             ,CheckInTime,PositionCode) VALUES('$user->UserName', '$user->Email',$user->DeptId,1,$user->CanApprove,0,$userid,now()
             ,$userid,now(),'$user->EmployeeId','$user->UserWId','$user->UserArea','$user->UserPosition','$user->UserProduct'
             ,'$user->UserParent','$user->UserParentId','$user->DeptCode','$user->CanApprovestr',0,'$user->CheckInTime'
             ,'$user->PositionCode')
EOD;
      
   if(mysqli_query($link, $str_query))
   {
      return SUCCESS;
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      return ERR_INSERT_DATABASE;
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
<link rel="stylesheet" type="text/css" href="../css/problem.css">
<link rel="stylesheet" type="text/css" href="../css/exam.css">
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
<title>武田 - 上传名单页面</title>
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
  <li class="active">上传名单</li>
</ol>
</div>
<div id="content">
<?
   if ($file_status->status == SUCCESS)
   {
      if(count($file_status->errors) > 0)
      {
         echo "<script>alert('题目新增成功，有部门题目需要修改。请记录行号手动修改！');</script>";
      }
      else
      {
         echo "<script>alert('题目新增成功，页面关闭后请自行刷新');window.close();</script>";
      }
   }
?>
<?if ($file_status->status != SUCCESS)
   {?>
   <div class="problem_info, error_info">
      <h1>名单</h1>
      <table class="problems_table">
         <th style="width:5%">页签</th><th style="width:5%">行</th><th>员工姓名</th><th>员工号</th><th>错误</th>
      <? foreach ($file_status->errors as $error)
            {?>
               <tr><td><?echo $error["sheet"];?></td><td><?echo $error["lines"];?></td><td><?echo $error["UserName"];?></td><td><?echo $error["EmployeeId"]; ?></td><td><?echo $error["message"];?></td></tr>
         <? }?>
      </table>
   </div>
<? }?>
<?if ($file_status->status == SUCCESS)
   {
      if(count($file_status->errors) > 0)
      {?>
      <div class="problem_info, error_info">
         <h1>名单</h1>
         <table class="problems_table">
            <th style="width:5%">页签</th><th style="width:5%">列</th><th>员工姓名</th><th>员工号</th><th>错误</th>
            <? foreach ($file_status->errors as $error)
               {?>
                  <tr><td><?echo $error["sheet"];?></td><td><?echo $error["lines"];?></td><td><?echo $error["UserName"];?></td><td><?echo $error["EmployeeId"]; ?></td><td><?echo $error["message"];?></td></tr>
            <? }?>
         </table>
      </div>
<? }}?>
</div>
</body>
</html>