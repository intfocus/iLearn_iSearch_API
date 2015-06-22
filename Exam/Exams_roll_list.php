<?php
   require_once("Exams_utility.php");

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

   //return value
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);

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
   $UserId;

   //query
   $link;

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   if (($exam_id = check_number($_GET["ExamId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   function get_employ_id_from_user_id($user_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;
         die("连接DB失败");
      }
           
      $str_query = "select * from users where UserId=$user_id";
      if($result = mysqli_query($link, $str_query)){
         $row = mysqli_fetch_assoc($result);
         return $row["EmployeeId"];
      }
      else
      {
         echo DB_ERROR;
         die("操作资料库失败");
      }
   }
   
   
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue", 01 Jan 1980 1:00:00 GMT">
<link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="../css/OSC_layout.css">
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../lib/jquery.min.js"></script>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<script type="text/javascript" src="../js/utility.js"></script>
<!-- for tree view -->
<link rel="stylesheet" type="text/css" href="../css/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="../css/themes/icon.css">
<link rel="stylesheet" type="text/css" href="../css/demo.css">
<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<!-- End of tree view -->
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
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
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyUsersContent(exam_id)
{
   newUsersBatchInput = document.getElementsByName("newUsersBatchInput")[0].value.trim();
   users_id = newUsersBatchInput.match(/[^\r\n]+/g);
   
   if (!users_id)
   {
      alert("1234");
   }
   
   for (i=0;i<users_id.length;i++)
   {
      users_id[i] = trim_start_end(users_id[i]);
      if (!users_id[i].match(/^[0-9|a-zA-Z]+$/))
      {
         alert(users_id[i] + "有非数字及英文之字元");
         return;
      }
   }

   url_str = "Exams_update_roll.php?";

   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str,
      data: {
               "exam_id": exam_id,
               "users_id": users_id,
            },
      cache: false,
      success: function(res)
      {
         if (res != 0)  //failed
         {
            alert(res);
         }
         else  //success
         {
            alert("用户批次新增成功，页面关闭后请自行刷新");
            //window.close();
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
   <span class="bLink company"><span>批次上传用户</span><span class="bArrow"></span></span>
</div>
<div id="content">
   <div id="exam_id" style="disply:none;"></div>
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>批次上传内容：(一行一笔数据，数据格式为 "工号" 或着 "使用者ID")</th>
      </tr>
      <tr>
         <td><Textarea name=newUsersBatchInput rows=30 cols=100><?   
            // read roll
         $str_query = "select * from examroll where ExamId=$exam_id AND Status=".ACTIVE;
         if($result = mysqli_query($link, $str_query)){
            $row_number = mysqli_num_rows($result);

            for ($i=0; $i<$row_number; $i++)
            {
               $row = mysqli_fetch_assoc($result);
               $user_id = $row["UserId"];
               $employee_id = get_employ_id_from_user_id($user_id);
               echo "$employee_id\n";
            }
         }?></Textarea></td>        
      </tr>   
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyUsersContent"><input name="modifyUsersButton" type="button" value="保存" OnClick="modifyUsersContent(<?echo $exam_id?>)"></a>
         </th>
      </tr>        
   </table>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->