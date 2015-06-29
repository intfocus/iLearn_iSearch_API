<?php
/////////////////////////
//OSC_index_adm.php
//
// #001 created by Phantom, 2014/09/12
//  1. For 安泰客製, 給系統管理者使用
//  報表管理, 查詢 tab 拿掉 
//////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");
   define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php");
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);

   /////////////////////////////////////////
   // Default value of the customer (不同客戶, 這邊要修改)
   /////////////////////////////////////////
   define(NAME, "安泰銀行");
   define(COMPANY_NUMBER, "86928561");
   define(CHAIRMAN, "");
   define(TEL,"02-81012277");
   define(FAX,"02-81016094");
   define(CONTACT,"");
   define(CONTACT_TEL,"02-81012277");
   define(CONTACT_EXT,"");
   define(CONTACT_MOBILE,"");
   define(CONTACT_EMAIL,"");
   define(CONTACT_EMAIL2,"");
   define(ADDRESS,"台北市信義區信義路五段7號40樓");
   define(ZIP,"");
   define(UNIFORM_NUMBER,"86928561");
   define(UNIFORM_TYPE,0);
   define(UNIFORM_TITLE, "安泰銀行");
   define(UNIFORM_ADDRESS,"台北市信義區信義路五段7號40樓");
   define(COMPANY_TYPE,"金融證卷");
   define(COMPANY_SIZE,2000);
   // 不用修改的部分
   define(LOGIN_NAME,"openfind12345678"); // The username can't be login
   define(PASSWORD,"12342234"); // No such MD5, will never be login
   define(VALIDCODE,"00000000"); // default valid code 
   define(REMAIN,"1");
   define(UPLOADMASK,1);
   define(STATUS,0);
   define(NETDISK,0);
   define(REMOVABLEDISK,0);
   define(CONF,"0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
   define(KEYWORD1,"");
   define(EXPRESSENABLE,0);
   define(EXPRESSTIMEOUT,5);
   
   if (file_exists(FILE_NAME) && file_exists(CONFIGFUNCTION_PHP))
   {
      include(FILE_NAME);
      include(CONFIGFUNCTION_PHP);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   session_start();
   if (!session_is_registered("GUID") || !session_is_registered("GUID_ADM") ||
       !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit();
   }
   if ($_SESSION["GUID_ADM"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit();
   }
   $login_level = $_SESSION["loginLevel"];
   $login_name = $_SESSION["loginName"];
   session_write_close();

   $systemAdminFlag = 1;
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
<script type="text/javascript" src="js/PMarkFunction_adm.js"></script>
<script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
<script type="text/javascript" src="openflashchart/js/json/json2.js"></script>
<script>
</script>
<!--[if lt IE 10]>
<script type="text/javascript" src="lib/PIE.js"></script>
<![endif]-->
<title>Openfind P-Marker</title>
<!-- BEG_ORISBOT_NOINDEX -->
<!-- Billy 2012/2/3 -->
<?php
   
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(DEFAULT_GUID, "00000000_0000_0000_0000_000000000000");
   define(PAGE_SIZE, 100);
   
   //define(EXTREME_TYPE_NUMBER, '8');  //個資類型  comment out, let it be a variable rather than a constant

   define(AVAILABLE, 0);
   define(TRIAL, 0);
   define(DB_ERROR, -1);

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
 
   //使用者、系統資訊
   $uniform_title;          //公司名稱
   $remain;                 //剩餘次數
   $contact_email;          //管理者信箱
   $expire_time;            //有效日期
   $temp_expire_time;
   $page_default_no;        //預設頁數
   $page_size;              //每頁報表數
   $page_num;
   $conf;
   $keyword_conf;
   
   //預設風險個資類型
   $rItemW_default = array(0, 0, 0, 0, 0, 0, 0, 0); 
   $rItemW_default_temp;
   $temp_begin;
   $extremeTypeNumber;
   $riskExtreme;
   $riskHigh;
   $riskLow;

   $type_number = 7;        //個資類型數目，預設七種
   $type8_name = "生日";    //第八種個資的名稱，預設"生日"

   //報表
   $rID;
   $rNameW;                 //報表名稱
   $temp_time;
   $rTimeW;                 //產生日期
   $vHighW_file;            //極高風險-檔案
   $HighW_file;             //高風險-檔案
   $MediumW_file;           //中風險-檔案
   $LowW_file;              //低風險-檔案
   $totalW_file;            //檔案總數
   $vHighW_data;            //極高風險-個資
   $HighW_data;             //高風險-個資
   $MediumW_data;           //中風險-個資
   $LowW_data;              //低風險-個資
   $totalW_data;            //個資總數
   $rItemW;                 //掃描項目
   $rItemW_temp1;
   $rItemW_temp2;
   //$rItemW_map = array("身分證", "手機號碼", "地址", "電子郵件地址", "信用卡號碼", "姓名", "市話號碼", "集保帳號");  //掃描項目對應  comment out, no use
   $tRangeW_begin;          //產生區間-開始
   $temp_end;
   $tRangeW_end;            //產生區間-結束
   $cTotalW;                //電腦
   $i;
   $flag;
   $scanMode;
   $scanTime;

   date_default_timezone_set(TIME_ZONE);  //set timezone
   $GUID = DEFAULT_GUID;

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }

   // GUID = (00000000_0000_0000_0000_000000000000)
   // 如果路徑不存在 /usr/local/www/apache22/data/upload_old/$GUID => mkdir
   $whiteListPath = "/usr/local/www/apache22/data/upload_old/$GUID";
   if (!file_exists($whiteListPath))
      system("mkdir -p -m 0774 $whiteListPath");

   $str_query = "select * from customer where GUID='$GUID'";
   if ($result = mysqli_query($link, $str_query))  //query success
   {
      $row_num = mysqli_num_rows($result);
      if ($row_num == 0)
      {
         //////////////////////////////////////////////////////////
         // 如果 DB Customer 不存在這筆資料 => Insert Customer
         //////////////////////////////////////////////////////////
         mysqli_free_result($result);
         unset($row);
         $timestr = date('Y-m-d H:i:s', time());

         // 產生 expire_time
         // (1) 找 customer 裡面的最大值
         // (2) 如果找不到, 用 $timestr 加一年
         $str_query = "select expire_time from customer order by expire_time DESC";
         $result = mysqli_query($link, $str_query);
         $row_num = mysqli_num_rows($result);
         if ($row_num > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $expire_time = $row["expire_time"];
            unset($row);
         }
         else
         {
            $expire_time = date('Y-m-d H:i:s', strtotime('+1 years'));
         }
         mysqli_free_result($result);

         $str_insert = "Insert customer " . 
                       "(GUID,name,company_number,chairman,tel,fax,contact,contact_tel,contact_ext,contact_mobile," .
                       "contact_email,contact_email2,address,zip,uniform_number,uniform_type,uniform_title,uniform_address," .
                       "company_type,company_size,create_time,last_modified_time,providerID,provider_privilege,login_name," .
                       "password,validcode,remain,expire_time,uploadMask,status,company_no,netDisk,removableDisk,conf," .
                       "keyword1,expressEnable,expressTimeout)" . 
                       "VALUES('$GUID','" . NAME . "','" . COMPANY_NUMBER . "','" . CHAIRMAN . "','" . TEL . "','" . FAX .
                              "','" . CONTACT . "','" . CONTACT_TEL . "','" . CONTACT_EXT . "','" . CONTACT_MOBILE .
                              "','" . CONTACT_EMAIL . "','" . CONTACT_EMAIL2 . "','" . ADDRESS . "','" . ZIP . "','" . UNIFORM_NUMBER .
                              "'," . UNIFORM_TYPE . ",'" . UNIFORM_TITLE . "','" . UNIFORM_ADDRESS .
                              "','" . COMPANY_TYPE . "'," . COMPANY_SIZE . ",'$timestr','$timestr',0,0,'" . LOGIN_NAME .
                              "','" . PASSWORD . "','" . VALIDCODE . "'," . REMAIN . ",'$expire_time'," . UPLOADMASK .
                              "," . STATUS . ",''," . NETDISK . "," . REMOVABLEDISK . ",'" . CONF .
                              "','" . KEYWORD1 . "'," . EXPRESSENABLE . "," . EXPRESSTIMEOUT . ");";
         mysqli_query($link, $str_insert);
      }
      else
      {
         $flag = 0;
         $row = mysqli_fetch_assoc($result);
         $uniform_title =$row["name"];
         $remain = $row["remain"];
         $validcode = $row["validcode"];
         $contact_email = $row["contact_email"];
         $temp_expire_time = $row["expire_time"];
         $expire_time = date("Y/m/d", strtotime($temp_expire_time));
         $uploadMask = $row["uploadMask"];
         $status = $row["status"];
         $netDisk = $row["netDisk"];
         $removableDisk = $row["removableDisk"];
         $conf = $row["conf"];
         $scanMode = $row["expressEnable"];
         $scanTime = $row["expressTimeout"];
         mysqli_free_result($result);
         unset($row);
      }
   }
   else
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo DB_ERROR;  
   }

   $systemScanDirEnabled = get_config_by_name($link, $GUID, "systemScanDirEnabled");
   if ($systemScanDirEnabled != 1)
      $systemScanDirEnabled = 0;

   ////////////////
   // check the content of conf
   ////////////////

   if($conf[1] == "1"){              // 是否顯示關鍵字掃瞄功能
      $keyword_conf = 1;
   }
   
   $str_query = "
      select *
      from riskCategory
      where GUID = '" . $GUID . "'";
     
   //----- 讀取預設掃描項目 -----
   if ($result = mysqli_query($link, $str_query))
   {
      // if no data found in riskCategory then insert new data
      $row_number = mysqli_num_rows($result);
      if($row_number == 0)
      {
         $str_query = "
            insert into riskCategory 
            (GUID,low,high,extreme,extreme_type_num,extreme_type)
            values ('" . $GUID . "',5,20,20,2,'0,1,2,3,4,5,6')"; 

         mysqli_query($link, $str_query);
         $rItemW_default_temp = "0,1,2,3,4,5,6"; 
         $extremeTypeNumber = 2;
         $riskExtreme = 20;
         $riskHigh = 20;
         $riskLow = 5;
         mysqli_free_result($result);
         unset($row);
      }
      else
      {
         $row = mysqli_fetch_assoc($result);
         //$flag = 1;
         $rItemW_default_temp = $row["extreme_type"];
         $extremeTypeNumber = $row["extreme_type_num"];
         $riskExtreme = $row["extreme"];
         $riskHigh = $row["high"];
         $riskLow = $row["low"];
         if ((int)($row["type8_enable"]) == 1)
         {
            $type_number = 8;
            $type8_name = $row["type8_name"];
         }
         mysqli_free_result($result);
         unset($row);
      }

      for ($i = 0; $i < strlen($rItemW_default_temp); $i++)
      {
         if ($rItemW_default_temp[$i] >= '0' && $rItemW_default_temp[$i] < $type_number)
            $rItemW_default[(int)$rItemW_default_temp[$i]] = 1;
      }
   }
   else
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo DB_ERROR;  
   }
   
