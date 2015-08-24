<?php
   define("FILE_NAME", "./DB.conf");
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
   
   // TODO: 从 Session 里面拿到 login_name + user_id
   session_start();
   if ($_SESSION["GUID"] == "" || $_SESSION["username"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php?cmd=err");
      exit();
   }
   $user_id = $_SESSION["GUID"];
   $login_name = $_SESSION["username"];
   // $login_name = "Phantom";
   // $user_id = 1;
   $current_func_name = "iSearch";
   session_write_close();
   
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("PAGE_SIZE", 100);

   define("AVAILABLE", 0);
   define("TRIAL", 0);
   define("DB_ERROR", -1);

   define("MSG_REPORT_1", "目前沒有任何報表，請點選&quot;<a>產生新的報表</a>&quot;");

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
 
   date_default_timezone_set(TIME_ZONE);  //set timezone

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }
   
   //eric-edit -begin
   // 取得这个 user_id 相对应的 function_name, icon, codepath, rank with function_type=0, 放入一个 Array
   $str_query1 = "Select F.FunctionName as func_name, 
      F.Icon as icon, F.CodePath as codepath, F.Rank as rank 
      from privileges P, functions F 
      where P.UserId=$user_id and P.FunctionId = F.FunctionId and F.FunctionType = 0
      order by F.Rank";
   $func_array = Array();
   class Stufun{
      public $functionname;
      public $icon;
      public $codepath;
   }
   $first_func_name = "";
   if ($result = mysqli_query($link, $str_query1))
   {
      while ($row = mysqli_fetch_assoc($result))
      {
         $sf = new Stufun();
         $func_name = $row["func_name"];
         $sf->functionname = $func_name;
         $sf->icon = $row["icon"];
         $sf->codepath = $row["codepath"];
         if ($first_func_name == "")
            $first_func_name = $func_name;
         array_push($func_array,$sf);
      }
   }
   
   class StuExams{
      public $ExamId;
      public $ExamName;
   }
   $dataexams = array();
   $str_exams = "select ExamId,ExamName from exams";
   if($result = mysqli_query($link, $str_exams)){
      while($row = mysqli_fetch_assoc($result)){      
         $se = new StuExams();
         $se->ExamId = $row["ExamId"];
         $se->ExamName = $row['ExamName'];
         array_push($dataexams,$se);
      }
   }
   //print_r($dataexams);
   //eric-edit -end
   $canApprove = 0;
   $str_users = "select CanApprove from wutian.users where UserId=$user_id";
   if($result = mysqli_query($link, $str_users)){
      while($row = mysqli_fetch_assoc($result)){
         $canApprove = $row["CanApprove"];
      }
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
<link type="image/x-icon" href="./images/wutian.ico" rel="shortcut icon">

<link rel="stylesheet" type="text/css" href="lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="css/OSC_layout.css">

<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
        <!-- Bootstrap core CSS -->
        <link href="newui/css/bootstrap.min.css" rel="stylesheet">
        <link href="newui/css/bootstrap-reset.css" rel="stylesheet">

        <!--Animation css-->
        <link href="newui/css/animate.css" rel="stylesheet">

        <!--Icon-fonts css-->
        <link href="newui/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="newui/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="newui/assets/morris/morris.css">

        <!-- sweet alerts -->
        <link href="newui/assets/sweet-alert/sweet-alert.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="newui/css/style.css" rel="stylesheet">
        <link href="newui/css/helper.css" rel="stylesheet">
        <link href="newui/css/style-responsive.css" rel="stylesheet" />


<link type="text/css" href="lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->


<title>武田 - 后台页面</title>
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
	
}

</Script>
<style>
#enterpiceReport .mainContent {
  background-color: #fff;
  border: 0px solid #bbb;
}
</style>
</head>


<body Onload="">
<div id="loadingWrap" class="nodlgclose loading" style="display:none;">
   <div id="loadingContent">
      <span id="loadingContentInner">
         <span id="loadingIcon"></span><span id="loadingText">读取中，请稍后...</span>
      </span>
   </div>
</div>



        <!-- Aside Start-->
        <aside class="left-panel">

            <!-- brand -->
            <div class="logo">
                <a href="index.php" class="logo-expanded">
                    <img src="newui/img/single-logo.png" alt="logo">
                    <span class="nav-label">TSA</span>
                </a>
            </div>
            <!-- / brand -->
      
            <!-- Navbar Start -->
            <nav class="navigation">
                <ul class="list-unstyled mainTabW">                	
