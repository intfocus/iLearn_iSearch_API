<?php
   require_once("Exams_utility.php");
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
   $current_func_name = "iLearn";
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
      if(strcmp($check_str, "read") && strcmp($check_str, "update"))
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
   $ProbId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($ExamId = check_number($_GET["ExamId"])) == SYMBOL_ERROR)
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

   $TitleStr = MSG_EXAM_MODIFY;
   
   $problems = array();
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if (strcmp($cmd, "read") == 0) // Load
   {  
      $str_query1 = "Select * from exams where ExamId=$ExamId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $ExamId = $row["ExamId"];
            $ExamName = $row["ExamName"];
            $ExamType = $row["ExamType"];
            $ExamLocation = $row["ExamLocation"];
            $ExamBegin = $row["ExamBegin"];
            $ExamEnd = $row["ExamEnd"];
            $ExamAnsType = $row["ExamAnsType"];
            $ExamPassword = $row["ExamPassword"];
            $ExamDesc = $row["ExamDesc"];
            $ExamContent = $row["ExamContent"];
            $Duration = $row["Duration"];
            $CreatedUser = $row["CreatedUser"];
            $CreatedTime = $row["CreatedTime"];
            $ExamStatus = $row["Status"];
            $StatusStr = $ExamStatus == 0 ? "下架" : "上架";
            if ($ExamStatus == 1)
               $TitleStr = "考卷查看 (上架状态无法修改)";
            
            $ExamContentStr = get_content_str($ExamContent);
         }
         else
         {
            header("Location: ../index.php");
            die();
         }

         $str_query = "select * from examdetail where ExamId=$ExamId";
         if($result = mysqli_query($link, $str_query))
         {
            $row_number = mysqli_num_rows($result);
            for ($i=0; $i<$row_number; $i++)
            {
               $row = mysqli_fetch_assoc($result);
               array_push($problems, get_problems_info($row["ProblemId"]));
            }
         }
      }
   }
   else if ($cmd == "update")// Update
   {
      if (!isset($_GET["ExamName"]) || !isset($_GET["ExamDesc"]) || !isset($_GET["Duration"]) ||
          !isset($_GET["ExamBeginTime"]) || !isset($_GET["ExamEndTime"]))
      {
         echo ERR_INVALID_PARAMETER;
         return;
      }
      
      
      $ExamName = $_GET["ExamName"];
      $ExamDesc = $_GET["ExamDesc"];
      $Duration = $_GET["Duration"];
      $ExamBeginTime = $_GET["ExamBeginTime"];
      $ExamEndTime = $_GET["ExamEndTime"];
      $ExamPassword = $_GET["ExamPassword"];

      $from_datetime = timestamp_to_datetime($ExamBeginTime);
      $end_datetime = timestamp_to_datetime($ExamEndTime);

      // ID check
      $str_query = "Select * from exams where ExamId='$ExamId'";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number == 0)
         {
            echo ERR_EXAM_NOT_EXIST;
            return;
         }
      }

      $str_query1 = <<<EOD
                      Update exams set ExamName='$ExamName', ExamDesc='$ExamDesc',
                      Duration=$Duration, ExamBegin='$from_datetime', ExamPassword = '$ExamPassword', 
                      ExamEnd='$end_datetime', EditUser=$user_id, EditTime=now() where ExamId=$ExamId