?>
<Script Language=JavaScript>
var checkBoxType = 8  // even if we have only seven types here, g_defaultExtremeType[7] won't be referenced, so it's okay to leave it here
var g_defaultExtremeType = new Array(checkBoxType);
g_defaultExtremeType[0] = <?php echo $rItemW_default[0] ?>;
g_defaultExtremeType[1] = <?php echo $rItemW_default[1] ?>; 
g_defaultExtremeType[2] = <?php echo $rItemW_default[2] ?>; 
g_defaultExtremeType[3] = <?php echo $rItemW_default[3] ?>; 
g_defaultExtremeType[4] = <?php echo $rItemW_default[4] ?>; 
g_defaultExtremeType[5] = <?php echo $rItemW_default[5] ?>; 
g_defaultExtremeType[6] = <?php echo $rItemW_default[6] ?>;
g_defaultExtremeType[7] = <?php echo $rItemW_default[7] ?>; 

var user_searchHint = "電腦、人員或部門名稱";

g_validcode = "<?php echo $validcode ?>";

function lockFunction(obj, n)
{
   if (g_defaultExtremeType[n] == 1)
      obj.checked = true;
} 

function click_logout()  //log out
{
   document.getElementsByName("logoutform")[0].submit();
}

function uploadAsset()
{
   window.open("upload_computerListPage.php");
}

