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
   if ($cmd == "read") // 准备 batch new 画面
   {
   }
   else if ($cmd == "write") // Batch Insert
   {
      $delUsersBatchInput = $_GET["delUsersBatchInput"];
      // 1. 按照 \n 切开
      $tmp = explode("\n", $delUsersBatchInput);
      $tmp_count = count($tmp);
      // 2. 产生 (工号1,工号2,工号3)
      $EmployeeId_list = "";
      for ($i=0;$i<$tmp_count;$i++)
      {
          $EmployeeId_list = $EmployeeId_list . "'" . $tmp[$i] . "',";
      }
      $EmployeeId_list = $EmployeeId_list . "'-100'";
      $sql_str = "Update Users set status=-1 where EmployeeId in ($EmployeeId_list)";      
      if (!mysqli_query($link, $sql_str))
      {
         if ($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         $ErrMsg = "数据错误 -- 批次删除失败";
         echo "-- " . $ErrMsg;
         return;
      }
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo "0";
      return;
   }
   else
   {
      echo -__LINE__;
      return;
   }
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue", 01 Jan 1980 1:00:00 GMT">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
<link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
<link rel="stylesheet" type="text/css" href="../css/OSC_layout.css">
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
function modifyUsersContent()
{
   delUsersBatchInput = document.getElementsByName("delUsersBatchInput")[0].value.trim();
   
   str = "cmd=write&delUsersBatchInput=" + encodeURIComponent(delUsersBatchInput);
   url_str = "../User/Users_batch_del.php?";

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
            alert("错误信息："+res);
         }
         else  //success
         {
            alert("用户批次删除成功，页面关闭后请自行刷新");
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
   <span class="bLink company"><span>批次删除用户</span><span class="bArrow"></span></span>
</div>
<div id="content">
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>批次删除内容：(一行一笔数据，数据格式为 工号)</th>
      </tr>
      <tr>
         <td><Textarea name=delUsersBatchInput rows=30 cols=100>
工号1
工号2
         </Textarea></td>        
      </tr>      
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyUsersContent"><input name="modifyUsersButton" type="button" value="保存" OnClick="modifyUsersContent()"></a>
         </th>
      </tr>        
   </table>
</div>
</body>
</html>