<?php
   require_once("../Problem/Problems_utility.php");
   require_once("../Exam/Exams_utility.php");

   /*
   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   */
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
   /*
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
*/

   //return value
   //define("SUCCESS", 0);
   //define("DB_ERROR", -1);

   $prob_set_num = 0;

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
   <div class="cur_problem_set problem_set" id="problem_set_0">
         <form>

         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <? $func_type = FUNCTION_OTHER; ?>
            <tr>
               <th><?echo "<label for=''>".get_func_type_name($func_type)."</label>";?></th>
            </tr>
            <tr>
               <td>
               <select class="ps_required_function">
                              <?
            // Function Other
            
            $str_query = "Select * from functions where FunctionType=$func_type";
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
               <select>
               </td>
            </tr>
            <tr>
               <th>OBU</th>
            </tr>
            <tr>
               <td>
                  <select class="ps_is_obu_or_not">
                     <option value=1>是</option>
                     <option value=0 selected>否</option>
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
            
            $str_query = "Select * from functions where FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=ps_product_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
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
            
            $str_query = "Select * from functions where FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=ps_adapation_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                     $i++;
                  }
               
               }
            }
            ?>
               </td>
            </tr>
         </table>
      </form>
                                          <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="NewExamSingleSelProbType" class="control-label">单选题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamSingleSelProbType" value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMutiSelProbType" class="control-label">多选题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamMutiSelProbType" value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamTrueFalseProbType" class="control-label">是非题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamTrueFalseProbType" value=0>
                                    </div>
                                    </div>

                                  
                                    
                                    
                                    <div class="col-md-4">
                                       <div class="panel panel-default">
                                          <div class="panel-heading"><h3 class="panel-title">难易配比</h3></div>
                                          <div class="panel-body">
                                    <div class="form-group">
                                        <label for="NewExamEasyLevel">易 %</label>
                              <select class="problem_level form-control NewExamEasyLevel" data-index="0"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMidLevel">中 %</label>
                              <select class="problem_level form-control NewExamMidLevel" data-index="0" disabled>
                                 <option class="problem_mid_level" selected value=100>100</option>
                              </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamHardLevel">难 %</label>
                              <select class="problem_level form-control NewExamHardLevel" data-index="0"></select>
                                    </div>

                                          </div>
                                       </div>

                                            </div>
         </div>
         <div class="problem_set" id="problem_set_template" style="display:none">
         <form>

         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <? $func_type = FUNCTION_OTHER; ?>
            <tr>
               <th><?echo "<label for=''>".get_func_type_name($func_type)."</label>";?></th>
            </tr>
            <tr>
               <td>
               <select class="ps_required_function">
                              <?
            // Function Other
            
            $str_query = "Select * from functions where FunctionType=$func_type";
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
               <select>
               </td>
            </tr>
            <tr>
              <th>OBU</th>
            </tr>
            <tr>
               <td>
                  <select class="ps_is_obu_or_not">
                     <option value=1>是</option>
                     <option value=0 selected>否</option>
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
            
            $str_query = "Select * from functions where FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=ps_product_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
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
            
            $str_query = "Select * from functions where FunctionType=$func_type";
            if($result = mysqli_query($link, $str_query))
            {
               $row_number = mysqli_num_rows($result);
               if ($row_number > 0)
               {
    
                  $i = 0;
                  while ($i < $row_number)
                  {
                     $row = mysqli_fetch_assoc($result);
                     echo "<label class='cr-styled'><Input type=checkbox class=ps_adapation_functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                     $i++;
                  }
               
               }
            }
            ?>
               </td>
            </tr>
         </table>
      </form>
                                          <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="NewExamSingleSelProbType" class="control-label">单选题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamSingleSelProbType" value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMutiSelProbType" class="control-label">多选题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamMutiSelProbType" value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamTrueFalseProbType" class="control-label">是非题数量</label>
                                 <Input type=text class="problem_type_count form-control NewExamTrueFalseProbType" value=0>
                                    </div>
                                    </div>

                                  
                                    
                                    
                                    <div class="col-md-4">
                                       <div class="panel panel-default">
                                          <div class="panel-heading"><h3 class="panel-title">难易配比</h3></div>
                                          <div class="panel-body">
                                    <div class="form-group">
                                        <label for="NewExamEasyLevel">易 %</label>
                              <select class="problem_level form-control NewExamEasyLevel"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMidLevel">中 %</label>
                              <select class="problem_level form-control NewExamMidLevel" disabled>
                                 <option class="problem_mid_level" selected value=100>100</option>
                              </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamHardLevel">难 %</label>
                              <select class="problem_level form-control NewExamHardLevel"></select>
                                    </div>

                                          </div>
                                       </div>

                                            </div>
         </div>
                                     <div class="col-md-12" id="problem_set_btn_template"  style="display: none;">
                                          <a class="btn_submit_new problem_set_btn"><input name="problem_set_button" type="button"></a>
                                       </div> 

                                     <div class="col-md-5">
                                          <a class="btn_submit_new next_problem_set"><input name="next_problem_set" type="button" value="新增产生题目规则"></a>
                                       </div>  
    </body>
</html>