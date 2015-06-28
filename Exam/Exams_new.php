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
   define("UPLOAD_FILE_NAME","upload.pdf");

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

        <!-- Bootstrap core CSS -->
        <link href="../newui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../newui/css/bootstrap-reset.css" rel="stylesheet">

        <!--Animation css-->
        <link href="../newui/css/animate.css" rel="stylesheet">

        <!--Icon-fonts css-->
        <link href="../newui/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="../newui/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="../newui/assets/morris/morris.css">

        <!-- sweet alerts -->
        <link href="../newui/assets/sweet-alert/sweet-alert.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="../newui/css/style.css" rel="stylesheet">
        <link href="../newui/css/helper.css" rel="stylesheet">
        <link href="../newui/css/style-responsive.css" rel="stylesheet" />
		
		
        <!--Form Wizard-->
        <link rel="stylesheet" type="text/css" href="../newui/assets/form-wizard/jquery.steps.css" />

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
          <script src="../newui/js/html5shiv.js"></script>
          <script src="../newui/js/respond.min.js"></script>
        <![endif]-->
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<title>武田 - 考卷页面</title>
<!-- BEG_ORISBOT_NOINDEX -->

<Script Language=JavaScript>
function is_valid_prob_type_amount(true_false_amount, single_selection_amount, multi_selection_amount)
{
   if (isNaN(true_false_amount) || isNaN(single_selection_amount) || isNaN(multi_selection_amount))
   {
      alert("题数必须为数字");
      return;
   }

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
 
   for (i=0; i<=50; i=i+5)
   {
      dom = "<option value="+ i +">" + i + "</option>";
      $(dom).appendTo("#NewExamEasyLevel");
      $(dom).appendTo("#NewExamHardLevel");
   }

   /*
   // when click previous button, hide #problem_info, $("#status_template").show();
   $("a[href='#previous']").click(function(){
      //$("#err_no_problem").hide();
      //$(".problem_info").hide();
      //$("#status_template").hide();
   });
   */
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
         $("#exam_ans_type").hide();
         //$("#exam_time_selections").hide();
         $("#exam_location_selections").hide();
      }
      else if ($(this).val() == 1) 
      {

         
         $("#exam_ans_type").show();
         //$("#exam_time_selections").show();
         $("#exam_location_selections").show();
      }
   });

   $("#genProbsButton").click(function(){
      $("#genProbsButton").attr("disabled", true);
      
      functions_id = [];
      
      true_false_amount = $("#NewExamTrueFalseProbType").val();
      single_selection_amount = $("#NewExamSingleSelProbType").val();
      multi_selection_amount =$("#NewExamMutiSelProbType").val();
            
      if (!is_valid_prob_type_amount(true_false_amount, single_selection_amount, multi_selection_amount))
      {
         $("#genProbsButton").removeAttr('disabled');
         return;
      }

      easy_level_percent = $("#NewExamEasyLevel").val();
      mid_level_percent = $("#NewExamMidLevel").val();
      hard_level_percent = $("#NewExamHardLevel").val();

      if (!is_valid_exam_level(parseInt(easy_level_percent, 10), parseInt(hard_level_percent, 10), parseInt(mid_level_percent, 10)))
      {
         $("#genProbsButton").removeAttr('disabled');
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
         $("#genProbsButton").removeAttr('disabled');
         return;
      }

      $.ajax({
         beforeSend: function(){
            $(".tmp_data").remove();
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
                  $("#err_no_problem").show();
                  $(".exam_info").hide();
                  $(".problem_info").hide();
                  $("#status_template").hide();
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
            }
            
            if (res.hasOwnProperty("problems")) {
               sequence = 1;
               $.each(res.problems, function(key, val){
                  type_str = get_type_str_from_id(val.type);
                  level_str = get_level_str_from_id(val.level);

                  val_string = "<td class='problem_id' style='display:none'>" + val.id + "</td><td>" + sequence + "</td><td>" + type_str + "</td><td>" + level_str + "</td><td>" + val.desc + "</td>";
                  $("#problem_template").clone().html(val_string).insertBefore("#problem_template").removeAttr("id").addClass("tmp_data").show();
                  sequence++;
               });
               $("#err_no_problem").hide();
               $(".exam_info").show();
               $(".problem_info").show();
            }
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
            
         },
         complete: function(xhr)
         {
            $("#genProbsButton").removeAttr('disabled');
         }
      });
   }); 
   
   $(".saveProbsButton").click(function(){
      exam_single_score = $("#NewExamSingleSelScore").val();
      exam_multi_score = $("#NewExamMutiSelScore").val();
      exam_true_false_score = $("#NewExamTrueFalseScore").val();
      exam_name = $("#exam_name").val();
      exam_type = $("#exam_type").val();
      exam_answer_type = $("#exam_answer_type").val();
      exam_from_date = $("#exam_begin_time").val();
      exam_from_hour = $("#exam_from_hour").val();
      exam_from_min = $("#exam_from_min").val();
      exam_duration = $("#exam_duration_time").val();
      exam_to_date = $("#exam_end_time").val();
      exam_to_hour = $("#exam_to_hour").val();
      exam_to_min = $("#exam_to_min").val();
      exam_desc = $("#exam_desc").val();
      exam_location = $("#exam_location").val();
      exam_selected_functions = functions_id;
      exam_probs_id = [];
      exam_content = [$("#content0").html(), $("#content1").html(), $("#content2").html(), $("#content3").html(), $("#content4").html(), $("#content5").html()];
      from_timestamp = 0;
      to_timestamp = 0;
<<<<<<< HEAD
      expire_timestamp = new Date(exam_expire_date).getTime();
=======

      user_id = $("#userid").val();
>>>>>>> master
      
      if (exam_single_score < 1 || exam_single_score > 5 || exam_multi_score < 1 || exam_multi_score > 5 || exam_true_false_score < 1 || exam_true_false_score > 5)
      {
         alert("分数必须为 1~5 之间的整数");
         return;
      }

      if(exam_from_date.length == 0 || exam_to_date.length == 0){
            alert("考试时间段不能为空");
            return;
      }
      
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

      if (exam_desc.length > 500)
      {
         alert("考卷描述不能超过500字元");
         return;
      }
      
      if (exam_duration.length == 0)
      {
         alert("考试长度不能为空");
         return;
      }

      if (isNaN(exam_duration) || exam_duration <= 0)
      {
         alert("考试长度必须为大于 0 的正整数");
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
                  "exam_single_score": exam_single_score,
                  "exam_multi_score": exam_multi_score,
                  "exam_true_false_score": exam_true_false_score,
                  "exam_name": exam_name, 
                  "exam_type": exam_type,
                  "exam_answer_type": exam_answer_type,
                  "exam_probs_id": exam_probs_id,
                  "from_timestamp": (from_timestamp/1000),
                  "to_timestamp": (to_timestamp/1000),
                  "exam_duration": exam_duration,
                  "exam_desc": exam_desc,
                  "exam_content": exam_content,
                  "exam_functions_id": exam_selected_functions,
                  "exam_location": exam_location,
                },
         success: function(res) {
            if (res == 0) 
            {
               alert("新增考卷成功，页面关闭后请自行刷新")
               window.close();
            }
            else
            {
               if (res == <? echo ERR_INSERT_DATABASE;?>)
               {
                  alert("无法新增，可能为已新增过之考题内容");
               }
               else if (res == <? echo ERR_SAVE_JSON_FILE;?>)
               {
                  alert("储存考卷JSON文档失败");
               }
               else if (res == <? echo ERR_PROBLEM_COUNT_NOT_ENOUGH ?>)
               {
                  alert("题目数不能为0");
               }
               else
               {
                  alert("新增考卷失败");
               }
               return;
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

<style>

.wizard > .content
{
    min-height: 470px;
	background: #fafafa;
}
</style>

</head>
<body Onload="loaded();">
<<<<<<< HEAD
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
             </select>%
             中<select class="problem_level" id=NewExamMidLevel disabled>
             <option id="problem_mid_level" selected value=100>100</option>
             </select>%
             难<select class="problem_level" id=NewExamHardLevel>
             </select>%
         </td>
      </tr>
      <tr>
=======

        <!--Main Content Start -->
        <div class="" id="content">
            
            <!-- Header -->
            <header class="top-head container-fluid">
                <button type="button" class="navbar-toggle pull-left">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                
                
                <!-- Left navbar -->
                <nav class=" navbar-default hidden-xs" role="navigation">
                    <ul class="nav navbar-nav">

                        <li><a href="#"><?php echo date('Y-m-d',time()); ?></a></li>
                    </ul>
                </nav>
                
                <!-- Right navbar -->
                <ul class="list-inline navbar-right top-menu top-right-menu">  

                    <!-- user login dropdown start-->
                    <li class="dropdown text-center">
              	
						   <input type="hidden" id="userid" value="<?php echo $user_id ?>" />
						   <form name=logoutform action=logout.php>
						   </form>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-user"></i>
                            <span class="username"><?php echo $login_name ?> </span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu extended pro-menu fadeInUp animated" tabindex="5003" style="overflow: hidden; outline: none; display:none;">
                            <li><a href="javascript:void(0)" OnClick="click_logout();"><i class="fa fa-sign-out"></i> 退出</a></li>
                        </ul>
                    </li>
                    <!-- user login dropdown end -->       
                </ul>
                <!-- End right navbar -->

            </header>
            <!-- Header Ends -->


            <!-- Page Content Start -->
            <!-- ================== -->

            <div class="wraper container-fluid">
                <div class="page-title"> 
                    <h3 class="title">新增考卷</h3> 
                </div>

                <!-- Basic Form Wizard -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body"> 
                                <form id="basic-form" action="#">
                                    <div>
                                        <h3>考试类别-至少选一项</h3>
                                        <section>
                                            <div class="form-group"  style="height:420px;">
                                                <div class="col-lg-12">
									


									
>>>>>>> master
<?php

         // Function Other
         $func_type = FUNCTION_OTHER;
         $str_query = "Select * from functions where FunctionType=$func_type";
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               echo "<div class='form-group'><label for=''>".get_func_type_name($func_type)."</label> <br/>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<label class='cr-styled'><Input type=checkbox class='functions' value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                  $i++;
               }
               echo "</div>";
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
               echo "<div class='form-group'><label for=''>".get_func_type_name($func_type)."</label> <br/>";

               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<label class='cr-styled'><Input type=checkbox class=functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                  $i++;
               }
               echo "</div>";
            }
         }
          // Function Adaptation
         $func_type = FUNCTION_ADAPTATION;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               echo "<div class='form-group'><label for=''>".get_func_type_name($func_type)."</label> <br/>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  echo "<label class='cr-styled'><Input type=checkbox class=functions value=\"".$row['FunctionId']."\"><i class='fa'></i> ".$row['FunctionName'].'</label>';
                  $i++;
               }
               echo "</div>";
            }
         }
 
      ?>
	  
   
											
                                                </div>
                                            </div>
                                        </section>
                                        <h3>题型选择</h3>
                                        <section>
												<div class="col-md-5">
                                    <div class="form-group">
                                        <label for="NewExamSingleSelProbType" class="control-label">单选题数量</label>
											<Input type=text class="problem_type_count form-control" id=NewExamSingleSelProbType value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMutiSelProbType" class="control-label">多选题数量</label>
											<Input type=text class="problem_type_count form-control" id=NewExamMutiSelProbType value=0>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamTrueFalseProbType" class="control-label">是非题数量</label>
											<Input type=text class="problem_type_count form-control" id=NewExamTrueFalseProbType value=0>
                                    </div>
												</div>

												<div class="col-md-3">
                                    <div class="form-group">
                                        <label for="NewExamSingleSelScore" class="control-label">单选题每题分值</label>
										<select class="form-control" id=NewExamSingleSelScore>
											<option selected value=1>1</option>
											<option value=2>2</option>
											<option value=3>3</option>
											<option value=4>4</option>
											<option value=5>5</option>
										</select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMutiSelScore" class="control-label">多选题每题分值</label>
										<select class="form-control" id=NewExamMutiSelScore>
											<option selected value=1>1</option>
											<option value=2>2</option>
											<option value=3>3</option>
											<option value=4>4</option>
											<option value=5>5</option>
										</select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamTrueFalseScore" class="control-label">是非题每题分值</label>
										<select class="form-control" id=NewExamTrueFalseScore>
											<option selected value=1>1</option>
											<option value=2>2</option>
											<option value=3>3</option>
											<option value=4>4</option>
											<option value=5>5</option>
										</select>
                                    </div>
												</div>
												
												
												<div class="col-md-4">
													<div class="panel panel-default">
														<div class="panel-heading"><h3 class="panel-title">难易配比</h3></div>
														<div class="panel-body">
                                    <div class="form-group">
                                        <label for="NewExamEasyLevel">易 %</label>
										<select class="problem_level form-control" id=NewExamEasyLevel></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamMidLevel">中 %</label>
										<select class="problem_level form-control" id=NewExamMidLevel disabled>
											<option id="problem_mid_level" selected value=100>100</option>
										</select>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewExamHardLevel">难 %</label>
										<select class="problem_level form-control" id=NewExamHardLevel></select>
                                    </div>

														</div>
													</div>
                                            </div>
                                        </section>
                                        <h3>产生考题</h3>
                                        <section>
                                            <div class="form-group clearfix">
                                                <div class="col-lg-12">
												
												<div class="col-md-6">
                                                    <p class="lead">条件选择完成，可以产生考题了</p>
													<p>产生考题后，可以在下方预览结果</p>
													<p>如需调整请回到上一步</p>
													<p>如考题无误，继续下一步完善考卷信息</p>
													<p></p>
													<p></p>
										
													<div class="form-group">
            <a class="btn_submit_new"><input name="genProbsButton" id="genProbsButton" type="button" class="btn btn-warning m-b-5" value="产生考题" >
			   </a>
													</div>
                                                </div>
												
												<div class="col-md-6 status" id="status_template" style="display:none">
                        <div class="panel panel-default">
                            <div class="panel-heading"> 
                                <h3 class="panel-title">本次产生题目摘要</h3> 
                            </div> 
                            <div class="panel-body"> 
		<div id="selected_functions" style="display:none"></div>
		<div class="error" id="error_template" style="display:none"></div>
   
      <div>是非题目题数: <u id="content0"></u></div>
      <div>单选题目题数: <u id="content1"></u></div>
      <div>多选题目题数: <u id="content2"></u></div>
      <div>简易题目题数: <u id="content3"></u></div>
      <div>中等题目题数: <u id="content4"></u></div>
      <div>困难题目题数: <u id="content5"></u></div>
   

							
							</div>  <!-- End panel-body -->
                        </div> <!-- End panel -->
             
												</div>
                                            </div>
										</div>
                                        </section>
                                        <h3>考试信息</h3>
                                        <section>
                                            <div class="form-group clearfix">
                                            <div id="err_no_problem">问题题数为 0 时，不会显示考试信息</div>
                                            <div class="col-lg-12 exam_info" style="display: none">
						
												<div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_name">考试名称</label>
										<input type="text" size="100" id="exam_name" class="form-control">
										
                                    </div>
                                    <div class="form-group">
										   <select id="exam_type" class="form-control">
											  <option selected value=0>模拟考试</option>
											  <option value=1>正式考试</option>
										   </select>
                                    </div>
                                    <div class="form-group">
										<input type="text" id="exam_duration_time" type="text" class="from form-control" placeholder="考试长度 (分鐘)">
                                    </div>
                                    <div class="form-group">
										<textarea id="exam_desc" rows="4" class="form-control" placeholder="考试描述"></textarea>
                                    </div>
									
									<div class="form-group">   
										<a class="btn_submit_new"><input name="saveProbsButton" class="saveProbsButton btn btn-success" type="button" value="保存试卷"></a>
									</div>

												</div>			
												
												<div class="col-md-6" id="exam_answer_selections">
													<div class="panel panel-default">
														<div class="panel-body">
                                    <div class="form-group" id="exam_ans_type" style="display:none">
                                        <label for="exam_answer_type">答案公布类型</label>
										<select id="exam_answer_type" class="form-control">
											<option value=1>考试交卷后公布答案</option>
											<option value=2 selected>考试结束后公布答案</option>
										</select>
										
                                    </div>
									
                                    <div class="form-group" id="exam_location_selections"  style="display: none">
                                        <label for="exam_location">考试方式</label>
										   <select id="exam_location" class="form-control">
											  <option value=0 selected>线上</option>
											  <option value=1>落地考</option>
										   </select>
                                    </div>
                                    <div class="form-group" id="exam_password_sections" style="display:none">
										<input type="text" id="exam_password" class="form-control" placeholder="考卷密码(4位数字)">
                                    </div>
									
									<div  id="exam_time_selections">
									<div>
									<form class="form-inline" role="form">
										<div class="form-group">
											<label class="sr-only" for="to73">test</label>
										</div>
                                    </form>
									<form class="form-inline" role="form">
										<div class="form-group">
											<label class="sr-only" for="exam_begin_time">考试时间</label>
											<input id="exam_begin_time" type="text" name="exam_from_date6" class="to form-control" readonly="true" placeholder="考试日期/小时/分钟">
										</div>
										  
										<div class="form-group m-l-10">
										   <select id="exam_from_hour" class="form-control m-b-5" placeholder="小时"></select>
										</div>
										<div class="form-group m-l-10">
										   <select id="exam_from_min" class="form-control m-b-5" placeholder="分钟"></select>
										</div>
                                    </form>
									</div>
									<div>
									<form class="form-inline" role="form">
										<div class="form-group">
											<label class="sr-only" for="exam_end_time">结束时间</label>
											<input id="exam_end_time" type="text" name="exam_to_date6" class="to form-control" readonly="true" placeholder="结束日期/小时/分钟">
										</div>
										  
										<div class="form-group m-l-10">
										   <select id="exam_to_hour" class="form-control m-b-5" placeholder="小时"></select>
										</div>
										<div class="form-group m-l-10">
										   <select id="exam_to_min" class="form-control m-b-5" placeholder="分钟"></select>
										</div>
									
                                    </form>
									</div>
									</div>
														</div>	
													</div>
												</div>

                                            </div>
											</div>
                                        </section>
                                    </div>
                                </form> 
                            </div>  <!-- End panel-body -->
                        </div> <!-- End panel -->

                    </div> <!-- end col -->

                </div> <!-- End row -->

  
               <select id="exam_status" style="display: none">
                  <option value=0>下架</option>
               </select>

                <!-- Wizard with Validation -->

                <!-- Wizard with Validation -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"> 
                                <h3 class="panel-title">题目结果预览</h3> 
                            </div> 
                            <div class="panel-body"> 
							

   <div class="problem_info" style="display:none">
      <table class="table">
         <th>编号</th><th>题型</th><th>难易</th><th>描述</th>
         <tr id="problem_template"></tr>
      </table>
   </div>
							
							</div>  <!-- End panel-body -->
                        </div> <!-- End panel -->
                    </div> <!-- end col -->
                </div> <!-- End row -->
             

            </div>
            <!-- Page Content Ends -->
            <!-- ================== -->

            <!-- Footer Start -->
            <footer class="footer">
                2015 © Takeda.
            </footer>
            <!-- Footer Ends -->



        </div>
        <!-- Main Content Ends -->

        <!-- js placed at the end of the document so the pages load faster -->
        <script src="../newui/js/jquery.js"></script>
        <script src="../newui/js/bootstrap.min.js"></script>
        <script src="../newui/js/pace.min.js"></script>
        <script src="../newui/js/wow.min.js"></script>
        <script src="../newui/js/jquery.nicescroll.js" type="text/javascript"></script>


        <!--Form Validation-->
        <script src="../newui/assets/form-wizard/bootstrap-validator.min.js" type="text/javascript"></script>

        <!--Form Wizard-->
        <script src="../newui/assets/form-wizard/jquery.steps.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="../newui/assets/jquery.validate/jquery.validate.min.js"></script>

        <!--wizard initialization-->
        <script src="../newui/assets/form-wizard/wizard-init.js" type="text/javascript"></script>


        <script src="../newui/js/jquery.app.js"></script>

<script type="text/javascript" src="../lib/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>

    </body>
</html>