EOD;

      if(mysqli_query($link, $str_query1))
      {

         $json_file = EXAM_FILES_DIR."/".$ExamId.".json";
         $exam_json = json_decode(file_get_contents($json_file));

         $exam_json->exam_name = $ExamName;
         $exam_json->description = $ExamDesc;
         $exam_json->duration = (int)$Duration;
         $exam_json->begin = (int)$ExamBeginTime;
         $exam_json->end = (int)$ExamEndTime;
         $exam_json->password = $ExamPassword;
         
         $tmp_file = EXAM_FILES_DIR."/".$ExamId.time();
         file_put_contents($tmp_file, json_encode($exam_json));
         copy($tmp_file, $json_file);
         
         echo "0";
         return;
      }
      else
      {
         echo -__LINE__ . $str_query1;
         return;
      }
   }
   
   function get_problems_info($problem_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         return DB_ERROR;       
      }
      
      
      $str_query = "select * from problems where ProblemId=$problem_id";
      if($result=mysqli_query($link, $str_query))
      {
         $row = mysqli_fetch_assoc($result);
         $problem = new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], $row["CreatedTime"]);
         return $problem;
      }
      
      return;
   }
   
   function get_content_str($content_str)
   {
      //input format: yes_no, single_choice, multi_choice, easy_level, mid_level, hard_level, categories....
      $contents = explode(",", $content_str);
      $countents_count = count($contents);
      $str = "";
      
      for ($i=0; $i<$countents_count; $i++)
      {
         if ($i == 0)
         {// yes_no problem
            $str = $str."是非题题数: ".$contents[$i].", ";
         }
         else if ($i == 1)
         {// single choice problem
            $str = $str."单选题题数: ".$contents[$i].", ";
         }
         else if ($i == 2)
         {// multi level problem
            $str = $str."多选题题数: ".$contents[$i].", ";
         }
         else if ($i == 3)
         {// easy level problem
            $str = $str."简易题数: ".$contents[$i].", ";
         }
         else if ($i == 4)
         {// mid level problem
            $str = $str."中等难度题数: ".$contents[$i].", ";
         }
         else if ($i == 5)
         {// hard level problem
            $str = $str."困难难度: ".$contents[$i].", ";
         }
         else if ($i == 6)
         {// category
            $str = $str."分类: ";
         }
         else if ($i == ($countents_count-1))
         {
            $str = $str.$contents[$i];
         }
         else {
            $str = $str.$contents[$i].", ";
         }
      }
      return $str;
   }
   
?>

<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="../css/OSC_layout.css">
<link rel="stylesheet" type="text/css" href="../css/exam.css">
<link rel="stylesheet" type="text/css" href="../css/problem.css">
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<!-- for tree view
<link rel="stylesheet" type="text/css" href="../css/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="../css/themes/icon.css">
<link rel="stylesheet" type="text/css" href="../css/demo.css">
 -->

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
		

<!-- End of tree view -->
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<title>武田 - 考卷页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<!-- Billy 2012/2/3 -->
<script type="text/javascript" src="../js/utility.js"></script>
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
   dom = "<option></option>";
   $(dom).appendTo("#exam_from_hour");
   $(dom).appendTo("#exam_to_hour");
   for (i=0; i<=23; i++)
   {
      dom = "<option value="+ i +">" + i + "</option>";
      $(dom).appendTo("#exam_from_hour");
      $(dom).appendTo("#exam_to_hour");
   }
      
   dom = "<option></option>";
   $(dom).appendTo("#exam_from_min");
   $(dom).appendTo("#exam_to_min");
   for (i=0; i<=59; i++)
   {
      dom = "<option value="+ i +">" + i + "</option>";
      $(dom).appendTo("#exam_from_min");
      $(dom).appendTo("#exam_to_min");
   }
}

