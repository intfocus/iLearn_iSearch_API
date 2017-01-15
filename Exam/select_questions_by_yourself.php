<?php
   require_once("../Problem/Problems_utility.php");
   require_once("../Exam/Exams_utility.php");


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
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);


   //return value
   //define("SUCCESS", 0);
   define("DB_ERROR", -1);

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      $resultStr =  "文档上传失败 - " . -__LINE__;
   }
?>

<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">

<link rel="stylesheet" type="text/css" href="../css/exam.css">
<link rel="stylesheet" type="text/css" href="../css/problem.css">
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->


   
		


        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
          <script src="../newui/js/html5shiv.js"></script>
          <script src="../newui/js/respond.min.js"></script>
        <![endif]-->
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<!-- BEG_ORISBOT_NOINDEX -->
</head>
<body>

         <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <? $func_type = FUNCTION_OTHER; ?>
            <tr>
               <th><?echo "<label for=''>".get_func_type_name($func_type)."</label>";?></th>
            </tr>
            <tr>
               <td>
               <select id="select_specific_problem_set">
				  <label class='cr-styled'><option value="0"><i class='fa'></i>全部</label>
                              <?
            // Function Other
            
            $str_query = "Select * from functions where Status = 1 and FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><option value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                     $i++;
                  }
               
               }
            }
            ?>
               </select>
               </td>
            </tr>
            <tr>
               <th>OBL</th>
            </tr>
            <tr>
               <td>
                  <select id="is_obu_or_not">
				     <option value=2 selected>不限</option>
                     <option value=1>是</option>
                     <option value=0>否</option>
                  </select>
               </td>
            </tr>
                      
            <tr>
                 <? $func_type = FUNCTION_PRODUCT; ?>
               <th><?echo "<label for=''>".get_func_type_name($func_type)."</label>";?></th>
            </tr>
            <tr>
               <td>
                              <?
            // Function Other
            
            $str_query = "Select * from functions where Status = 1 and FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=product_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                     $i++;
                  }
               
               }
            }
            ?>
               </td>

            </tr>
             <tr>
                 <? $func_type = FUNCTION_ADAPTATION;; ?>
               <th><?echo "<label for=''>".get_func_type_name($func_type)."</label>";?></th>
             </tr>
             <tr>  
               <td>
                              <?
            // Function Other
            
            $str_query = "Select * from functions where Status = 1 and FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=adapation_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                     $i++;
                  }
               
               }
            }
            ?>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchExamProbs"><input name="searchExamProbsButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>

         </table>
      </form>
      <div class="select_problem_info">
         <table class="table">
            <th></th><th>编号</th><th>题型</th><th>难易</th><th>描述</th><th>上传日期</th>
            <tr id="select_problem_template" style="display: none"></tr>
         </table>
      </div>
</html>