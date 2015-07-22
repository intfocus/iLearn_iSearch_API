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
   $TrainingId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($TrainingId = check_number($_GET["TrainingId"])) == SYMBOL_ERROR)
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
      $str_query1 = "Select * from Trainings where TrainingId=$TrainingId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $TrainingId = $row["TrainingId"];
            $TrainingName = $row["TrainingName"];
            $SpeakerName = $row["SpeakerName"];
            $TrainingBegin = date("Y/m/d",strtotime($row["TrainingBegin"]));
            $TrainingEnd = date("Y/m/d",strtotime($row["TrainingEnd"]));
            $StartDate = date("Y/m/d",strtotime($row["StartDate"]));
            $EndDate = date("Y/m/d",strtotime($row["EndDate"]));
            $Status = $row["Status"];
            $TrainingLocation = $row["TrainingLocation"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $TrainingMemo = $row["TrainingMemo"];
            $TrainingManager = $row["TrainingManager"];
            $ApproreLevel = $row["ApproreLevel"]; 
            // $OccurTime = $row["OccurTime"] == null ? '' : date("Y/m/d",strtotime($row["OccurTime"]));
            $TitleStr = "课程修改";
            if ($Status == 1)
               $TitleStr = "课程查看 (上架状态无法修改)";
         }
         else
         {
            $TrainingId = 0;
            $TrainingName = "";
            $SpeakerName = "";
            $TrainingBegin = "";
            $TrainingEnd = "";
            $TitleStr = "课程新增";
            $StartDate = "";
            $EndDate = "";
            $TrainingLocation = "";
            $TrainingMemo = "";
            $TrainingManager = "";
            $Status = 0;
            $ApproreLevel = 1;
         }
      }
   }
   else if ($TrainingId == 0) // Insert
   {
      // $TraininContent = file_get_contents("php://input");
      // //$jsonResult = htmlspecialchars_decode($fileContent);
      // $Trainin = json_decode($TraininContent);
      $TrainingName = $_POST["TrainingName"];
      $SpeakerName = $_POST["SpeakerName"];
      $TrainingBegin = "'" . $_POST["TrainingBegin"] . "'";
      $TrainingEnd = "'" . $_POST["TrainingEnd"] . "'";
      $StartDate = "'" . $_POST["StartDate"] . "'";
      $EndDate = "'" . $_POST["EndDate"] . "'";
      $TrainingLocation = $_POST["TrainingLocation"];
      $TrainingMemo = $_POST["TrainingMemo"];
      $TrainingManager = $_POST["TrainingManager"];
      $ApproreLevel = $_POST["ApproreLevel"];
      // $TrainingName = $_GET["TrainingName"];
      // $SpeakerName = $_GET["SpeakerName"];
      // $TrainingBegin = "'" . $_GET["TrainingBegin"] . "'";
      // $TrainingEnd = "'" . $_GET["TrainingEnd"] . "'";
      // $StartDate = "'" . $_GET["StartDate"] . "'";
      // $EndDate = "'" . $_GET["EndDate"] . "'";
      // $TrainingLocation = $_GET["TrainingLocation"];
      // $TrainingMemo = $_GET["TrainingMemo"];
      // $TrainingManager = $_GET["TrainingManager"];
      // $ApproreLevel = $_GET["ApproreLevel"];
      if ($TrainingEnd == "''")
         $TrainingEnd = "NULL";
      $str_query1 = "Insert into Trainings (TrainingName,SpeakerName,TrainingBegin,TrainingEnd,StartDate,EndDate,TrainingLocation,TrainingMemo,TrainingManager,Status,ApproreLevel,CreatedUser,CreatedTime,EditUser,EditTime)" 
                  . " VALUES('$TrainingName','$SpeakerName',$TrainingBegin,$TrainingEnd,$StartDate,$EndDate,'$TrainingLocation','$TrainingMemo','$TrainingManager',0,$ApproreLevel,$user_id,now(),$user_id,now())" ;
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
      $TrainingName = $_POST["TrainingName"];
      $SpeakerName = $_POST["SpeakerName"];
      $TrainingBegin = "'" . $_POST["TrainingBegin"] . "'";
      $TrainingEnd = "'" . $_POST["TrainingEnd"] . "'";
      $StartDate = "'" . $_POST["StartDate"] . "'";
      $EndDate = "'" . $_POST["EndDate"] . "'";
      $TrainingLocation = $_POST["TrainingLocation"];
      $TrainingMemo = $_POST["TrainingMemo"];
      $TrainingManager = $_POST["TrainingManager"];
      $ApproreLevel = $_POST["ApproreLevel"];
      if ($TrainingEnd == "''")
         $TrainingEnd = "NULL";
      //TODO EditUser=UserId
      $str_query1 = "Update Trainings set TrainingName='$TrainingName', SpeakerName='$SpeakerName', TrainingBegin=$TrainingBegin, 
         TrainingEnd=$TrainingEnd, StartDate=$StartDate, EndDate=$EndDate, TrainingLocation='$TrainingLocation', 
         TrainingMemo = '$TrainingMemo', TrainingManager='$TrainingManager', 
         ApproreLevel='$ApproreLevel', EditUser=$user_id, EditTime=now() where TrainingId=$TrainingId";
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
<title>武田 - 课程页面</title>
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
   var i = <?php echo $ApproreLevel?>;
   i = i-1;
   document.getElementsByName("ApproreLevel")[0].options[i].selected = true;
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyTrainingsContent(TrainingId)
{
   TrainingName = document.getElementsByName("TrainingName")[0].value.trim();
   SpeakerName = document.getElementsByName("SpeakerName")[0].value.trim();
   TrainingBegin = document.getElementsByName("TrainingBegin")[0].value.trim();
   TrainingEnd = document.getElementsByName("TrainingEnd")[0].value.trim();
   StartDate = document.getElementsByName("StartDate")[0].value.trim();
   EndDate = document.getElementsByName("EndDate")[0].value.trim();
   TrainingLocation = document.getElementsByName("TrainingLocation")[0].value.trim();
   TrainingMemo = document.getElementsByName("TrainingMemo")[0].value.trim();
   TrainingManager = document.getElementsByName("TrainingManager")[0].value.trim();
   ApproreLevel = document.getElementsByName("ApproreLevel")[0].value.trim();

   if (TrainingName.length == 0 || SpeakerName.length == 0)
   {
      alert("课程名称及讲师名称不可为空白");
      return;
   }
   
   if (TrainingName.length > 100 || SpeakerName.length > 100 || TrainingLocation.length > 1000 || TrainingMemo.length > 1000 || TrainingManager.length > 255)
   {
      alert("课程名称，讲师名称，报名地点， 课程备注及课程负责人长度过长！请缩短后重新保存。");
      return;
   }
   
   if (TrainingBegin.length > 0)
   {
      if (TrainingBegin.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(TrainingBegin))
      {
         alert("日期格式必须为 yyyy/mm/dd " + TrainingBegin);
         return;
      }
   }
   else{
      alert("报名起始时间不可为空白");
      return;
   }
   
   if (TrainingEnd.length > 0)
   {
      if (TrainingEnd.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(TrainingEnd))
      {
         alert("日期格式必须为 yyyy/mm/dd " + TrainingEnd);
         return;
      }
   }
   else{
      alert("报名截至时间不可为空白");
      return;
   }
   
   if (StartDate.length > 0)
   {
      if (StartDate.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(StartDate))
      {
         alert("日期格式必须为 yyyy/mm/dd " + StartDate);
         return;
      }
   }
   else{
      alert("课程起始时间不可为空白");
      return;
   }
   
   if (EndDate.length > 0)
   {
      if (EndDate.length != 10)
      {
         alert("日期格式必须为 yyyy/mm/dd");
         return;
      }
      var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
      if (!reg.exec(EndDate))
      {
         alert("日期格式必须为 yyyy/mm/dd " + EndDate);
         return;
      }
   }
   else{
      alert("课程截至时间不可为空白");
      return;
   }
   
   str = "cmd=write&TrainingId=" + TrainingId + "&TrainingName=" + encodeURIComponent(TrainingName) + 
         "&SpeakerName=" + encodeURIComponent(SpeakerName) + "&TrainingBegin=" + encodeURIComponent(TrainingBegin) + 
         "&TrainingEnd=" + encodeURIComponent(TrainingEnd) + "&StartDate=" + encodeURIComponent(StartDate) + 
         "&EndDate=" + encodeURIComponent(EndDate) + "&TrainingLocation=" + encodeURIComponent(TrainingLocation) + 
         "&TrainingMemo=" + encodeURIComponent(TrainingMemo) + "&TrainingManager=" + encodeURIComponent(TrainingManager) + 
         "&ApproreLevel=" + encodeURIComponent(ApproreLevel);
   url_str = "Trainings_modify.php?cmd=write&TrainingId=" + TrainingId;

   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "POST",
      url: url_str,
      data:{
         TrainingName:TrainingName,
         SpeakerName:SpeakerName,
         TrainingBegin:TrainingBegin,
         TrainingEnd:TrainingEnd,
         StartDate:StartDate,
         EndDate:EndDate,
         TrainingLocation:TrainingLocation,
         TrainingMemo:TrainingMemo,
         TrainingManager:TrainingManager,
         ApproreLevel:ApproreLevel
      },
      cache: false,
      dataType:'json',
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
            alert("课程新增/修改成功，页面关闭后请自行刷新");
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
         <th>课程名称</th>
         <td><Input type="text" name="TrainingName" size="100" value="<?php echo $TrainingName;?>" /></td>
      </tr>
      <tr>
         <th>讲师名称：</th>
         <td><input type="text" name="SpeakerName" size="100" value="<?php echo $SpeakerName;?>" /></td>
      </tr>
      <tr>
         <th>报名（起始/截止）时间：</th>
         <td>
            <input id="from8" type="text" name="TrainingBegin" class="from" readonly="true" value="<?php echo $TrainingBegin ?>" /> 
            ~ 
            <input id="to8" type="text" class="to" name="TrainingEnd" readonly="true" value="<?php echo $TrainingEnd ?>" />
         </td>
      </tr>
      <tr>
         <th>课程（起始/截至）时间：</th>
         <td>
            <input id="from9" type="text" name="StartDate" class="from" readonly="true" value="<?php echo $StartDate ?>" /> 
            ~ 
            <input id="to9" type="text" class="to" name="EndDate" readonly="true" value="<?php echo $EndDate ?>" />
         </td>
      </tr>
      <tr>
         <th>报名地点：</th>
         <td>
            <input type="text" name="TrainingLocation" size="100" value="<?php echo $TrainingLocation ?>" />
         </td>
      </tr>
      <tr>
         <th>课程负责人：</th>
         <td>
            <input type="text" name="TrainingManager" size="100" value="<?php echo $TrainingManager ?>" />
         </td>
      </tr>
      <tr>
         <th>报名审批层级：</th>
         <td>
            <select name="ApproreLevel">
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>               
            </select>
         </td>
      </tr>
      <tr>
         <th>课程备注：</th>
         <td><Textarea name="TrainingMemo" rows="30" cols="100"><?php echo $TrainingMemo;?></textarea></td>
      </tr>
<?php
   if ($Status != 1)
   {
?>       
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_Training modifyTrainingsContent"><input name="modifyTrainingsButton" type="button" value="保存" OnClick="modifyTrainingsContent(<?php echo $TrainingId;?>)"></a>
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