function modifyExamsContent(ExamId)
{
   ExamName = document.getElementsByName("ExamNameModify")[0].value.trim();
   ExamDesc = document.getElementsByName("ExamDescModify")[0].value.trim();
   ExamPassword = document.getElementsByName("ExamPasswordModify")[0].value.trim();

   ExamFromDate = document.getElementById("exam_begin_time").value;
   ExamFromHour = document.getElementById("exam_from_hour").value;
   ExamFromMin = document.getElementById("exam_from_min").value;
   ExamToDate = document.getElementById("exam_end_time").value;
   ExamToHour = document.getElementById("exam_to_hour").value;
   ExamToMin = document.getElementById("exam_to_min").value;
   Duration = document.getElementById("exam_duration").value;

   if (ExamName.length == 0 || ExamDesc.length == 0)
   {
      alert("考卷描述及考卷答案不可为空白");
      return;
   }
   
   if (ExamName.length > 100)
   {
      alert("考卷名称不能超过100字元");
      return;
   }
   
   if (ExamDesc.length > 500)
   {
      alert("考卷描述不能超过500字元");
      return;
   }
   
   
   if (Duration.length == 0)
   {
      alert("考试长度不能为空");
   }
   if (isNaN(Duration) || Duration <= 0)
   {
      alert("考试长度必须为大于 0 的正整数");
      return;
   }
      

   if (ExamFromDate.length > 0 && ExamFromHour.length > 0 && ExamFromMin.length > 0 &&
       ExamToDate.length > 0 && ExamToHour.length > 0 && ExamToMin.length > 0)
   {
      //calculate from time stamp, and end time stamp
      date_timestamp = new Date(ExamFromDate).getTime();
      hour_min_timestamp = (60 * 60 * ExamFromHour + 60 * ExamFromMin) * 1000;
      from_timestamp = date_timestamp + hour_min_timestamp;
      
      date_timestamp = new Date(ExamToDate).getTime();
      hour_min_timestamp = (60 * 60 * ExamToHour + 60 * ExamToMin) * 1000;
      to_timestamp = date_timestamp + hour_min_timestamp;
      
      if (from_timestamp >= to_timestamp)
      {
         alert("考试开始时间不能大于结束时间");
         return;
      }
   }
   else if (ExamFromDate.length == 0 && ExamFromHour.length == 0 && ExamFromMin.length == 0 &&
       ExamToDate.length == 0 && ExamToHour.length == 0 && ExamToMin.length == 0)
   {
      from_timestamp = <? echo strtotime($ExamBegin); ?> * 1000;
      to_timestamp = <? echo strtotime($ExamEnd); ?> * 1000; 
   }
   else
   {
      alert("修改考试时间段时，不能有值为空白");
      return;
   }

   str = "cmd=update&ExamId=" + ExamId + "&ExamName=" + encodeURIComponent(ExamName) + "&ExamPassword=" + encodeURIComponent(ExamPassword) + 
         "&ExamDesc=" + encodeURIComponent(ExamDesc) + "&Duration=" + encodeURIComponent(Duration) +         
         "&ExamBeginTime=" + encodeURIComponent(from_timestamp/1000) + "&ExamEndTime=" + encodeURIComponent(to_timestamp/1000);
   url_str = "Exams_modify.php?";
   
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(url_str + str);
      },
      // should be POST
      type: "GET",
      url: url_str + str,
      cache: false,
      
      success: function(res)
      {
         if (res.match(/^-\d+$/))  //failed
         {
            if ($res == <? echo ERR_INVALID_PARAMETER?>)
            {
               alert("参数不合法");
            }
            else if ($res == <? echo ERR_EXAM_NOT_EXIST?>)
            {
               alert("考卷不存在");
            }
            return;
         }
         else  //success
         {
            alert("修改成功，页面关闭后请自行刷新");
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
<body Onload="loaded();">

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
                    <h3 class="title"><?php echo $TitleStr; ?></h3> 
                </div>

                <!-- Basic Form Wizard -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body"> 

   <table class="searchField" border="0" cellspacing="0" cellpadding="0" style="width:100%">
      <tr>
         <th>考卷名称: </th>
         <td><Input style="width:100%" type=text name=ExamNameModify size=50 value="<?php echo $ExamName?>"></td>
      </tr>
      <tr>
         <th>考卷描述：</th>
         <td><textarea style="width:100%" name=ExamDescModify rows=3><?php echo $ExamDesc;?></textarea></td>
      </tr>
      <tr>
         <th>考卷类型：</th>
         <td><Input style="width:100%" type=text name=ExamType size=50 disabled="disabled" value="<?php
            if ($ExamType == MOCK_EXAM) 
            {
               echo MSG_MOCK_EXAM;
            }
            else if ($ExamType == OFFICIAL_EXAM)
            {
               echo MSG_OFFICIAL_EXAM;
            }
            ?>">
         </td>
      </tr>
      <tr>
         <th>考卷答案类型:</th>
         <td><Input style="width:100%" type=text name=ExamType size=50 disabled="disabled" value="<?php
            if ($ExamAnsType == GIVE_ANSWER_AFTER_SUBMIT) 
            {
               echo MSG_GIVE_ANSWER_AFTER_SUBMIT;
            }
            else if ($ExamAnsType == GIVE_ANSWER_AFTER_EXAM_FINISHED)
            {
               echo MSG_GIVE_ANSWER_AFTER_EXAM_FINISHED;
            }
            ?>">
         </td>
      </tr>
      <tr <? if ($ExamLocation == OLINE_TEST){ echo "style='display:none'";}?>>
         <th>考卷密码：</th>
         <td><Input style="width:100%" type=text name=ExamPasswordModify size=50 value="<?php echo $ExamPassword;?>"></td>
      </tr>
      <tr>
         <th>考试地点: </th>
         <td><Input style="width:100%" type=text name=ExamLocation size=50 disabled="disabled" value="<?php
            if ($ExamLocation == OLINE_TEST) 
            {
               echo MSG_ONLINE_TEST;
            }
            else if ($ExamLocation == ONSITE_TEST)
            {
               echo MSG_ONSITE_TEST;
            }
            ?>">
         </td>
      </tr>
      <tr>
         <th>考试内容: </th>
          <td><Input style="width:100%" type=text name=ExamContentModify  disabled="disabled" value="<?php echo $ExamContentStr;?>"></td>
         </td>
      <tr>
         <th>考试长度 (分钟): </th>
         <td> <input id="exam_duration" type="text" name="exam_duration" class="from" value="<? echo $Duration;?>"></td>
      </tr>
      <tr>
         <th>原始考试时间段: </th>
         <td>
            <input type="text" readonly="true" disabled="disabled" value="<?echo $ExamBegin?>">
            ~
            <input type="text" readonly="true" disabled="disabled" value="<?echo $ExamEnd?>">
         </td>
      </tr>
      <tr>
         <th>新考试时间段: </th>
         <td>
            <input id="exam_begin_time" type="text" name="exam_from_date6" class="from" readonly="true">
            <select id="exam_from_hour"></select>
            <select id="exam_from_min"></select>
            ~
            <input id="exam_end_time" type="text" class="to" name="exam_to_date6" readonly="true">
            <select id="exam_to_hour"></select>
            <select id="exam_to_min"></select>
         </td>
      </tr>   
  
   </table>
   
							</div>  <!-- End panel-body -->
                        </div> <!-- End panel -->

                    </div> <!-- end col -->

                </div> <!-- End row -->
   
   
   
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"> 
                                <h3 class="panel-title">题目</h3> 
                            </div> 
                            <div class="panel-body"> 
   <div class="problem_info">
      <table class="problems_table table">
         <th style="width:3%">编号</th><th style="width:5%">题型</th><th style="width:5%">难易</th><th>描述</th>
<?php
         for ($i=0; $i<count($problems); $i++)
         {
            $sequence = $i + 1;
            echo "<tr><td>".$sequence."</td><td>".get_type_name_from_id($problems[$i]->type)."</td><td>".get_level_name($problems[$i]->level)."</td><td>".$problems[$i]->desc."</td></tr>";
         }
?>         
      </table>
   </div>
<?php
   if ($ExamStatus != 1)
   {
?>       
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyExamsContent"><input name="modifyExamsButton" type="button" value="保存" OnClick="modifyExamsContent(<?php echo $ExamId;?>)"></a>
         </th>
      </tr>      
<?php
   }
?> 

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

<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<script type="text/javascript" src="../lib/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
</body>
</html>
<!--Step15 新增修改页面    结束 -->