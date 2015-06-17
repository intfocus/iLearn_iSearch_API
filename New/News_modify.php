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
   session_start();
   if ($_SESSION["GUID"] == "" || $_SESSION["username"] == "")
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
   
   //get data from client
   $cmd;
   $NewId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($NewId = check_number($_GET["NewId"])) == SYMBOL_ERROR)
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
      $str_query1 = "Select * from news where NewId=$NewId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $NewId = $row["NewId"];
            $NewTitle = $row["NewTitle"];
            $NewMsg = $row["NewMsg"];
            $Status = $row["Status"];
            $DeptList = $row["DeptList"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $OccurTime = $row["OccurTime"] == null ? '' : date("Y/m/d",strtotime($row["OccurTime"]));
            $TitleStr = "公告修改";
            if ($Status == 1)
               $TitleStr = "公告查看 (上架状态无法修改)";
         }
         else
         {
            $NewId = 0;
            $NewTitle = "";
            $NewMsg = "";
            $DeptList = "All";
            $OccurTime = "";
            $TitleStr = "公告新增";
            $Status = 0;
         }
      }
   }
   else if ($NewId == 0) // Insert
   {
      $NewTitle = $_GET["NewTitle"];
      $NewMsg = $_GET["NewMsg"];
      $OccurTime = "'" . $_GET["OccurTime"] . "'";
      $DeptList = $_GET["DeptList"];
      if ($OccurTime == "''")
         $OccurTime = "NULL";
      $str_query1 = "Insert into news (NewTitle,NewMsg,OccurTime,DeptList,CreatedUser,CreatedTime,EditUser,EditTime,Status)" 
                  . " VALUES('$NewTitle','$NewMsg',$OccurTime, '$DeptList',$user_id,now(),$user_id,now(),1)" ;
      if(mysqli_query($link, $str_query1))
      {
         echo "0";
         return;
      }
      else
      {
         echo -__LINE__ . $str_query1;
         return;
      }
   }
   else // Update
   {
      $NewTitle = $_GET["NewTitle"];
      $NewMsg = $_GET["NewMsg"];
      $OccurTime = "'" . $_GET["OccurTime"] . "'";
      $DeptList = $_GET["DeptList"];
      if ($OccurTime == "''")
         $OccurTime = "NULL";
      //TODO EditUser=UserId
      $str_query1 = "Update news set NewTitle='$NewTitle', NewMsg='$NewMsg', DeptList='$DeptList', OccurTime=$OccurTime, EditUser=$user_id, EditTime=now() where NewId=$NewId";
      if(mysqli_query($link, $str_query1))
      {
         echo "0";
         return;
      }
      else
      {
         echo -__LINE__ . $str_query1;
         return;
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
<link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
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
<title>武田 - 公告页面</title>
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
   $('#depttree').tree({cascadeCheck:$(this).is(':checked')})
   $("#depttree").tree({
       onCheck: function (node, checked) {
           if (checked) {
               var parentNode = $(this).tree('getParent', node.target);
               if (parentNode != null) {
                   $(this).tree('check', parentNode.target);
               }
           } else {
               var childNode = $(this).tree('getChildren', node.target);
               if (childNode.length > 0) {
                   for (var i = 0; i < childNode.length; i++) {
                       $(this).tree('uncheck', childNode[i].target);
                   }
               }
           }
       }
   });
   window.setTimeout("expandToDept()", 1000);
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyNewsContent(NewId)
{
   NewTitle = document.getElementsByName("NewTitleModify")[0].value.trim();
   NewMsg = document.getElementsByName("NewMsgModify")[0].value.trim();
   OccurTime = document.getElementsByName("OccurTimeModify")[0].value.trim();
   DeptList = getCheckedDept();
   
   if (NewTitle.length == 0 || NewMsg.length == 0)
   {
      alert("公告主题及公告内容不可为空白");
      return;
   }
   if (OccurTime.length > 0)
   {
      if (OccurTime.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(OccurTime))
      {
         alert("日期格式必须为 yyyy/mm/dd " + OccurTime);
         return;
      }
   }
   
   str = "cmd=write&NewId=" + NewId + "&NewTitle=" + encodeURIComponent(NewTitle) + 
         "&NewMsg=" + encodeURIComponent(NewMsg) + "&OccurTime=" + encodeURIComponent(OccurTime) + "&DeptList=" + encodeURIComponent(DeptList);
   url_str = "News_modify.php?";

   // alert(str);
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
         if (res.match(/^-\d+$/))  //failed
         {
            alert(MSG_OPEN_CONTENT_ERROR);
         }
         else  //success
         {
            alert("公告新增/修改成功，页面关闭后请自行刷新");
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
   <span class="bLink company"><span><?php echo $TitleStr; ?></span><span class="bArrow"></span></span>
</div>
<div id="content">
   <table class="searchField" border="0" cellspacing="0" cellpadding="0">
      <tr>
         <th>标题</th>
         <td><Input type=text name=NewTitleModify size=50 value="<?php echo $NewTitle;?>"></td>
      </tr>
      <tr>
         <th>内文：</th>
         <td><Input type=text name=NewMsgModify size=100 value="<?php echo $NewMsg;?>"></td>
      </tr>
      <tr>
         <th>发生时间 ：</th>
         <td>
            <input id="from0" type="text" name="OccurTimeModify" class="from" readonly="true" value="<?php echo $OccurTime;?>"/>
         </td>
      </tr>
      <tr>
         <th>选择部门：</th>
         <td>
            <div style="margin:20px 0;">
               <a id=displayExpandToDeptButton href="#" class="easyui-linkbutton" onclick="expandToDept()">显示当前所属部门</a>
            </div>
            <div class="easyui-panel" style="padding:5px">
               <ul id="depttree" class="easyui-tree" data-options="url:'<?php echo $web_path ?>Dept_tree_load.php',method:'get',animate:true,checkbox:true"></ul>
            </div>
            <script type="text/javascript">
               function expandToDept(){
                  $('#displayExpandToDeptButton').hide();
                  var dlstr = "<?php echo $DeptList; ?>";
                  var dlstr1 = dlstr.substring(1,dlstr.length-1);
                  var dlstr_array = dlstr1.split(",,");
                  for(var m=0; m<dlstr_array.length;m++)
                  {
                     var node = $('#depttree').tree('find',Number(dlstr_array[m]));
                     $('#depttree').tree('check', node.target);
                  }
                  $('#depttree').tree('expandToDept', node.target);
               }
               
               function getCheckedDept(){
                  var nodes = $('#depttree').tree('getChecked');
                  var s = '';
                  for(var i=0; i<nodes.length; i++){
                     if (s != '') s += ',,';
                     s += nodes[i].id;
                  }
                  return ',' + s + ',';
               }
            </script>         
         </td>
      </tr>
<?php
   if ($Status != 1)
   {
?>       
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyNewsContent"><input name="modifyNewsButton" type="button" value="保存" OnClick="modifyNewsContent(<?php echo $NewId;?>)"></a>
         </th>
      </tr>      
<?php
   }
?>   
   </table>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->