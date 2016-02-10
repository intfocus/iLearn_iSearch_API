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
   $datasyz;
   $datacpmc;
   
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
   $CategoryId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($CategoryId = check_number($_GET["CategoryId"])) == SYMBOL_ERROR)
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
   
   $datasyz = array();
   $datacpmc = array();
   class StuFunction{
      public $functionId;
      public $functionName;
      public $createdTime;
   }
   
   $str_functionsyz="select FunctionId, FunctionName, CreatedTime from functions where FunctionType=1";
   if($rs = mysqli_query($link, $str_functionsyz)){
      while($row = mysqli_fetch_assoc($rs)){
         $syz = new StuFunction();
         $syz->functionId = $row["FunctionId"];
         $syz->functionName = $row["FunctionName"];
         $syz->createdTime = $row["CreatedTime"];
         array_push($datacpmc,$syz);
      }
   }
   
   $str_functioncpmc="select FunctionId, FunctionName, CreatedTime from functions where FunctionType=2";
   if($rs = mysqli_query($link, $str_functioncpmc)){
      while($row = mysqli_fetch_assoc($rs)){
         $cpmc = new StuFunction();
         $cpmc->functionId = $row["FunctionId"];
         $cpmc->functionName = $row["FunctionName"];
         $cpmc->createdTime = $row["CreatedTime"];
         array_push($datasyz,$cpmc);
      }
   }
   
   // for($i=0; $i<count($datacpmc); $i++)
   // {
      // $cpmc = $datacpmc[$i];
      // echo $cpmc -> functionId . "<br />";
      // echo $cpmc -> functionName . "<br />";
      // echo $cpmc -> createdTime . "<br />";
   // }
   // return;
   
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if ($cmd == "read") // Load
   {
      $str_query1 = "Select * from Categories where CategoryId=$CategoryId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $CategoryId = $row["CategoryId"];
            $CategoryName = $row["CategoryName"];
            $PAList = $row["PAList"];
            $ProductList = $row["ProductList"];
            $Status = $row["Status"];
            $FilePath = $row["FilePath"];
            $ParentId = $row["ParentId"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "分类修改";
            $CategoryParent = $row["CategoryPath"];
            if ($Status == 1)
               $TitleStr = "分类查看 (上架状态无法修改)";
         }
         else
         {
            $CategoryId = 0;
            $CategoryName = "";
            $FilePath = "";
            $ParentId = 1;
            $PAList = "";
            $ProductList = "";
            $TitleStr = "分类新增";
            $Status = 0;
            $CategoryParent = "";
         }
      }
   }
   else if ($CategoryId == 0) // Insert
   {
      $CategoryName = $_POST["CategoryName"];
      $FilePath = $_POST["FilePath"];
      $PAList = $_POST["PAList"] == "" ? "All":$_POST["PAList"];
      $ProductList = $_POST["ProductList"] == ""?"All":$_POST["ProductList"];
      $ParentId = $_POST["ParentId"];
      $CategoryPath = $_POST["CategoryParent"];
      $str_query1 = "Insert into Categories (CategoryName,FilePath,ParentId,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime,Status,CategoryPath)" 
                  . " VALUES('$CategoryName','$FilePath',$ParentId,'$PAList','$ProductList',$user_id,now(),$user_id,now(),1,$CategoryPath)" ;
      if(mysqli_query($link, $str_query1))
      {
		 $str_id = (string)mysqli_insert_id($link);
		 $str_query2 = "Update Categories set FilePath= '" . $FilePath ."/" . $str_id . "' where CategoryId=$str_id";
		 //echo $str_id;
         mysqli_query($link, $str_query2);
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
      $CategoryName = $_POST["CategoryName"];
      $FilePath = $_POST["FilePath"] . "/" . $CategoryId;
      $ParentId = $_POST["ParentId"];
      $PAList = $_POST["PAList"] == "" ? "All":$_GET["PAList"];
      $ProductList = $_POST["ProductList"] == ""?"All":$_GET["ProductList"];
      $CategoryPath = $_POST["CategoryParent"];
      //TODO EditUser=UserId
      $str_query1 = "Update Categories set CategoryName='$CategoryName', ParentId=$ParentId, FilePath='$FilePath', PAList='$PAList', ProductList='$ProductList', EditUser=$user_id, ";
      $str_query1 = $str_query1 . "EditTime=now(), CategoryPath='$CategoryPath' where CategoryId=$CategoryId";
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
<title>武田 - 分类页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<!-- Billy 2012/2/3 -->
<Script Language=JavaScript>
$(function(){
   $("#tt").tree({
      onClick: function(node){
         //alert(node.text);
         var categoryparent = categoryPath(node);
         document.getElementsByName("CategoryParent")[0].value = categoryparent;
      }
   });
});
function categoryPath(node){
   var parent = node;
   // alert(parent.text);
   var tree = $('#tt');
   var path = new Array();
   do{
      path.unshift(parent.text);
      var parent = tree.tree('getParent', parent.target);
   }while(parent);

   var pathStr = '';
   for(var i = 0; i < path.length; i++){
      pathStr += path[i];
      if(i < path.length -1){
         pathStr += "/";
      }
   }

   return pathStr;
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
   $('#depttree').tree({cascadeCheck:$(this).is(':checked')});
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
   var palstr = "<?php echo $PAList; ?>";
   var palstr1 = palstr.substring(1,palstr.length-1);
   var palstr_array = palstr1.split(",,");
   for(var i=0; i<palstr_array.length;i++)
   {
      var palcheck_array=document.getElementsByName("palist");
      for(var j=0;j<palcheck_array.length;j++)
      {
         if(palcheck_array[j].value==palstr_array[i])
         {         
            palcheck_array[j].checked=true;
         }
      }
   }
   
   var plstr = "<?php echo $ProductList; ?>";
   var plstr1 = plstr.substring(1,plstr.length-1);
   var plstr_array = plstr1.split(",,");
   for(var m=0; m<plstr_array.length;m++)
   {
      var plcheck_array=document.getElementsByName("productlist");
      for(var n=0;n<plcheck_array.length;n++)
      {
         if(plcheck_array[n].value==plstr_array[m])
         {         
            plcheck_array[n].checked=true;
         }
      }
   }
   window.setTimeout("expandTo()",2000);
   // window.setTimeout("expandToDept()", 2000);
}
//***Step23 PAList and ProductList begin
function PAListStr(){
   var rusult="";
   var check_array=document.getElementsByName("palist");
   for(var i=0;i<check_array.length;i++)
   {
      if(check_array[i].checked==true)
      {
         rusult=rusult+"," + check_array[i].value + ",";
      }
   }
   return rusult;
}

function ProductListStr(){
   var rusult="";
   var check_array=document.getElementsByName("productlist");
   for(var i=0;i<check_array.length;i++)
   {
       if(check_array[i].checked==true)
       {         
          rusult=rusult+"," + check_array[i].value + ",";
       }
   }
   return rusult;
}
//***Step23 PAList and ProductList end

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyCategoriesContent(CategoryId)
{
   var PAList = PAListStr();
   var ProductList = ProductListStr();
   CategoryName = document.getElementsByName("CategoryNameModify")[0].value.trim();
   ParentId = getSelectedId();
   FilePath = getSelectedFilePath();
   CategoryParent = document.getElementsByName("CategoryParent")[0].value;
   //alert(DeptList);
   
   if (CategoryName.length == 0)
   {
      alert("分类名称不可为空白");
      return;
   }
   
   if (CategoryName.length > 100)
   {
      alert("分类名称长度过长！请缩短后重新保存。");
      return;
   }
   
   // str = "cmd=write&CategoryId=" + CategoryId + "&CategoryName=" + encodeURIComponent(CategoryName) + 
         // "&ProductList=" + encodeURIComponent(ProductList) + "&PAList=" + encodeURIComponent(PAList) + 
         // "&ParentId=" + ParentId + "&FilePath=" + encodeURIComponent(FilePath);
   str = "cmd=write&CategoryId=" + CategoryId;
   url_str = "../Category/Categories_modify.php?";

   //alert(url_str + str);
   //return;
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str + str,
      data:{
         CategoryName:CategoryName,
         ProductList:ProductList,
         PAList:PAList,
         ParentId:ParentId,
         FilePath:FilePath,
         CategoryParent:CategoryParent,
      },
      cache: false,
      dataType: 'json',
      success: function(res)
      {
         //alert("Data Saved: " + res);
         res = String(res);
         if (res.match(/^-\d+$/))  //failed
         {
            alert(MSG_OPEN_CONTENT_ERROR);
         }
         else  //success
         {
            alert("分类新增/修改成功，页面关闭后请自行刷新");
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
   <input type="hidden" name="CategoryParent" value="<?php echo $CategoryParent;?>" />
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
         <th>分类名称：</th>
         <td><Input type=text name=CategoryNameModify size=50 value="<?php echo $CategoryName;?>"></td>
      </tr>
      <tr>
         <th>适应症：</th>
         <td>
<?php
for($i=0; $i<count($datasyz); $i++)
{
   $syz = $datasyz[$i];
?>
           <input type="checkbox" value="<?php echo $syz->functionId ?>" name="palist"/><?php echo $syz->functionName ?>
<?php
}
?>
         </td>
      </tr>
      <tr>
         <th>产品名称：</th>
         <td>
<?php
for($i=0; $i<count($datacpmc); $i++)
{
   $cpmc = $datacpmc[$i];
?>
           <input type="checkbox" value="<?php echo $cpmc->functionId ?>" name="productlist"/><?php echo $cpmc->functionName ?>
<?php
}
?>
         </td>
      </tr>
      <tr>
         <th>选择分类：</th>
         <td>
            <div style="margin:20px 0;">
               <a id=displayExpandToButton href="#" class="easyui-linkbutton" onclick="expandTo()">显示当前所属分类</a>
            </div>
            <div class="easyui-panel" style="padding:5px">
               <ul id="tt" class="easyui-tree" data-options="url:'<?php echo $web_path ?>Category_tree_load.php',method:'get',animate:true"></ul>
            </div>
            <script type="text/javascript">
               function collapseAll(){
                  $('#tt').tree('collapseAll');
               }
               function expandAll(){
                  $('#tt').tree('expandAll');
               }
               function expandTo(){
                  var node = $('#tt').tree('find',<?php echo $ParentId; ?>);
                  $('#tt').tree('expandTo', node.target).tree('select', node.target);
                  $('#displayExpandToButton').hide();
                  $('#tt').tree('collapseAll');
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
               
               function getChecked(){
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
                  if (node){
                     return node.filepath;
                  }
                  else
                     return 0;
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
            <a class="btn_submit_new modifyCategoriesContent"><input name="modifyCategoriesButton" type="button" value="保存" OnClick="modifyCategoriesContent(<?php echo $CategoryId;?>)"></a>
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