function uploadReplace()
{
   window.open("upload_replaceListPage.php");
}

function uploadKeyword()
{
   window.open("upload_keywordListPage.php");
}

</Script>
</head>
<body Onload="loaded();">
<div id="searchContent" class="blockUI" style="display:none;">
   <span class="dialog">
      <div class="header">
         <span id="closeDialog" class="close" OnClick="hideContent();"></span>
         <span class="title">風險檔案內容</span>
      </div>
      <div class="content">
         <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th class="title left" colspan="2"><span>檔案基本資料</span></th>
               <th class="title right">個資搜尋結果</th>
            </tr>
            <tr>
               <th><span>電腦編號：</span></th>
               <td>x001</td>
               <td class="resultW" rowspan="8"><div class="result">M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866......M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866..M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866....M12XXXX1276、王X銘、0986XXX110、賴X奇、A20XXX866......</div></td>
            </tr>
            <tr>
               <th><span>人員名稱：</span></th>
               <td>Mario</td>
            </tr>
            <tr>
               <th><span>部門：</span></th>
               <td>行銷部</td>
            </tr>
            <tr>
               <th><span>檔案類型：</span></th>
               <td>word</td>
            </tr>
            <tr>
               <th><span>最後編輯：</span></th>
               <td>2012/02/04 23:00</td>
            </tr>
            <tr>
               <th><span>個資數量：</span></th>
               <td>1,100</td>
            </tr>
            <tr>
               <th><span>個資種類：</span></th>
               <td>N、A、T</td>
            </tr>
            <tr>
               <th><span>檔案路徑：</span></th>
               <td>...../D/my document/報名清單</td>
            </tr>
         </table>
      </div>
   </span>
