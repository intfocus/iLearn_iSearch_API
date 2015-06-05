<?php
/////////////////////////
//index.php
//2015/05/15", By Phantom+Eric
//////////////////////////

   define("FILE_NAME", "d:/phptest/DB.conf");
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
   $login_name = "Phantom";
   $user_id = 1;
   $current_func_name = "iSearch";
   
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
   
   // 取得这个 user_id 相对应的 function_name with function_type=0, 放入一个 Array
   $str_query1 = "Select F.FunctionName as func_name, 
      F.Icon as icon, F.CodePath as codepath, F.Rank as rank 
      from privileges P, functions F 
      where P.UserId=$user_id and P.FunctionId = F.FunctionId
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
         Array_Push($func_array,$sf);
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
<link rel="stylesheet" type="text/css" href="lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="css/OSC_layout.css">
<link type="text/css" href="lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/OSC_layout.js"></script>
<script type="text/javascript" src="js/css3pie.js"></script>
<script type="text/javascript" src="js/PMarkFunction.js"></script>
<script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
<script type="text/javascript" src="openflashchart/js/json/json2.js"></script>
<script>
</script>
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
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
</head>
<body Onload="loaded();">
<div id="loadingWrap" class="nodlgclose loading" style="display:none;">
   <div id="loadingContent">
      <span id="loadingContentInner">
         <span id="loadingIcon"></span><span id="loadingText">读取中(需要数分钟)...</span>
      </span>
   </div>
</div>
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
   <span class="bLink company"><span><?php echo $current_func_name ?></span><span class="bArrow"></span></span>
</div>
<div id="content">
  
   <!-- Report Container -->
   <div id="enterpiceReport" style="display:block;">
      <ul class="mainTabW">
	     <!-- ***Step 1 xiougai -->
        <!-- 根据 $func_array 决定哪些出现, 哪些不出现, 同时决定第一页($first_func_name) -->
<?php
   for($i=0; $i<count($func_array); $i++){
      $func = $func_array[$i];
      if($i == 0){
         echo "<li class='active'><span class='tabIcon $func->icon'></span><span>$func->functionname</span></li>";
      }
      else{
         echo "<li><span class='tabIcon $func->icon'></span><span>$func->functionname</span></li>";
      }
      
   }
   // if (in_array("公告管理",$func_array))
   // {
      // if ($first_func_name == "公告管理")
         // echo "<li class='active'><span class='tabIcon news'></span><span>公告管理</span></li>";
      // else
         // echo "<li><span class='tabIcon news'></span><span>公告管理</span></li>";
   // }
   // if (in_array("人员管理",$func_array))
   // {
      // if ($first_func_name == "人员管理")
         // echo "<li class='active'><span class='tabIcon userMgmt'></span><span>人员管理</span></li>";
      // else
         // echo "<li><span class='tabIcon userMgmt'></span><span>人员管理</span></li>";
   // }
   // if (in_array("部门管理",$func_array))
   // {
      // if ($first_func_name == "部门管理")
         // echo "<li class='active'><span class='tabIcon depts'></span><span>部门管理</span></li>";
      // else
         // echo "<li><span class='tabIcon depts'></span><span>部门管理</span></li>";
   // }
   // if (in_array("分类管理",$func_array))
   // {
      // if ($first_func_name == "分类管理")
         // echo "<li class='active'><span class='tabIcon categories'></span><span>分类管理</span></li>";
      // else
         // echo "<li><span class='tabIcon categories'></span><span>分类管理</span></li>";
   // }
   // if (in_array("文档管理",$func_array))
   // {
      // if ($first_func_name == "文档管理")
         // echo "<li class='active'><span class='tabIcon files'></span><span>文档管理</span></li>";
      // else
         // echo "<li><span class='tabIcon files'></span><span>文档管理</span></li>";
   // }
   // if (in_array("后台权限管理",$func_array))
   // {
      // if ($first_func_name == "后台权限管理")
         // echo "<li class='active'><span class='tabIcon setting'></span><span>后台权限管理</span></li>";
      // else
         // echo "<li><span class='tabIcon setting'></span><span>后台权限管理</span></li>";
   // }
?>         
      </ul>
      <div class="mainContent">
<?php
   for($i=0; $i<count($func_array); $i++){
      $func = $func_array[$i];
      if($i==0){
         echo "<div class='container searchNewsC' style='display:block;>";
         include($func->codepath);
         echo "</div>";
      }
      else{
         echo "<div class='container searchNewsC' style='display:none;>";
         include($func->codepath);
         echo "</div>";
      }
   }
   // if (in_array("公告管理",$func_array))
   // {
      // echo "<div class='container searchNewsC' style='display:";
      // if ($first_func_name == "公告管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("New\News_list.php")) include("New\News_list.php");
      // echo "</div>";
   // }
   // if (in_array("人员管理",$func_array))
   // {
      // echo "<div class='container searchUserC' style='display:";
      // if ($first_func_name == "人员管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("User\Users_list.php")) include("User\Users_list.php");
      // echo "</div>";
   // }
   // if (in_array("部门管理",$func_array))
   // {
      // echo "<div class='container searchDeptC' style='display:";
      // if ($first_func_name == "部门管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("Dept\Depts_list.php")) include("Dept\Depts_list.php");
      // echo "</div>";
   // }
   // if (in_array("分类管理",$func_array))
   // {
      // echo "<div class='container searchCategoryC' style='display:";
      // if ($first_func_name == "分类管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("Category\Categories_list.php")) include("Category\Categories_list.php");
      // echo "</div>";
   // }
   // if (in_array("文档管理",$func_array))
   // {
      // echo "<div class='container searchCategoryC' style='display:";
      // if ($first_func_name == "文档管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("File\Files_list.php")) include("File\Files_list.php");
      // echo "</div>";
   // }
   // if (in_array("后台权限管理",$func_array))
   // {
      // echo "<div class='container superUserC' style='display:";
      // if ($first_func_name == "后台权限管理")
         // echo "block;'";
      // else
         // echo "none;'";
      // echo ">";
      // if (file_exists("Admin\Privileges_list.php")) include("Admin\Privileges_list.php");
      // echo "</div>";
   // }
?> 
  
   </div>
</div>
</body>
</html>