<?php
   require_once("../Problem/Problems_utility.php");

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
   $login_name = "Phantom";

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
      $resultStr =  "文档上传失败 - " . -__LINE__;
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

   $resultStr = "上传成功，文档格式转换需要数分钟";
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
<link rel="stylesheet" type="text/css" href="../css/exam.css">
<link rel="stylesheet" type="text/css" href="../css/problem.css">
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
<title>武田 - 考卷页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<Script Language=JavaScript>
function is_valid_prob_type_amount(true_false_amount, single_selection_amount, multi_selection_amount)
{
   if (true_false_amount < 0 || single_selection_amount < 0 || multi_selection_amount < 0)
   {
      alert("题目数不能为负数");
      return false;
   }
   
   total_amount = true_false_amount + single_selection_amount + multi_selection_amount;
   if (total_amount == 0)
   {
      alert("总题数不能为0");
      return false;
   }

   return true;
}

function is_valid_exam_level(easy_level_percent, hard_level_percent, mid_level_percent)
{
   if (easy_level_percent < 0 || mid_level_percent < 0 || hard_level_percent < 0)
   {
      alert("易中难比值不能有负数");
      return false;
   }
   
   total_level = easy_level_percent + mid_level_percent + hard_level_percent;
   if (total_level != 100)
   {
      alert("易中难比值相加需为100");
      return false;
   }
   
   return true;
}

function get_type_str_from_id(type_id)
{
   if (type_id == <?echo TRUE_FALSE_PROB;?>)
   {
      return "<? echo TRUE_FALSE_CHINESE;?>";
   }
   else if (type_id == <? echo SINGLE_CHOICE_PROB; ?>)
   {
      return "<? echo SINGLE_CHOICE_CHINESE;?>";
   }
   else if (type_id == <? echo MULTI_CHOICE_PROB;?>)
   {
      return "<? echo MULTI_CHOICE_CHINESE;?>";
   }
}

function get_level_str_from_id(level_id)
{
   if (level_id == <?echo EASY_LEVEL;?>)
   {
      return "<? echo EASY_LEVEL_NAME;?>";
   }
   else if (level_id == <? echo MID_LEVEL;?>)
   {
      return "<? echo MID_LEVEL_NAME;?>";
   }
   else if (level_id == <? echo HIGH_LEVEL;?>)
   {
      return "<? echo HARD_LEVEL_NAME;?>";
   }
}