</div>
<div id="loadingWrap" class="nodlgclose loading" style="display:none;">
   <div id="loadingContent">
      <span id="loadingContentInner">
         <span id="loadingIcon"></span><span id="loadingText">讀取中(需要數分鐘)...</span>
      </span>
   </div>
</div>
<div id="header">
   <form name=logoutform action=logout_adm.php>
   </form>
   <span class="global">使用者 : <?php echo $login_name ?>
      <font class="logout" OnClick="click_logout();">登出</font>&nbsp;
      <a href="onlinehelp/index.html" target=_blank><img style="vertical-align:middle" src="images/icon_onlinehelp.png" title="使用說明"/></a>
   </span>
   <span class="logo"></span>
   <span class="creditW">
      <span>系統管理者功能頁面</span>
   </span>
</div>
<div id="banner">
   <span class="bLink first"><span>單位名稱</span><span class="bArrow"></span></span>
   <span class="bLink company"><span><?php echo $uniform_title ?></span><span class="bArrow"></span></span>
</div>
<div id="content">
  
   <!-- Report Container -->
   <div id="enterpiceReport" style="display:block;">
      <ul class="mainTabW">
         <li class="active"><span class="tabIcon search"></span><span>進度查詢</span></li>
         <li><span class="tabIcon system"></span><span>系統資訊</span></li>
         <li><span class="tabIcon setting"></span><span>設定</span></li>
         <li><span class="tabIcon whitelist"></span><span>白名單</span></li>
         <li><span class="tabIcon branch"></span><span>分行管理</span></li>
         <li><span class="tabIcon userMgmt"></span><span>系統管理者</span></li>
      </ul>
      <div class="mainContent">

         <!-- 分行查詢 -->
