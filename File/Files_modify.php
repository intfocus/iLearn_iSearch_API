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
   
   //get data from client
   $cmd;
   $FileId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($FileId = check_number($_GET["FileId"])) == SYMBOL_ERROR)
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
      $str_query1 = "Select * from Files where FileId=$FileId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);         
         
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $FileId = $row["FileId"];
            $FileName = $row["FileName"];
            $FileTitle = $row["FileTitle"];
            $FileDesc = $row["FileDesc"];
            $CategoryId = $row["CategoryId"];
            $PageNo = $row["PageNo"];
            $FileType = $row["FileType"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "文档修改 (只允许修改文档标题及文档说明)";
            if ($Status == 1)
               $TitleStr = "文档查看 (上架状态无法修改)";
         }
         else
         {
            $FileId = 0;
            $FileName = "";
            $FileTitle = "";
            $FileDesc = "";
            $CategoryId = 1;
            $PageNo = 0;
            $FileType = 1;
            $TitleStr = "文档新增";
            $Status = 0;
         }
      }
   }
   else if ($FileId == 0) // Insert
   {
      $FileName = $_GET["FileName"];
      $FileCode = $_GET["FileCode"];
      $PAList = $_GET["PAList"] == "" ? "All":$_GET["PAList"];
      $ProductList = $_GET["ProductList"] == ""?"All":$_GET["ProductList"];
      $ParentId = $_GET["ParentId"];
      $str_query1 = "Insert into Files (FileName,FileCode,ParentId,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime,Status)" 
                  . " VALUES('$FileName','$FileCode',$ParentId,'$PAList','$ProductList',$user_id,now(),$user_id,now(),1)" ;
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
      $FileTitle = $_GET["FileTitle"];
      $FileDesc = $_GET["FileDesc"];
      //TODO EditUser=UserId
      $str_query1 = "Update Files set FileTitle='$FileTitle', FileDesc='$FileDesc', EditUser=$user_id, EditTime=now() where FileId=$FileId";
      
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
<title>武田 - 部门页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<Script Language=JavaScript>
function checkFileTypeModify() {
   FileName = document.getElementsByName("FilePathModify")[0].value;
   var pos1 = FileName.lastIndexOf('/');
   var pos2 = FileName.lastIndexOf('\\');
   var pos  = Math.max(pos1, pos2)
   if(pos>=0)
      FileName = FileName.substring(pos+1);
   pos3 = FileName.lastIndexOf('.');
   extension = FileName.substring(pos3+1);
   var Obj = document.getElementsByName("FileTypeModify");
   if(extension == "zip"){
      Obj[3].checked = true;
   }
   extensions = "mp4mp3";
   if(extensions.indexOf(extension)>-1){
      Obj[2].checked = true;
   }
   
   if(extension == "pdf" && !Obj[0].checked && !Obj[1].checked){
      Obj[0].checked = true;
   }
}

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
   window.setTimeout("expandTo()",1000);
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyFilesContent(FileId)
{
   FileTitle = document.getElementsByName("FileTitleModify")[0].value.trim();
   FileDesc = document.getElementsByName("FileDescModify")[0].value.trim();
   CategoryId = getSelectedId();
   CategoryFilePath = getSelectedFilePath();
   
   if (FileTitle.length == 0 || FileDesc.length == 0)
   {
      alert("文档标题及文档说明不可为空白");
      return;
   }
   
   // 如果 FileId == 0 ==> 代表新增, 走 submit 的方式
   if (FileId == 0)
   {
	  FileName = document.getElementsByName("FilePathModify")[0].value;
	  var pos1 = FileName.lastIndexOf('/');
	  var pos2 = FileName.lastIndexOf('\\');
     var pos  = Math.max(pos1, pos2)
	  if(pos>=0)
		FileName = FileName.substring(pos+1);
      document.getElementsByName("FileName")[0].value = FileName;
      document.getElementsByName("CategoryId")[0].value = CategoryId;
      document.getElementsByName("CategoryFilePath")[0].value = CategoryFilePath;
      document.getElementsByName("uploadFileForm")[0].submit();
      return;
   }
   
   // Else 代表 Update, 走 AJAX 方式
   str = "cmd=write&FileId=" + FileId + "&FileTitle=" + encodeURIComponent(FileTitle) + 
         "&FileDesc=" + encodeURIComponent(FileDesc) + "&CategoryId=" + CategoryId;
   url_str = "../File/Files_modify.php?";

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
            alert(MSG_OPEN_CONTENT_ERROR + res);
         }
         else  //success
         {
            alert("文档新增/修改成功，页面关闭后请自行刷新");
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
      <form enctype="multipart/form-data" name=uploadFileForm action="../File/Files_upload.php" method="POST">
         <Input type=hidden name="FileId" value="0">
         <Input type=hidden name="cmd" value="uploadFile">
         <Input type=hidden name="CategoryId" value="1">
         <Input type=hidden name="CategoryFilePath" value="">
<?php
   if ($FileId > 0)
   {
?>   
      <tr>
         <th>文档名称：</th>
         <td><Input type=text size=50 readonly="true" value="<?php echo $FileName;?>">(共 <?php echo $PageNo; ?> 页)</td>
      </tr>
<?php
   }
   else
   {
      echo "<Input type=hidden name=FileName>";
   }
?>
      <tr>
         <th>文档标题：</th>
         <td><Input type=text name=FileTitleModify size=50 value="<?php echo $FileTitle;?>"></td>
      </tr>
      <tr>
         <th>文档说明：</th>
         <td><Input type=text name=FileDescModify size=50 value="<?php echo $FileDesc;?>"></td>
      </tr>   
      <tr>
         <th>选择分类：</th>
         <td>
            <div style="margin:20px 0;">
               <a id=displayExpandToButton href="#" class="easyui-linkbutton" onclick="expandTo()">显示当前所属分类</a>
            </div>
            <div class="easyui-panel" style="padding:5px">
               <ul id="tt" class="easyui-tree" data-options="url:'<?php echo $web_path;?>Category_tree_load.php',method:'get',animate:true"></ul>
            </div>
            <script type="text/javascript">
               function collapseAll(){
                  $('#tt').tree('collapseAll');
               }
               function expandAll(){
                  $('#tt').tree('expandAll');
               }
               function expandTo(){
                  var node = $('#tt').tree('find',<?php echo $CategoryId; ?>);
                  $('#tt').tree('expandTo', node.target).tree('select', node.target);
                  $('#displayExpandToButton').hide();
               }
               function getSelected(){
                  var node = $('#tt').tree('getSelected');
                  if (node){
                     var s = node.text;
                     if (node.attributes){
                        s += ","+node.attributes.p1+","+node.attributes.p2;
                     }
                     //alert(s);
                     return s;
                  }
               }
               function getSelectedId(){
                  var node = $('#tt').tree('getSelected');
                  if (node){
                     return node.id;
                  }
                  else
                     return 0;
               }
               function getSelectedFilePath(){
                  var node = $('#tt').tree('getSelected');
                  if (node) {
                     return node.filepath;
                  }
                  else
                     return 0;
               }
            </script>         
         </td>
      </tr>
      <tr>
         <th>文档类型：</th>
         <td>
            <Input type=radio name=FileTypeModify value=1 <?php if ($FileType == 1) echo "checked";?>>ppt转pdf(横板)
            <Input type=radio name=FileTypeModify value=2 <?php if ($FileType == 2) echo "checked";?>>pdf(直版)
            <Input type=radio name=FileTypeModify value=3 <?php if ($FileType == 3) echo "checked";?>>视频
            <Input type=radio name=FileTypeModify value=3 <?php if ($FileType == 4) echo "checked";?>>Zip
         </td>
      </tr>
<?php
   if ($FileId == 0)
   {
?>
      <tr>
         <th>选取上传文档：</th>
         <td>
            <Input type=file accept="application/pdf,application/x-zip-compressed,audio/mp4,audio/mp3" size=50 name="FilePathModify" onchange="checkFileTypeModify();" />
         </td>
      </tr>
<?php
   }
?>      
<?php
   if ($Status != 1)
   {
?>       
      <tr>
         <th colspan="2" class="submitBtns">
            <a class="btn_submit_new modifyFilesContent">
               <input name="modifyFilesButton" type="button" value="保存 <?php if ($FileId > 0) echo "(只允许修改文档标题及文档说明)"; ?>" OnClick="modifyFilesContent(<?php echo $FileId;?>)">
            </a>
         </th>
      </tr>      
<?php
   }
?>
      </Form>
   </table>
</div>
</body>
</html>
<!--Step15 新增修改页面    结束 -->