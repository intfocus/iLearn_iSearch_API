<?php
require_once("Problems_utility.php");

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
define("TIME_ZONE", "Asia/Shanghai");
define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
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

if (($ret = read_excel_and_insert_into_database($target_file)) != SUCCESS)
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

function read_excel_and_insert_into_database($target_file)
{
   // return {"status":, error:[{"line":"1", "message":"xxx error"},{"line":"", "message":""}, ...]}

   $problems = array();

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
      if ($sheet_title == "上传题库说明")
      {
         continue;
      }
      // if sheet name is xxxx, skip it
      $highest_row = $sheet->getHighestRow();
      $highest_col = count($file_status->upload_problem_syntax);

      $tmp = array();

      for ($col=0; $col<=$highest_col; $col++)
      {
         array_push($tmp, trim($sheet->getCellByColumnAndRow($col, 1)->getValue()));
      }
      if (!is_valid_syntax_import_file($tmp))
      {
         $file_status->status = ERR_FILE_LOAD;
         array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>0, "message"=>MSG_ERR_FILE_CONTENT_SYNTAX));
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
         
         $cur_problem = new UploadProblem($tmp);

         if (!is_correct_prob_type_format($cur_problem->type))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=>MSG_ERR_PROB_TYPE_FORMAT));
         }
         
         if (!is_correct_prob_desc_format($cur_problem->desc))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=>MSG_ERR_PROB_DESC_FORMAT));
         }
         
         if (!is_correct_prob_level_format($cur_problem->level))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=>MSG_ERR_PROB_LEVEL_FORMAT));
         }
         
         if (!is_correct_prob_answer_format($cur_problem->answer, $cur_problem->selections, $cur_problem->type))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=>MSG_ERR_PROB_ANSWER_FORMAT));
         }

         if (!is_correct_prob_selection_format($cur_problem->selections, $cur_problem->type))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=>MSG_ERR_PROB_SELECTOR_FORMAT));
         }

         // if this sheet is OBL, insert OBL function name to $cur_problem->category_product
         if ($sheet_title == "OBL")
         {
            print_r("OBL");
            array_push($cur_problem->category_product, "OBL");
         } 

         foreach ($cur_problem->category_product as $product_name)
         {
            $func_id = get_function_id_from_database($product_name);
            if ($func_id == ERR_PROB_FUNC_NOT_EXIST) 
            {
               $file_status->status = ERR_FILE_LOAD;
               array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=> "$product_name 不存在"));
            }
            else
            {
               array_push($functions, $func_id);
            }
         }

         foreach ($cur_problem->category_adaptaion as $adaptation_name)
         {
            $func_id = get_function_id_from_database($adaptation_name);
            if ($func_id == ERR_PROB_FUNC_NOT_EXIST) 
            {
               $file_status->status = ERR_FILE_LOAD;
               array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=> "$adaptation_name 不存在"));
            }
            else
            {
               array_push($functions, $func_id);
            }
         }

         foreach ($cur_problem->problem_category as $category_name)
         {
            $func_id = get_function_id_from_database($category_name);
            if ($func_id == ERR_PROB_FUNC_NOT_EXIST) 
            {
               $file_status->status = ERR_FILE_LOAD;
               array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=> "$category_name 不存在"));
            }
            else
            {
               array_push($functions, $func_id);
            }
         }

         if (is_no_any_functions($functions))
         {
            $file_status->status = ERR_FILE_LOAD;
            array_push($file_status->errors, array("sheet"=>$cur_sheet, "lines"=>$row, "message"=> "至少需要有一个分类"));
         }
         
         $cur_problem->functions_str = output_category_str_from_func_array($functions);
         array_push($problems, $cur_problem);
      }
   }

   if ($file_status->status == UPLOAD_SUCCESS)
   {
      return write_into_database($problems);
   }
   else
   {  
      return $file_status->status;
   }
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

function write_into_database($problems)
{
   foreach ($problems as $problem)
   {
       if (is_problem_exist($problem))
       {
          $ret = update_old_problem($problem);
       }
       else
       {
          $ret = insert_new_problem($problem);
       }
       if ($ret != SUCCESS)
       {
         return $ret;
       }
   }
   return SUCCESS;
}

function is_problem_exist($problem)
{
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link) 
   {   
      die(MSG_ERR_CONNECT_TO_DATABASE);
   }
   
   $str_query = "Select * from problems where ProblemDesc='$problem->desc' AND ProblemMemo='$problem->memo'";
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

function  insert_new_problem($problem)
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
                INSERT INTO problems (ProblemType,ProblemDesc,ProblemSelectA,ProblemSelectB,ProblemSelectC,
                 ProblemSelectD,ProblemSelectE,ProblemSelectF,ProblemSelectG,ProblemSelectH,
                 ProblemAnswer,ProblemCategory,ProblemLevel,ProblemMemo,Status,CreatedTime,EditTime) VALUES
                ($problem->type, '$problem->desc',
                 '$selA','$selB','$selC','$selD','$selE','$selF','$selG','$selH',
                 '$problem->answer','$problem->functions_str',$problem->level,'$problem->memo', 1, now(), now())
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
<!-- End of tree view -->
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<title>武田 - 题目页面</title>
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
   <span class="bLink company"><span>上传题目</span><span class="bArrow"></span></span>
</div>
<div id="content">
<?
   if ($file_status->status == SUCCESS)
   {
      echo "<script>alert('题目新增/修改成功，页面关闭后请自行刷新');window.close();</script>";
   }
?>
<?if ($file_status->status != SUCCESS)
   {?>
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <form enctype="multipart/form-data" action="check_problem_upload.php" method="POST" enctype="multipart/form-data">
      
      <tr>
         <th>选取上传文档：</th>
         <td>
            <Input type=file size=50 name="fileToUpload" />
         </td>
      </tr>
      <tr>
         <th colspan="2" class="submitBtns">
               <input type="submit" value="上传文档" name="submit">
         </th>
      </tr>      
      </Form>
   </table>
   <div class="problem_info, error_info">
      <h1>题目</h1>
      <table class="problems_table">
         <th style="width:5%">页签</th><th style="width:5%">列</th><th>錯誤</th>
      <? foreach ($file_status->errors as $error)
            {?>
               <tr><td><?echo $error["sheet"];?></td><td><?echo $error["lines"];?></td><td><?echo $error["message"];?></td></tr>
         <? }?>
      </table>
   </div>
<? }?>
</div>
</body>
</html>