<?php if (file_exists("OSC_branch_query.php")) include("OSC_branch_query.php"); ?>

         <!-- 系統資訊 -->
         <div class="container systemC" style="display:none;">
            <table class="sysInfo" border="0" cellspacing="0" cellpadding="0">
               <tr>
                  <th>註冊名稱</th>
                  <td><?php echo $uniform_title ?></td>
               </tr>
               <tr>
                  <th>服務名稱</th>
                  <td>Openfind P-Marker Cloud Service <?php if($status == TRIAL) echo "試用版"?></td>
               </tr>
               <tr>
                  <th>剩餘掃描次數</th>
                  <td><?php echo $remain ?> 次</td>
               </tr>
               <!--
               <tr>
                  <th>預設用戶端密碼</th>
                  <td>
                     <span id="curValidcode" class="curPW"><span id="Validcode"><?php echo $validcode ?> </span><a href="#" id="changeValidcodeBtn" >變更密碼</a></span>
                     <form name="formValidcode">
                        <ul id="changeValidcode" style="display:none;">
                           <li><span class="title">舊密碼 :</span> <input id="oldValidcode"<?php echo "value=\"$validcode\"" ?>></li>
                           <li><span class="title">新密碼 :</span> <input id="newValidcode" type="password"><font color=red>(不能有'及-)</font></li>
                           <li><span class="title">確認新密碼 :</span> <input id="newValidcodeConfirm" type="password"></li>
                           <li><button id="submitChangeValidcode" type="button">確認</button><button id="cancelChangeValidcode" type="button">取消</button></li>
                        </ul>
                     </form>
                  </td>
               </tr>
               -->
               <tr>
                  <th>系統管理者密碼</th>

                  <td>
                     <span id="curAdminPW" class="curPW">●●●●●●●●●● <a href="#" id="changeAdminPWBtn" >變更密碼</a></span>
                     <form name="formAdminPW">
                        <ul id="changeAdminPW" style="display:none;">
                           <li><span class="title">舊密碼 :</span> <input id="oldAdminPW" type="password"></li>
                           <li><span class="title">新密碼 :</span> <input id="newAdminPW" type="password"></li>
                           <li><span class="title">確認新密碼 :</span> <input id="newAdminPWConfirm" type="password"></li>
                           <input id="loginLevel" type="hidden" value="<?php echo $login_level; ?>">
                           <li><button id="submitChangeAdminPW" type="button">確認</button><button id="cancelChangeAdminPW" type="button">取消</button></li>
                        </ul>
                     </form>
                  </td>
               </tr>
               <!--
               <tr>
                  <th>管理者信箱</th>
                  <td><Input type=text size=50 maxlength=50 name=contact_email value="<?php echo $contact_email ?>"><button id="changeContactEmail" type="button">修改</button></td>
               </tr>
               -->
               <tr>
                  <th>到期日</th>
                  <td><?php echo $expire_time ?></td>
               </tr>
               <tr>
                  <th>會員同意書</th>
                  <td><a href="/privacy.html" target="_blank">同意書</a></td>
               </tr>
            </table>
         </div>

         <!-- 設定 開始 -->
         <div class="container settingC" style="display:none;">
            <div class="settingW">
<?php
   if ($systemAdminFlag == 1)
   {
      echo "<table class=sysInfo border=0 cellspacing=0 cellpadding=0>";
      echo "<tr><th>(身份為系統管理者，此設定會套用到所有單位)</th></tr>";
      echo "</table>";
   }
?>
<?php
      if($keyword_conf == 1){
?>
            <div class="title">盤點內容設定</div>
               <div class="content">
                  <table>
                     <tbody>
                        <tr>
                           <td>
                              <div>
                                 <label><input name="scanType" type="checkbox" checked> 個資盤點</label><br />
                                 <label><input name="scanType" type="checkbox" checked> 關鍵字盤點</label>
                                    <span class="toolMenu">
                                       <span class="btn new" OnClick="uploadKeyword();">匯入關鍵字清單</span>
                                    </span>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
<?php
      }
?>
            <div class="title">上傳內容設定</div>
               <div class="content">        
                  <table>
                     <tbody>
                        <tr>
                           <td>
                              <div>
                                 <label><input name="uploadMask" value="0" type="radio" <?php if ($uploadMask == 0) echo "checked";?>> 僅記錄檔案路徑及個資筆數</label><br />
                                 <label><input name="uploadMask" value="1" type="radio" <?php if ($uploadMask == 1) echo "checked";?>> 記錄檔案路徑、個資筆數與資料片段</label><br />
                                 <div style="float:right; margin-top:10px"><a href="/upload_explain.html" target="_blank"><img hspace="3" style="vertical-align:middle" src="/p-marker/images/icon_help.gif" border="0">詳細說明&raquo;</a></div>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            <div class="title">盤點範圍設定</div>
               <div class="content">        
                  <table>
                     <tbody>
                        <tr>
                           <td>
                              <div>
                                 <label><input name="netDisk" type="checkbox" <?php if ($netDisk == 1) echo "checked";?>> 搜尋網路磁碟機</label><br/>
                                 <label><input name="removableDisk" type="checkbox" <?php if($removableDisk == 1) echo "checked";?>> 搜尋卸除式存放裝置(例如 USB、外接硬碟)</label>
                              </div>
                           </td>
                        </tr>
                        <tr>
                           <td><input name="systemScanDirEnabled" type="checkbox" <?php if ($systemScanDirEnabled == 1) echo "checked";?>> 
                              指定盤點路徑 (每台 client 只掃描下方的指定目錄，以及自定目錄)<br/><br/>
                              每行限填寫一個盤點路徑、範例：<br/>
                              C:\<br/>
                              C:\Downloads<br/>
                              C:\Users\Public\Documents<br/>
                              D:\<br/>
                              <br/>
                              請輸入要盤點的路徑：<br/><br />
                              <Textarea name="systemScanDirContent" cols=50 rows=10><?php
      /////////////////////////////
      // Read from systemScanDir.txt as default
      /////////////////////////////
      $guid_dir_path = '/usr/local/www/apache22/data/upload_old' . "/$GUID";
      if (!file_exists($guid_dir_path)) {
         system("mkdir -p -m 0774 $guid_dir_path");
      }
      $systemScanDirPath = $guid_dir_path . "/systemScanDir.txt";
      if (file_exists($systemScanDirPath)) {
         $fp = fopen($systemScanDirPath,"r");
         if ($fp) {
            while(!feof($fp)) {
               $buf = fgets($fp);
               echo $buf;
            }
            fclose($fp);
         }
      }
                              ?></Textarea>
                           </td>
                        </tr> 
                     </tbody>
                  </table>
               </div>
            <div class="title">盤點模式設定</div>
               <div class="content">
                  <table>
                     <tbody>
                        <tr>
                           <td>
                              <div>
                                 <input name="scanMode" type="radio" <?php if ($scanMode == 1) echo "checked";?>> 快速模式<br/>
                                    <ul style="margin-left:20px;">
                                       <li> - 每項個資都找到 <span id="extremeNum"><?php echo $riskExtreme; ?></span> 筆後即停止盤點該檔案。（此設定值等於「極高風險」的門檻值，可至風險等級設定修改）</li>
                                       <li> - 每個檔案最多盤點
                                          <select id="scanTime" name="scanTime">
                                             <option value="1" <?php if ($scanMode == 0 || $scanTime == 1) echo "selected"; ?>>1分鐘</option>
                                             <option value="2" <?php if ($scanMode == 1 && $scanTime == 2) echo "selected"; ?>>2分鐘</option>
                                             <option value="3" <?php if ($scanMode == 1 && $scanTime == 3) echo "selected"; ?>>3分鐘</option>
                                             <option value="4" <?php if ($scanMode == 1 && $scanTime == 4) echo "selected"; ?>>4分鐘</option>
                                             <option value="5" <?php if ($scanMode == 1 && $scanTime == 5) echo "selected"; ?>>5分鐘</option>
                                          </select>。</li>
                                    </ul>
                                 
                                 <input name="scanMode" type="radio" <?php if ($scanMode == 0) echo "checked";?>> 正常模式<br/>
                                    <ul style="margin-left:20px;">
                                       <li> - 每項個資都找到 500 筆以上即停止盤點該檔案。（本設定值無法修改）</li>
                                       <li> - 每個檔案最多盤點 5 分鐘。</li>
                                    </ul>
                              </div>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
                                       
            <div class="title">風險等級設定</div>
               <div class="content">
                  <table>
                     <tr>
                        <th class="highest">極高度 風險設定</th>
                        <td>
                           <div class="subject">個資類型包含 <span class="hint">(最少要選2種)</span> : </div>
                           <div class="typesW clearfix">
                              <label name="Name"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[5]) echo "checked" ?>> 姓名</label>
                              <label name="ID"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[0]) echo "checked" ?>> 身分證</label>
                              <label name="Phone"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[6]) echo "checked" ?>> 市話號碼</label>
                              <label name="Address"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[2]) echo "checked" ?>> 地址</label>
                              <label name="CreditCard"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[4]) echo "checked" ?>> 信用卡號碼</label>
                              <label name="Email"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[3]) echo "checked" ?>> 電子郵件地址</label>
                              <label name="Cell"><input name="ExtremeCheckbox" type="checkbox" <?php if($rItemW_default[1]) echo "checked" ?>> 手機號碼</label>