<?php
   for($i=0; $i<count($func_array); $i++){
      $func = $func_array[$i];
      if($i == 0){
         echo "<li class='active'><a href='javascript:void(0)'><span class='tabIcon $func->icon'></span><span class='nav-label'>$func->functionname</span></a></li>";
      }
      else{
         echo "<li><a href='javascript:void(0)'><span class='tabIcon $func->icon'></span><span class='nav-label'>$func->functionname</span></a></li>";
      }
      
   }
?>     
<?php
   if($canApprove==1)
   {
      echo "<li><a href='javascript:void(0)'><span class='tabIcon examine'></span><span class='nav-label'>报名审批</span></a></li>";
   }
?>
                </ul>
            </nav>
        </aside>
        <!-- Aside Ends-->


        <!--Main Content Start -->
        <section class="content">
            
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
                    	
										   <form name=logoutform action=logout.php>
										   </form>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-user"></i>
                            <span class="username"><?php echo $login_name ?> </span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu extended pro-menu fadeInUp animated" tabindex="5003" style="overflow: hidden; outline: none;">
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

            <div class="wraper container-fluid" id="enterpiceReport">
      
      <div class="mainContent">
      	
<?php
   for($i=0; $i<count($func_array); $i++){
      $func = $func_array[$i];
      $codepath = $func->codepath;
      if($i==0){
         echo "<div class='container2 searchNewsC' style='display:block;'>";
         include($codepath);
         echo "</div>";
      }
      else{
         echo "<div class='container2 searchNewsC' style='display:none;'>";
         include($codepath);
         echo "</div>";
      }
   }
?>
<?php
   if($canApprove==1)
   {
      echo "<div class='container2 searchNewsC' style='display:none;'>";
         include("TraineeExamine/TraineeExamines_list.php");
      echo "</div>";
   }
?> 
  
   </div>
   
   
   
            </div>
            <!-- Page Content Ends -->
            <!-- ================== -->

            <!-- Footer Start -->
            <footer class="footer">
                2015 © Takeda.
            </footer>
            <!-- Footer Ends -->



        </section>
        <!-- Main Content Ends -->
        


        <!-- js placed at the end of the document so the pages load faster -->
        <script src="newui/js/jquery.js"></script>
        <script src="newui/js/bootstrap.min.js"></script>
        <script src="newui/js/modernizr.min.js"></script>
        <script src="newui/js/pace.min.js"></script>
        <script src="newui/js/wow.min.js"></script>
        <script src="newui/js/jquery.scrollTo.min.js"></script>
        <script src="newui/js/jquery.nicescroll.js" type="text/javascript"></script>
        <script src="newui/assets/chat/moment-2.2.1.js"></script>

        <!-- Counter-up -->
        <script src="newui/js/waypoints.min.js" type="text/javascript"></script>
        <script src="newui/js/jquery.counterup.min.js" type="text/javascript"></script>

        <!-- sparkline --> 
        <script src="newui/assets/sparkline-chart/jquery.sparkline.min.js" type="text/javascript"></script>
        <script src="newui/assets/sparkline-chart/chart-sparkline.js" type="text/javascript"></script> 

        <!-- sweet alerts -->
        <script src="newui/assets/sweet-alert/sweet-alert.min.js"></script>
        <script src="newui/assets/sweet-alert/sweet-alert.init.js"></script>

        <script src="newui/js/jquery.app.js"></script>
        <!-- Chat -->
        <script src="newui/js/jquery.chat.js"></script>

        <!-- Todo -->
        <script src="newui/js/jquery.todo.js"></script>


        <script type="text/javascript">
        /* ==============================================
             Counter Up
             =============================================== */
            jQuery(document).ready(function($) {
                $('.counter').counterUp({
                    delay: 100,
                    time: 1200
                });
            });
        </script>
<!--

<script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
<script type="text/javascript" src="openflashchart/js/json/json2.js"></script>
-->    

<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/OSC_layout.js"></script>
<script type="text/javascript" src="js/css3pie.js"></script>
<script type="text/javascript" src="js/WutianFunction.js"></script>

    </body>
</html>