function loaded() {
   functions_id = [];
 
   for (i=0; i<=50; i++)
   {
      dom = "<option value="+ i +">" + i + "</option>";
      $(dom).appendTo("#NewExamEasyLevel");
      $(dom).appendTo("#NewExamHardLevel");
   }
   

   $(".problem_type_count").click(function(){
      if ($(this).val() == 0)
      {
         $(this).val("");
      }
   });

   $(".problem_level").change(function(){
      mid_level = 100 - $("#NewExamEasyLevel").val() - $("#NewExamHardLevel").val();
      $("#problem_mid_level").val(mid_level);
      $("#problem_mid_level").html(mid_level);
      
   });
   
   $("#exam_type").change(function(){

      if ($(this).val() == 0) 
      {    
         $("#exam_answer_selections").hide();
         $("#exam_time_selections").hide();
         $("#exam_password_sections").hide();
         $("#exam_location_selections").hide();
      }
      else if ($(this).val() == 1) 
      {
         for (i=0; i<=23; i++)
         {
            dom = "<option value="+ i +">" + i + "</option>";
            $(dom).appendTo("#exam_from_hour");
            $(dom).appendTo("#exam_to_hour");
         }
         for (i=0; i<=59; i++)
         {
            dom = "<option value="+ i +">" + i + "</option>";
            $(dom).appendTo("#exam_from_min");
            $(dom).appendTo("#exam_to_min");
         }
         
         $("#exam_answer_selections").show();
         $("#exam_time_selections").show();
         $("#exam_location_selections").show();
      }
   });

   $("#exam_location").change(function(){
      if ($(this).val() == 0)
      {
         $("#exam_password_sections").hide();
      }
      else
      {
         $("#exam_password_sections").show();
      }
   });
   
   $("#genProbsButton").click(function(){
      true_false_amount = $("#NewExamTrueFalseProbType").val();
      single_selection_amount = $("#NewExamSingleSelProbType").val();
      multi_selection_amount =$("#NewExamMutiSelProbType").val();
      
      if (!is_valid_prob_type_amount(true_false_amount, single_selection_amount, multi_selection_amount))
      {
         return;
      }

      easy_level_percent = $("#NewExamEasyLevel").val();
      mid_level_percent = $("#NewExamMidLevel").val();
      hard_level_percent = $("#NewExamHardLevel").val();

      if (!is_valid_exam_level(parseInt(easy_level_percent, 10), parseInt(hard_level_percent, 10), parseInt(mid_level_percent, 10)))
      {
         return;
      }

      i = 0;
      $(".functions:checked").each(function(){
         functions_id[i++] = $(this).val();
      });
      // how to check valid functions id?
      if (functions_id.length == 0)
      {
         alert("至少需选一个分类");
         return;
      }

      $.ajax({
         beforeSend: function(){
            $(".tmp_data").remove();
            // remove error, status and problem div
         },

         method: "GET",
         url: "gen_problems.php",
         cache: false,
         data: {
                "true_false_amount": true_false_amount, 
                "single_selection_amount": single_selection_amount,
                "multi_selection_amount": multi_selection_amount,
                "easy_level_percent": easy_level_percent,
                "mid_level_percent": mid_level_percent,
                "hard_level_percent": hard_level_percent,
                "functions_id": functions_id,
                },
         dataType: "json",
         success: function(res)
         {  
            if (res.hasOwnProperty("code"))
            {
               if (res.code != <? echo SUCCESS; ?>) {
                  alert(res.message);
                  return;
               }
            }
            // show errors
            if (res.hasOwnProperty("errors")) {
               $.each(res.errors, function(key, val){
                  $("#error_template").clone().html(val).insertAfter("#error_template").removeAttr("id").addClass("tmp_data").show();
               });
            }
            if (res.hasOwnProperty("status")) {
               $("#content0").html(res.status[0]);
               $("#content1").html(res.status[1]);
               $("#content2").html(res.status[2]);
               $("#content3").html(res.status[3]);
               $("#content4").html(res.status[4]);
               $("#content5").html(res.status[5]);
               $("#status_template").show();
               /*
               $.each(res.status, function(key, val){
                  $("#status_template").clone().html(val).insertBefore("#status_template").removeAttr("id").addClass("tmp_data").show();
               });*/
            }
            
            if (res.hasOwnProperty("problems")) {
               sequence = 0;
               $.each(res.problems, function(key, val){
                  type_str = get_type_str_from_id(val.type);
                  level_str = get_level_str_from_id(val.level);

                  val_string = "<td class='problem_id' style='display:none'>" + val.id + "</td><td>" + sequence + "</td><td>" + type_str + "</td><td>" + level_str + "</td><td>" + val.desc + "</td>";
                  $("#problem_template").clone().html(val_string).insertBefore("#problem_template").removeAttr("id").addClass("tmp_data").show();
                  sequence++;
               });
               $(".exam_info").show();
               $(".problem_info").show();
            }
            
            //alert(res.errors[0]);
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         },
      });
   }); 
   
   $(".saveProbsButton").click(function(){
      // exam name
      exam_name = $("#exam_name").val();
      exam_status = $("#exam_status").val();
      exam_type = $("#exam_type").val();
      exam_answer_type = $("#exam_answer_type").val();
      exam_from_date = $("#from7").val();
      exam_from_hour = $("#exam_from_hour").val();
      exam_from_min = $("#exam_from_min").val();
      exam_expire_date = $("#exam_expire_time").val();
      exam_to_date = $("#to7").val();
      exam_to_hour = $("#exam_to_hour").val();
      exam_to_min = $("#exam_to_min").val();
      exam_password = $("#exam_password").val();
      exam_desc = $("#exam_desc").val();
      exam_location = $("#exam_location").val();
      exam_selected_functions = functions_id;
      exam_probs_id = [];
      exam_content = [$("#content0").html(), $("#content1").html(), $("#content2").html(), $("#content3").html(), $("#content4").html(), $("#content5").html()];
      from_timestamp = 0;
      to_timestamp = 0;
      expire_timestamp = new Date(exam_expire_date).getTime();
      
      if (exam_name == 0)
      {
         alert("考卷名称不能为空");
         return;
      }
      
      if (exam_name.length > 100)
      {
         alert("考卷名称不能超过100字元");
         return;
      }

      // test type
      if (exam_type == 1)
      {
         //calculate from time stamp, and end time stamp
         date_timestamp = new Date(exam_from_date).getTime();
         hour_min_timestamp = (60 * 60 * exam_from_hour + 60 * exam_from_min) * 1000;
         from_timestamp = date_timestamp + hour_min_timestamp;
         
         date_timestamp = new Date(exam_to_date).getTime();
         hour_min_timestamp = (60 * 60 * exam_to_hour + 60 * exam_to_min) * 1000;
         to_timestamp = date_timestamp + hour_min_timestamp;
         
         if (from_timestamp >= to_timestamp)
         {
            alert("考试开始时间不能大于结束时间");
            return;
         }
         
         if (expire_timestamp < to_timestamp)
         {
            alert("有效日期必须大于结束时间");
            return;
         }
         
         if (exam_location == 1)
         {
            if (exam_password.match(/[0-9][0-9][0-9][0-9]/) == null)
            {
               alert("考试密码必须为四位数的数字");
               return;
            }
         }
      }

      // collect all problems id
      $(".problem_id").each(function(){
         exam_probs_id.push($(this).html());
      });

      if (exam_selected_functions.length == 0)
      {
         alert("至少需选一个分类");
         return;
      }
      
      $.ajax({
         type: "POST",
         url: "save_exams.php",
         cache: false,
         data: {
                  "exam_name": exam_name, 
                  "exam_status": exam_status,
                  "exam_type": exam_type,
                  "exam_answer_type": exam_answer_type,
                  "exam_probs_id": exam_probs_id,
                  "exam_password": exam_password,
                  "from_timestamp": (from_timestamp/1000),
                  "to_timestamp": (to_timestamp/1000),
                  "exam_expire_timestamp": (expire_timestamp/1000),
                  "exam_desc": exam_desc,
                  "exam_content": exam_content,
                  "exam_functions_id": exam_selected_functions,
                  "exam_location": exam_location,
                },
         success: function(res) {
            if (!res.match(/^-\d+$/)) 
            {
               alert("新增考卷成功，页面关闭后请自行刷新")
               window.close();
            }
            else
            {
               alert(res);
            }

         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         },      
      });
   });
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
   <span class="bLink company"><span>新增考卷</span><span class="bArrow"></span></span>
</div>
<div id="content">

   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>题型题数</th>
         <td>是非 <Input type=text class="problem_type_count" id=NewExamTrueFalseProbType size=3 value=0>&nbsp;
             单选 <Input type=text class="problem_type_count" id=NewExamSingleSelProbType size=3 value=0>&nbsp;
             多选 <Input type=text class="problem_type_count" id=NewExamMutiSelProbType size=3 value=0>&nbsp;
         </td>
      </tr>
      <tr>
         <th>易中难比重：</th>
         <td>易<select class="problem_level" id=NewExamEasyLevel>
             </select>
             中<select class="problem_level" id=NewExamMidLevel disabled>
             <option id="problem_mid_level" selected value=100>100</option>
             </select>
             难<select class="problem_level" id=NewExamHardLevel>
             </select>
         </td>
      </tr>
      <tr>
<?php
         // Function Adaptation
         $func_type = FUNCTION_ADAPTATION;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<Input type=checkbox class=functions value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  $i++;
               }
               echo "</tr>";
            }
         }
 
         // Function Product
         $func_type = FUNCTION_PRODUCT;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";

               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<Input type=checkbox class=functions value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  $i++;
               }
               echo "</tr>";
            }
         }
 
         // Function Other
         $func_type = FUNCTION_OTHER;
         $str_query = "Select * from functions where FunctionType=$func_type";
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<Input type=checkbox class=functions value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  $i++;
               }
               echo "</tr>";
            }
         }
      ?>
      </tr>
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new"><input name="genProbsButton" id="genProbsButton" type="button" value="产生考题"></a>
         </th>
      </tr>
   </table>
   <div id="selected_functions" style="display:none"></div>
   <div class="error" id="error_template" style="display:none"></div>
   <div class="status" id="status_template" style="display:none">
      <div>是非题目题数: <u id="content0"></u></div>
      <div>单选题目题数: <u id="content1"></u></div>
      <div>多选题目题数: <u id="content2"></u></div>
      <div>简易题目题数: <u id="content3"></u></div>
      <div>中等题目题数: <u id="content4"></u></div>
      <div>困难题目题数: <u id="content5"></u></div>
   </div>

   <div class="problem_info" style="display:none">
      <table class="problems_table">
         <tr><td>题目</td></tr>
         <th>编号</th><th>题型</th><th>难易</th><th>描述</th>
         <tr id="problem_template"></tr>
      </table>
   </div>
   <table class="exam_info" style="display: none">

         <tr><td>考卷名称&nbsp;</td><td> <input type="text" size="100" id="exam_name"></td></tr>
         <tr>
            <td>是否上架&nbsp;</td>
            <td>
               <select id="exam_status">
                  <option value=0>下架</option>
                  <option value=1>上架</option>
               </select>
            </td>
         </tr>
         <tr>
            <td>类型&nbsp;</td>
            <td>
               <select id="exam_type">
                  <option value=0>模拟考试</option>
                  <option value=1>正式考试</option>
               </select>
            </td>
         </tr>
         <tr id="exam_answer_selections" style="display:none">
            <td>答案公布类型&nbsp;</td>
            <td>
               <select id="exam_answer_type">
                  <option value=1>考试交卷后公布答案</option>
                  <option value=2 selected>考试结束后公布答案</option>
               </select>
            </td>
         </tr>
         <tr id="exam_expire_time_selections">
            <td>有效日期&nbsp;</td>
            <td> <input id="exam_expire_time" type="text" name="exam_expire_time" class="from" readonly="true"></td>
         </tr>
         <tr id="exam_location_selections"  style="display: none">
            <td>考试地点&nbsp;</td>
            <td>
               <select id="exam_location">
                  <option value=0 selected>线上</option>
                  <option value=1>落地考</option>
               </select>
            </td>
         </tr>
         <tr id="exam_time_selections" style="display:none">
            <td>考试时间段&nbsp;</td>
            <td>
               <input id="from7" type="text" name="exam_from_date6" class="from" readonly="true">
               <select id="exam_from_hour"></select>
               <select id="exam_from_min"></select>
               ~
               <input id="to7" type="text" class="to" name="exam_to_date6" readonly="true">
               <select id="exam_to_hour"></select>
               <select id="exam_to_min"></select>
            </td>
         </tr>
         <tr id="exam_password_sections" style="display:none">
            <td>考卷密码&nbsp;</td>
            <td>
               <input type="text" id="exam_password" size=4>
            </td>
         </tr>
         <tr>
            <td>考卷描述&nbsp;</td>
            <td>
               <textarea id="exam_desc" rows="5" cols="50"></textarea>
            </td>
         </td>
         <tr>
         <th colspan="4" class="submitBtns">
         <a class="btn_submit_new"><input name="saveProbsButton" class="saveProbsButton" type="button" value="储存考题"></a>
         </th>
         </tr>
   </table>
</div>
</body>
</html>