<?php       
   if ($type_number == 8)
   {
      echo "<label name=\"Account\"><input name=\"ExtremeCheckbox\" type=\"checkbox\" ";
      if($rItemW_default[7]) 
         echo "checked";
      echo "> $type8_name</label>";
   }
?>
                           </div>
                           <div class="conditions">
                              同時含其中
                              <select id="risktype" name="risktype">
                                 <option value="2" <?php if($extremeTypeNumber == 2) echo "selected" ?>>2</option>
                                 <option value="3" <?php if($extremeTypeNumber == 3) echo "selected" ?>>3</option>
                                 <option value="4" <?php if($extremeTypeNumber == 4) echo "selected" ?>>4</option>
                                 <option value="5" <?php if($extremeTypeNumber == 5) echo "selected" ?>>5</option>
                                 <option value="6" <?php if($extremeTypeNumber == 6) echo "selected" ?>>6</option>
                                 <option value="7" <?php if($extremeTypeNumber == 7) echo "selected" ?>>7</option>
<?php
   if ($type_number == 8)
   {
      echo "<option value=\"8\" ";
      if($extremeTypeNumber == 8) 
         echo "selected";
      echo ">8</option>";
   }
?>
                              </select>
                              類，
                           </div>
                           <div class="conditions">
                              且各類型各達 <input id="riskExtreme" type="text" class="sInput hMin" maxlength="4" size="4" value="<?php echo $riskExtreme ?>"> 筆以上。 <span class="hint">(最少1筆)</span>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <th class="high">高度 風險設定</th>
                        <td>包含個資筆數高於 <input id="riskHigh" class="sInput max" type="text" maxlength="4" size="4" value="<?php echo $riskHigh ?>"> 筆以上。 <span class="hint">(最少20筆)</span></td>
                     </tr>
                     <tr>
                        <th class="mid">中度 風險設定</th>
                        <td>包含個資筆數介於 <span id="mediumRangeBegin"><?php echo ($riskLow + 1) ?></span> ~ <span id="mediumRangeEnd"><?php echo ($riskHigh - 1) ?></span>筆之間。</td>
                     </tr>
                     <tr> 
                        <th class="low">低度 風險設定</th>
                        <td>包含個資筆數低於 <input id="riskLow" class="sInput min" type="text" maxlength="4" size="4" value="<?php echo $riskLow ?>"> 筆以下。 <span class="hint">(最少5筆)</span></td>
                     </tr>
                  </table>
               </div>
            </div>            
            <div class="submitW">
               <a class="btn_submit_new extreme_confirm"><input type="button" value="設定完成"></a>
      	    </div>  
         </div>

         <!-- 白名單 -->
<?php if (file_exists("OSC_whitelist.php")) include("OSC_whitelist.php"); ?>

         <!-- 分行管理 -->
<?php if (file_exists("OSC_customer_mgt.php")) include("OSC_customer_mgt.php"); ?>

         <!-- 系統管理者 -->
<?php if (file_exists("OSC_systemAdmin_mgt.php")) include("OSC_systemAdmin_mgt.php"); ?>
      </div>
   </div>
</div>
<!-- hidden data -->
<!-- used in PMarkFunction -->
<input type="hidden" id="GUID" value="<?php echo $GUID;?>">
<div style="width:1px; height:1px; background:transparent; left:-10000px; position:absolute; overflow:hidden;">
<div id="P-Marker_1"></div>
<div id="P-Marker_2"></div>
<div id="P-Marker_3"></div>
<div id="P-Marker_4"></div>
<div id="P-Marker_5"></div>
<div id="P-Marker_6"></div>
<div id="P-Marker_7"></div>
</div>
<!-- END_ORISBOT_NOINDEX -->
</body>
</html>
<?php
   //----- Release Connection -----
   if ($link)
   {
      mysqli_close($link);
      $link = 0;
   }
?>
