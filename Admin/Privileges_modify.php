<?php

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
   
   function check_email($check_str)
   {
      $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
      if(!preg_match($pattern,$check_str))
         return SYMBOL_ERROR;
      return SUCCESS;
   }
   
   //get data from client
   $cmd;
   $UserId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($UserId = check_number($_GET["UserId"])) == SYMBOL_ERROR)
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
      // 产生 $privilegesList
      $privilegesList = "";
      $str_query1 = "Select * from privileges where UserId=$UserId";
      //echo $str_query1;
      if($result = mysqli_query($link, $str_query1))
      {
         while ($row = mysqli_fetch_assoc($result))
         {
            $func_id = "," . $row["FunctionId"] . ",";
            $privilegesList = $privilegesList . $func_id;
         }
      }
      
      $str_query1 = "Select * from Users where UserId=$UserId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);

         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $UserId = $row["UserId"];
            $UserName = $row["UserName"];
            $EmployeeId = $row["EmployeeId"];
            $UserEmail = $row["Email"];
            $DeptId = $row["DeptId"];
            $CanApprove = $row["CanApprove"];
            $JobGrade = $row["JobGrade"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "用户权限修改";
            if ($Status == 1)
               $TitleStr = "用户权限查看 (上架状态无法修改)";
         }
         else
         {
            $UserId = 0;
            $UserName = "";
            $EmployeeId = "";
            $UserEmail = "";
            $DeptId = 1;
            $CanApprove = 0;
            $JobGrade = "";
            $TitleStr = "用户权限新增";
            $Status = 0;
         }
      }
   }
   else // Update
   {
      // Delete all the privileges with $UserId
      $str_query1 = "delete from privileges where UserId=$UserId";
      mysqli_query($link, $str_query1);
      
      // Insert privileges one by one for $UserId
      $privilegesList = $_GET["privilegesList"];
      $tmp = explode(",", $privilegesList);
      $tmp_count = count($tmp);
      for ($i=0;$i<$tmp_count;$i++)
      {
         $str_query1 = "Insert into privileges (UserId,FunctionId,Status,CreatedUser,CreatedTime,EditUser,EditTime)" .
            "VALUES($UserId,$tmp[$i],0,$user_id,now(),$user_id,now());";
         mysqli_query($link, $str_query1);
      }
      echo "0";
      return;
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
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<!-- for tree view -->
<!-- End of tree view -->
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
<title>武田 - 用户页面</title>
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
   var palstr = "<?php echo $privilegesList; ?>";
   var palstr1 = palstr.substring(1,palstr.length-1);
   var palstr_array = palstr1.split(",,");
   for(var i=0; i<palstr_array.length;i++)
   {
      var palcheck_array=document.getElementsByName("privilegesList");
      for(var j=0;j<palcheck_array.length;j++)
      {
         if(palcheck_array[j].value==palstr_array[i])
         {         
            palcheck_array[j].checked=true;
         }
      }
   }
}
//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyPrivilegesContent(UserId)
{
   var rusult="";
   var check_array=document.getElementsByName("privilegesList");
   for(var i=0;i<check_array.length;i++)
   {
      if(check_array[i].checked==true)
      {
         rusult=rusult + check_array[i].value + ",";
      }
   }
   rusult = rusult.substring(0,rusult.length-1);
   
   str = "cmd=write&UserId=" + UserId + "&privilegesList=" + encodeURIComponent(rusult);
   url_str = "../Admin/Privileges_modify.php?";

   //alert(url_str + str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "GET",
      url: url_str + str,
      cache: false,
      success: function(res)
      {
         //alert("Data Saved: " + res);
         if (res.match(/^-/))  //failed
         {
            alert("用户后台权限管理修改失败 " + res);
         }
         else  //success
         {
            alert("用户后台权限管理修改成功，请重新登陆");
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

   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>用户名称：</th>
         <td><Input type=text readonly="true" name=UserNameModify size=50 value="<?php echo $UserName;?>"></td>
      </tr>
      <tr>
         <th>工号：</th>
         <td><Input type=text readonly="true" name=EmployeeId size=50 value="<?php echo $EmployeeId;?>"></td>
      </tr>
      <tr>
         <th>用户邮箱：</th>
         <td><Input type=text readonly="true" name=UserEmailModify size=50 value="<?php echo $UserEmail;?>"></td>
      </tr>
      <tr>
         <th>后台功能列表：</th>
         <td>

      <!-- 显示所有的 function_name with functionId 以及目前这个 user 有哪些权限 开始-->
      <table>
         <tr>
<?php      
      $str_query1 = "Select * from functions where FunctionType=0 order by Rank" ;
      $i = 1;
      if ($result = mysqli_query($link, $str_query1))
      {
         while ($row = mysqli_fetch_assoc($result))
         {
            $func_name = $row["FunctionName"];
            $func_id = $row["FunctionId"];
            echo "<td><Input type=checkbox name='privilegesList' value='$func_id'>$func_name </td>";
            if($i == 8)
            {
               echo "</tr><tr>";
               $i = 1;
            }
            else 
            {
                $i = $i + 1;
            }
         }
      }
?>
         </tr>
      </table>
      <!-- 显示所有的 function_name with functionId 以及目前这个 user 有哪些权限 结束-->
         </td>
      </tr>
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyPrivilegesContent"><input name="modifyPrivilegesButton" type="button" value="保存" OnClick="modifyPrivilegesContent(<?php echo $UserId;?>)"></a>
         </th>
      </tr>      

   </table>

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