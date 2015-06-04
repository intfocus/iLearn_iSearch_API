<?php
/////////////////////////
//OSC_index.php
//
//1.main page after login
// #001 modified by Odie 2013/04/26
//  To support new feature: mutli-level admin
//     1. Add $_SESSION["loginLevel"]
//       admin => 1
//       user  => 2
//     2. If user, some functions will be hidden 
//
// #002 modified by Phantom+Odie, 2013/05/20
//  Add netDisk and removableDisk options
// 
// #003 modified by Odie, 2013/06/03
//  Add new feature: self-defined keyword (自訂關鍵字）
//
// #004 modified by Odie, 2013/06/10
//  Hide related forms for "自訂關鍵字" if the left most bit of conf is 0
//
// #005 modified by Odie, 2013/08/23
//  1. Insert data into "riskCategory" if no GUID found
//  2. Modified setting for scan mode
//
// #006 modified by Odie, 2013/09/06
//  1. Costomize for TDCC, add data type 8 (TDCC account)
//
// #007 modified by Odie, 2013/09/10
//  1. For easy maintaining, add two columns in "riskCategory" table (type8_enable, type8_name)
//     and read the setting to decide if the checkbox of type 8 will show or not
//
// #008 modified by Odie, 2013/12/02
//  1. customize for taisugar, add "change hostname, domain_name and employee_name"
//
// #009 modified by Odie, 2014/09/02
//  1. customize for EnTie bank, add "branch management"
//
// #010 modified by Phantom, 2014/09/03
//  1. customize for EnTie bank, add "system_scandir setting"
//
// #011 modified by Phantom, 2014/09/10
//  1. 白名單管理
//
// #012 modified by Phantom, 2014/09/15
//  1. For systemAdm
//  2. For 安泰銀行客製 (try to check file) 
//////////////////////////

   define(FILE_NAME, "/phptest/DB.conf");
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
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
   // #001, add checking $_SESSION["loginLevel"]  
   session_start();
   if (!session_is_registered("GUID") || !session_is_registered("GUID_ADM") || 
       !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "" || $_SESSION["loginLevel"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   $GUID_ADM = $_SESSION["GUID_ADM"];
   $login_level = $_SESSION["loginLevel"];
   $login_name = $_SESSION["loginName"];
   session_write_close();

   //////////////////////////////
   // #012 Check GUID_ADM and set systemAdm flag
   //////////////////////////////
   if ($GUID_ADM == "")
      $systemAdminFlag = 0;
   else if ($GUID_ADM == "00000000_0000_0000_0000_000000000000")
      $systemAdminFlag = 1;
   else
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }

   //////////////////////////////
   // #012 Check 安泰銀行 conf 檔案是否存在 
   //////////////////////////////
   define(ANTIE_FILE_NAME, "/usr/local/www/apache22/entie.conf");
   if (file_exists(ANTIE_FILE_NAME))
      $entieFlag = 1;
   else
      $entieFlag = 0;
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
<title>Openfind P-Marker</title>
<!-- BEG_ORISBOT_NOINDEX -->
<!-- Billy 2012/2/3 -->
<?php
   
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(PAGE_SIZE, 100);
   
   //define(EXTREME_TYPE_NUMBER, '8');  //個資類型  #007 comment out, let it be a variable rather than a constant

   define(AVAILABLE, 0);
   define(TRIAL, 0);
   define(DB_ERROR, -1);

   define(MSG_REPORT_1, "目前沒有任何報表，請點選&quot;<a>產生新的報表</a>&quot;");

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
   $validcode;              //用戶端密碼
   $contact_email;          //管理者信箱
   $expire_time;            //有效日期
   $temp_expire_time;
   $page_default_no;        //預設頁數
   $page_size;              //每頁報表數
   $page_num;
   $conf;                   //#004, 記錄conf 100個bytes的string
   $keyword_conf;           //#004, 是否啟用關鍵字掃瞄功能
   
   //預設風險個資類型
   $rItemW_default = array(0, 0, 0, 0, 0, 0, 0, 0);  //預設掃描項目 #006
   $rItemW_default_temp;
   $temp_begin;
   $extremeTypeNumber;
   $riskExtreme;
   $riskHigh;
   $riskLow;

   $type_number = 7;        //#007, 個資類型數目，預設七種
   $type8_name = "生日";    //#007, 第八種個資的名稱，預設"生日"

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
   //$rItemW_map = array("身分證", "手機號碼", "地址", "電子郵件地址", "信用卡號碼", "姓名", "市話號碼", "集保帳號");  //掃描項目對應  #007 comment out, no use
   $tRangeW_begin;          //產生區間-開始
   $temp_end;
   $tRangeW_end;            //產生區間-結束
   $cTotalW;                //電腦
   $i;
   $flag;
   $scanMode;
   $scanTime;

   date_default_timezone_set(TIME_ZONE);  //set timezone

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }

   //----- Get User Data -----    
   $str_query = "
      select *
      from customer
      where GUID = '" . $GUID . "'";    
 
   //----- 讀取使用者、公司、剩餘 -----
   if ($result = mysqli_query($link, $str_query))  //query success
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
      $netDisk = $row["netDisk"];  //#002 add
      $removableDisk = $row["removableDisk"];  //#002 add
      $conf = $row["conf"];   //#004 add
      $scanMode = $row["expressEnable"];
      $scanTime = $row["expressTimeout"];
      mysqli_free_result($result);
      unset($row);
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

   //#010 begin
   $systemScanDirEnabled = get_config_by_name($link, $GUID, "systemScanDirEnabled");
   if ($systemScanDirEnabled != 1)
      $systemScanDirEnabled = 0;
   //#010 end

   ////////////////
   // #004 check the content of conf
   ////////////////

   if($conf[1] == "1"){              // 是否顯示關鍵字掃瞄功能
      $keyword_conf = 1;
   }
   
   ////////////////
   // #004 end
   ////////////////

   ////////////////
   // #001
   // if login_level == 2, get "dept_list" from "userLogin"
   ////////////////

   $dept_list = "";
   $arr_dept_list = Array();
   if($login_level == 2){
      $sql = "
         select dept_list from userLogin where GUID = '$GUID' and login_name = '$login_name'
         ";
      if($result = mysqli_query($link, $sql)){
         $row_num = mysqli_num_rows($result);
         if($row_num != 1){
            if($link){
               mysqli_close($link);
            }
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }
         $row = mysqli_fetch_assoc($result);
         $dept_list = $row["dept_list"];
         // ex. $dept_list = "'marketing', 'advertising'";
         // user reg expression to parse it into array
         preg_match_all("/'(.*)'/U", $dept_list, $arr_dept_list, PREG_PATTERN_ORDER);
      }
      else{
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         echo -__LINE__;
         return;
      }
   }

   //20120405 Billy begin
   //----- Get Department name and Employee name-----
   // #001, if login_level == 2, add the restrict in SQL commands
   if($login_level == 1){
   $str_query = "
      select distinct department, employee_name
      from identityFound
      where GUID = '" . $GUID . "' and status = 0 order by department, employee_name";
   }
   else if($login_level == 2){
   $str_query = "
      select distinct department, employee_name
      from identityFound
      where GUID = '" . $GUID . "' and status = 0 and department in (". $dept_list.
      ") order by department, employee_name";
   }
                      
   if ($result = mysqli_query($link, $str_query))  //query success
   {
      $temp = "";
      $i = 0;
      $employee_number = mysqli_num_rows($result);
      while ($row = mysqli_fetch_assoc($result))
      {
         $department[] = $row["department"];
         $employee_name[] = $row["employee_name"];
         if ($i == 0)
         {
            $temp = $row["department"];
            $departmentMapping[] = $temp;
            $i++;
         }
         if (strcmp($temp, $row["department"]) != 0)
         {
            $temp = $row["department"];
            $departmentMapping[] = $temp;
            $i++;
         }
      }
      $department_num = $i;
      mysqli_free_result($result);
      unset($row);
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
  //20120405 Billy end

   $str_query = "
      select *
      from riskCategory
      where GUID = '" . $GUID . "'";
     
   //----- 讀取預設掃描項目 -----
   if ($result = mysqli_query($link, $str_query))
   {
      // #005 begin, if no data found in riskCategory then insert new data
      $row_number = mysqli_num_rows($result);
      if($row_number == 0)
      {
         $str_query = "
            insert into riskCategory 
            (GUID,low,high,extreme,extreme_type_num,extreme_type)
            values ('" . $GUID . "',5,20,20,2,'0,1,2,3,4,5,6')";      // #006

         mysqli_query($link, $str_query);
         $rItemW_default_temp = "0,1,2,3,4,5,6";                      // #006
         $extremeTypeNumber = 2;
         $riskExtreme = 20;
         $riskHigh = 20;
         $riskLow = 5;
         mysqli_free_result($result);
         unset($row);
      }
      // #005 end
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
      /*
      if ($flag == 0)  //使用系統預設值
      {
         $str_query = "
            select * 
            from riskCategory 
            where GUID = '" . DEFAULT_GUID . "'";
         if ($result = mysqli_query($link, $str_query))
         {
            $row = mysqli_fetch_assoc($result);
            $rItemW_default_temp = $row["extreme_type"];
            $extremeTypeNumber = $row["extreme_type_num"];
            $riskExtreme = $row["extreme"];
            $riskHigh = $row["high"];
            $riskLow = $row["low"];
            mysqli_free_result($result);
            unset($row);
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
      }
      */

      for ($i = 0; $i < strlen($rItemW_default_temp); $i++)
      {
         if ($rItemW_default_temp[$i] >= '0' && $rItemW_default_temp[$i] < $type_number)
            $rItemW_default[(int)$rItemW_default_temp[$i]] = 1;
      }
  
      //----- Release Connection -----
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
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
var checkBoxType = 8  // #006, even if we have only seven types here, g_defaultExtremeType[7] won't be referenced, so it's okay to leave it here
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

<?php

   //put employee name in arrays of department
   $i = 1;
   $j = 1;
   $count = 1;
   $department_temp = $department[0];
             
   //echo "var departmentArray = new Array();\n";
   echo "var employeeArray = new Array(" . ($department_num + 1) . ");\n";
   echo "employeeArray[0] = new Array();\n";
   if(count($departmentMapping) > 0){
	   foreach ($departmentMapping as $a)
	   {
		   //echo "departmentArray[$i] = \"$a\";\n";
		   echo "employeeArray[$i] = new Array();\n";
		   echo "employeeArray[$i][0] = \"請選擇\";\n";
		   $i++;
	   }
   }
   echo "\n";
   echo "employeeArray[0][0] = \"請選擇\";\n";
   for ($i = 0; $i < $employee_number; $i++)
   {
      if (strcmp($department_temp, $department[$i]) == 0)
      {
         echo "employeeArray[$j][$count] = \"$employee_name[$i]\";\n";
         $count++;
      }
      else
      {
         $count = 1;
         $j++;
         echo "\n";
         //echo "employeeArray[$j][0] = \"請選擇\";\n";
         echo "employeeArray[$j][$count] = \"$employee_name[$i]\";\n";
         $department_temp = $department[$i];
         $count++;
      }
      echo "employeeArray[0][" . ($i + 1) . "] = \"$employee_name[$i]\";\n";
   }
?>

function changeDepartment()
{   
   document.getElementById("searchEmployeeName").length = 0;
   var depart = document.getElementById("searchDepartName").selectedIndex;
   var employeeLength = employeeArray[depart].length;

   for(var i = 0; i < employeeLength; i++)
   {
       if (i == 0)
          var newOpt = new Option(employeeArray[depart][i], "", false, false); 
       else
          var newOpt = new Option(employeeArray[depart][i], employeeArray[depart][i], false, false);                
       document.getElementById("searchEmployeeName").options.add(newOpt);
   }         
}

function clickPage(obj, n)  //報表換頁
{
   if (obj.className == "page active")
      return;
   nPage = document.getElementsByName("page_no")[0].value;
   document.getElementsByName("page_no")[0].value = n;
   str = "page_begin_no_" + nPage;
   document.getElementById(str).className = "page";
   str = "page_end_no_" + nPage;
   document.getElementById(str).className = "page";
   str = "page_begin_no_" + n;
   document.getElementById(str).className = "page active";
   str = "page_end_no_" + n;
   document.getElementById(str).className = "page active";	
	
   //clear current table
   str = "page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "page" + n;
   document.getElementById(str).style.display = "block";
}

function clickDepartPage(obj, n)  //部門換頁
{
   if (obj.className == "depart_page active")
      return;
   nPage = document.getElementsByName("depart_page_no")[0].value;
   document.getElementsByName("depart_page_no")[0].value = n;
   str = "depart_page_begin_no_" + nPage;
   document.getElementById(str).className = "depart_page";
   str = "depart_page_end_no_" + nPage;
   document.getElementById(str).className = "depart_page";
   str = "depart_page_begin_no_" + n;
   document.getElementById(str).className = "depart_page active";
   str = "depart_page_end_no_" + n;
   document.getElementById(str).className = "depart_page active";	
	
   //clear current table
   str = "depart_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "depart_page" + n;
   document.getElementById(str).style.display = "block";
}

function clickSearchPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_page active")
      return;
   nPage = document.getElementsByName("search_page_no")[0].value;
   document.getElementsByName("search_page_no")[0].value = n;
   str = "search_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_page";
   str = "search_page_end_no_" + nPage;
   document.getElementById(str).className = "search_page";
   str = "search_page_begin_no_" + n;
   document.getElementById(str).className = "search_page active";
   str = "search_page_end_no_" + n;
   document.getElementById(str).className = "search_page active";	
	
   //clear current table
   str = "search_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_page" + n;
   document.getElementById(str).style.display = "block";
}

function clickScanPage(obj, n)  //搜尋換頁
{
   if (obj.className == "page active")
      return;
   nPage = document.getElementsByName("scan_page_no")[0].value;
   document.getElementsByName("scan_page_no")[0].value = n;
   str = "scan_page_begin_no_" + nPage;
   document.getElementById(str).className = "page";
   str = "scan_page_end_no_" + nPage;
   document.getElementById(str).className = "page";
   str = "scan_page_begin_no_" + n;
   document.getElementById(str).className = "page active";
   str = "scan_page_end_no_" + n;
   document.getElementById(str).className = "page active";	
	
   //clear current table
   str = "scan_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "scan_page" + n;
   document.getElementById(str).style.display = "block";
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

function uploadAsset()
{
   window.open("upload_computerListPage.php");
}

function uploadReplace()
{
   window.open("upload_replaceListPage.php");
}

// #003 add begin
function uploadKeyword()
{
   window.open("upload_keywordListPage.php");
}
// #003 add end

function deleteXML(xmlID,entryID)
{
   ret = window.confirm("確定要刪除此記錄並自資料庫刪除此項掃描結果？ (次數無法加回)");
   if (ret)
   {
      document.getElementsByName("deleteXMLID")[0].value = xmlID;
      document.getElementsByName("deleteEntryID")[0].value = entryID;
      document.getElementsByName("deleteXMLButton")[0].click();
   }
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
   <form name=logoutform action=logout.php>
   </form>
   <span class="global">使用者 : <?php echo $login_name ?>
      <font class="logout" OnClick="click_logout();">登出</font>&nbsp;
      <a href="onlinehelp/index.html" target=_blank><img style="vertical-align:middle" src="images/icon_onlinehelp.png" title="使用說明"/></a>
   </span>
   <span class="logo"></span>
   <span class="creditW">
      <span>剩餘 : <font color="red" class="credit"><?php echo $remain ?></font> 次</span>
      <a href=/index.html><span class="buy"><?php if($status == TRIAL) echo "升級正式版"; else echo "購買"; ?></span></a>
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
         <li class="active"><span class="tabIcon userMgmt"></span><span>用戶管理</span></li>
<?php 
   if($systemAdminFlag != 1) {
?>      
         <li><span class="tabIcon report"></span><span>報表管理</span></li>
         <li><span class="tabIcon search"></span><span>查詢</span></li>
<?php
   }
?>
         <li><span class="tabIcon system"></span><span>系統資訊</span></li>
<?php
   if($login_level == 1 && $systemAdminFlag != 1){
?>
         <li><span class="tabIcon setting"></span><span>設定</span></li>
<?php
   }

   if($systemAdminFlag != 1) {
?>      
         <!-- 20130411 Odie added, show the hitorical scan info -->
         <li><span class="tabIcon history"></span><span>紀錄</span></li>
<?php
      if ($login_level == 1) // 部門管理者不顯示
      {
?>
         <!-- 20130910 Phantom added, whitelist maintain #011 -->
         <li><span class="tabIcon whitelist"></span><span>白名單</span></li>
<?php
      }
   }
?>
      </ul>
      <div class="mainContent">

      <!-- 用戶管理 開始 20120611 新增 -->      
      <div class="container userC" style="display:block;">
        <div class="searchW">
         <form>
            <table class="searchField">
              <tr>
                <th>搜尋條件: </th>
              </tr>
              <tr>
                <td>
                  <label><input name=userMgmt_type value="completed" type="checkbox" checked>已完成</label>
                  <label><input name=userMgmt_type value="searching" type="checkbox" checked>清查中</label>
                  <label><input name=userMgmt_type value="expired" type="checkbox" checked>已逾時</label>
                  <label><input name=userMgmt_type value="notyet" type="checkbox" checked>未實施
                         <a href="javascript:alert('用戶端電腦清單內，尚未實施盤點的電腦。\n(需先匯入用戶端電腦清單！)')">
                             <img style="vertical-align:middle" src="/p-marker/images/icon_help.gif" border="0" alt="說明" title="說明" />
                         </a>
                  </label>
                </td>
              </tr>
              <tr>
                <th>搜尋範圍: 
                         <a href="javascript:alert('「匯入用戶端電腦清單」之後，\n可選擇觀看清單內或清單外的電腦盤點結果。')">
                             <img style="vertical-align:middle" src="/p-marker/images/icon_help.gif" border="0" alt="說明" title="說明" />
                         </a>
                </th>
              </tr>
              <tr>
                <td>
                  <label><input type="radio" name="userMgmt_targetCom" value="all" checked>全部電腦</label>
                  <label><input type="radio" name="userMgmt_targetCom" value="inside">用戶端清單內電腦</label>
                  <label><input type="radio" name="userMgmt_targetCom" value="outside">用戶端清單外電腦</label>
                </td>
              </tr>
              <tr>
                <th>關鍵字搜尋: </th>
              </tr>
              <tr>
                <td>
                  <input id="userSearch" name="userMgmt_keyword" class="searchBox empty" type="text" tabindex="4">
                </td>
              </tr>
              <tr>
                <th>搜尋時間區間: </th>
              </tr>
              <tr>
                <td>
                  <!--
                  <input id="userDate" name="userMgmt_date" type="text" name="from" class="from"/ size=10> ~ 至今日 
                  -->
                  <input type="text" id="from3" name="from" readonly="true" size="7"/> ~ <input type="text" id="to3" name="to" readonly="true" size="7"/>
                </td>
              </tr>
              <tr>
                <th class="uSubmitW"><a class="btn_submit_new userMgmt_confirm"><input name="searchUserMgmtButton" type="button" value="人員列表"></a><a class="btn_submit_new userDep_confirm"><input name="searchUserDepButton" type="button" value="部門統計"></a></th>
              </tr>
            </table>
            </form>
            <div class="uResultW" id="userMgmtPages">
<?php
   if($login_level == 1){
?>
               <div class="toolMenu">
                  <span class="btn new" OnClick="uploadAsset();">匯入用戶端電腦清單</span>
                  <!--
                  &nbsp;&nbsp;
                  <span class="btn new" OnClick="uploadReplace();">匯入替換電腦清單</span>
                  -->
               </div>
               <!-- #008 begin -->
               <div class="toolMenu">
               </div>
               <!-- #008 end -->
<?php
   }
?>
              <table class="report" border="0" cellspacing="0" cellpadding="0">
                <colgroup>
                  <col class="cIndex" />
                  <col class="cName" />
                  <col class="cMember" />
                  <col class="cDpmt" />
                  <col class="cIP" />
                  <col class="cLoginName" />
                  <col class="cStatus" />
                  <col class="uLvl" />
                  <col class="time" />
                  <col class="time" />
                  <col class="cAction" />
                </colgroup>
                <tr>
                  <th>序號</th>
                  <th>電腦名稱</th>
                  <th>人員名稱</th>
                  <th>部門</th>
                  <th>IP</th>
                  <th>登入帳號</th>
                  <th>狀態</th>
                  <th>含個資檔案數</th>
                  <th>開始時間</th>
                  <th>完成時間</th>
                  <th>紀錄</th>
                </tr>
                
                <tr>
                  <td colspan="11">請輸入左方查詢條件，並點選左下方按鈕 (最多顯示1,000筆資料)</td>
                </tr>
              </table>

            </div>
        </div>
      </div>

      <!-- 用戶管理 刪除資料 20120618 新增 -->
      <form name=deleteXMLform>
         <input type="hidden" name="deleteXMLID" value="">
         <input type="hidden" name="deleteEntryID" value="">
         <input type="button" style="display:none" name="deleteXMLButton" class="btn_submit_new deleteXMLClass">
      </form>

<?php 
   // #012 for system admin
   if($systemAdminFlag != 1) {
?>      
         <!-- 報表管理 開始 -->
         <div class="container reportC" style="display:none;">
            <!--<div class="navW">報表管理 &gt; <span>報表</span> &gt; 產生新的報表</div>-->
             
            <!-- Create New Report Start -->  
            <div id="newReport" class="newReport" style="display:none;">
               <form name="formNewReport">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                     <tr>
                        <th>報表名稱 : </th>
                        <td>
                           <!-- <form name="formNewReport"> -->
                           <input id="reportName" name="reportName" class="reportName" type="text" maxlength="255">            
                           <div id="reportNameHint" class="maxHint">*已超過最大字數限制</div>
                           <!-- </form> -->
                        </td>
                     </tr>
                     <div id="refreshPageExtreme">
                        <tr>
                           <th>掃描個資類型 : </th>
                           <td>
                              <label name="Name"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 5);"><font id="checkbox_5"> 姓名</font></label>
                              <label name="ID"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 0);"><font id="checkbox_0"> 身分證</font></label>
                              <label name="Phone"><input name="IdentityCheckbox" type="checkbox" <?php if($rItemW_default[6]){echo "checked";}?> onclick="lockFunction(this, 6);"><font id="checkbox_6"> 市話號碼</font></label>
                              <label name="Address"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 2);"><font id="checkbox_2"> 地址</font></label>
                              <label name="CreditCard"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 4);"><font id="checkbox_4"> 信用卡號碼</font></label>
                              <label name="Email"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 3);"><font id="checkbox_3"> 電子郵件地址</font></label>
                              <label name="Cell"><input name="IdentityCheckbox" type="checkbox" checked onclick="lockFunction(this, 1);"><font id="checkbox_1"> 手機號碼</font></label>
<!-- #007 -->
<?php
   if ($type_number == 8)
   {
      echo "<label name=\"Account\"><input name=\"IdentityCheckbox\" type=\"checkbox\" checked onclick=\"lockFunction(this, 7);\"><font id=\"checkbox_7\"> $type8_name </font></label>";
   }
?>   
<!-- #007 -->
                              <div class="msg">*紅字部分為極高風險設定欄位，為產出詳細風險分析報表，故設定為必選。</div>
                           </td>
                        </tr>
                     </div>
                     <tr>
                        <th>風險等級：</th>
                        <td>
                           <Input type=radio name="riskCategorySelect" value="2" checked>只產生極高風險、高風險檔案列表 </br>
                           <Input type=radio name="riskCategorySelect" value="3">產生極高風險、高風險、中風險檔案列表
                        </td>
                     </tr>
                     <tr>
                        <th>部門 :</th>
                        <td>
                           <select id="searchDepartName2">
                              <option selected value="">請選擇</option>
                              <?php
                                 if(count($departmentMapping) > 0){
                                    foreach ($departmentMapping as $optionDepartment)
                                    {
                                       echo "<option value=\"$optionDepartment\">$optionDepartment</option>";
                                    }
                                 }
                              ?> 
                           </select>
                        </td>
                     </tr>
                     <tr>
                        <th>電腦名稱(hostname) : </th>
                        <td>
                           <input type="text" id="reportHostname" name="reportHostname"/>
                        </td>
                     </tr>
                     <tr>
                        <th>時間區間 : </th>
                        <td>
                           <input id="from5" type="text" name="from" class="from" readonly="true" /> ~ <input id="to5" type="text" name="to" class="to" readonly="true" />
                        </td>
                     </tr>
                  </table>
               </form>   
               <div class="submitW">
                  <a class="btn_submit_new report_confirm"><input type="submit" value="確定"></a>
                  <a class="btn_submit_new report_cancel"><input type="button" value="取消"></a>
               </div>
            </div>      
            
            <!-- Report List Start -->
            <div id="reportW" class="reportW" style="display:block;">
      	       <div id="refreshPages">
                  <!-- refresh page begin-->
                  
                  <?php
                     /*
                        //----- Connect to MySql -----
                        $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
                        if (!$link)  //connect to server failure   
                        {   
                           sleep(DELAY_SEC);
                           echo DB_ERROR;                
                           return;
                        }
                        
                        //----- Get All Report Data -----
                        $str_query = "
                           select * 
                           from report
                           where GUID = '" . $GUID . "' and status =" . AVAILABLE .
                           " order by create_time desc";
                        
                        if ($result = mysqli_query($link, $str_query))  //query success
                        {  
                           $row_number = mysqli_num_rows($result);
                           while ($row = mysqli_fetch_assoc($result))  //將資料存成array
                           {
                              $rID[] = $row["reportID"];
                              $rNameW[] = $row["report_name"];
                              //$fileFolder[] = $row["fileFolder"];
                              //$fileName[] = $row["fileName"];
                              $temp_time = $row["create_time"];
                              $temp_time = strtotime($temp_time);
                              $rTimeW[] = date("Y-m-d", $temp_time);
                              $vHighW_file[] = $row["nExtremeFile"];
                              $HighW_file[] = $row["nHighFile"];
                              $MediumW_file[] = $row["nMediumFile"];
                              $LowW_file[] = $row["nLowFile"];
                              $vHighW_data[] = $row["nExtremeData"];
                              $HighW_data[] = $row["nHighData"];
                              $MediumW_data[] = $row["nMediumData"];
                              $LowW_data[] = $row["nLowData"];                  
                              $rItemW_temp1 = $row["identity_type"];  //掃描項目
                              $rItemW_temp1 = explode(",", $rItemW_temp1);
                              $rItemW_temp2 = "";
                              $flag = 0;
                              foreach ($rItemW_temp1 as $index => $value)
                              {
                                 if ($value >= '0' && $value < $type_number)
                                 {
                                    if ($index == 0)
                                       $rItemW_temp2 = $rItemW_temp2 . $rItemW_map[(int)$value];
                                    else
                                       $rItemW_temp2 = $rItemW_temp2 . "," . $rItemW_map[(int)$value];
                                 }
                              }
                              if ($flag != 0)
                                 $rItemW_temp2 = "data error";                  
                              $rItemW[] = $rItemW_temp2;
                              $temp_begin = $row["range_begin"];
                              $temp_begin = strtotime($temp_begin);
                              $tRangeW_begin[] = date("Y-m-d", $temp_begin);
                              $temp_end = $row["range_end"];
                              $temp_end = strtotime($temp_end);
                              $tRangeW_end[] = date("Y-m-d", $temp_end);
                              $cTotalW[] = $row["computer_numbers"];                 
                           }
                
                           //----- Release Connection And Result -----
                           mysqli_free_result($result);
                           unset($row);
                           if ($link)
                           {
                              mysqli_close($link);
                              $link = 0;
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
                           return;
                        }                     
                     
                        //----- Print Report Pages -----
                        $page_default_no = 1;
                        $page_size = PAGE_SIZE;
                                  
                        $page_num = (int)(($row_number - 1) / $page_size + 1);
                        echo "<div class=\"toolMenu\">";
                        echo "<span class=\"paging\">";
                        echo "<input type=\"hidden\" id=report_no value=$row_number>";
                        echo "<input type=\"hidden\" name=page_no value=1>";
                        echo "<input type=\"hidden\" name=page_size value=" . $page_size . ">";
                        if ($page_num > 1)
                        {
                           for ($i = 0; $i < $page_num; $i++)
                           {
                              echo "<span class=\"page";
                              if ($i + 1 == $page_default_no)
                                 echo " active";
                              echo "\" id=page_begin_no_" . ($i + 1) . " OnClick=clickPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
                           }
                        }      
                        echo "</span>";
                        echo "<span class=\"btn new\" OnClick=\"newReportFunc();\">產生新的報表</span>";
                        echo "<span class=\"btn expandR\" OnClick=\"expandContentFunc();\">顯示過長內文</span>";
                        echo "</div>";                   
                                                         
                        //----- Print Report Tables -----
                        if ($row_number == 0)
                        {
                           echo "<table id=\"report_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
                           echo "<colgroup>";
                           echo "<col class=\"rNameW\"/>";
                           echo "<col class=\"vHighW\"/>";
                           echo "<col class=\"highW\"/>";
                           echo "<col class=\"totalW\"/>";
                           echo "<col class=\"tRangeW\"/>";
                           echo "<col class=\"rItemW\"/>";
                           echo "<col class=\"cTotalW\"/>";
                           echo "<col class=\"rTimeW\"/>";
                           echo "<col class=\"actW\"/>";
                           echo "</colgroup>";
                           echo "<tr>";
                           echo "<th>報表名稱</th>";
                           echo "<th>極高風險</th>";
                           echo "<th>高風險</th>";
                           echo "<th>個資筆數 / 檔案總數</th>";
                           echo "<th>時間區間</th>";
                           echo "<th>掃描項目</th>";
                           echo "<th>電腦</th>";
                           echo "<th><span>產生日期</span></th>";
                           echo "<th>動作</th>";
                           echo "</tr>";
                           echo "<tr>";
                           echo "<td colspan=\"9\" class=\"empty\">" . MSG_REPORT_1 . "</td>";
                           echo "</tr>";
                           echo "</table>";
                        }
                        else
                        {
                           $i = 0;
                           $page_no = 1;
                           $page_count = 0;
                           while ($i < $row_number)
                           {
                              //----- If No Data -----
                              if ($page_count == 0)
                              {
                                 echo "<div id=\"page" . $page_no . "\" ";
                                 if ($page_no == 1)
                                    echo "style=\"display:block;\"";
                                 else
                                    echo "style=\"display:none;\"";
                                 echo ">";
                                 echo "<table id=\"report_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
                                 echo "<colgroup>";
                                 echo "<col class=\"rNameW\"/>";
                                 echo "<col class=\"vHighW\"/>";
                                 echo "<col class=\"highW\"/>";
                                 echo "<col class=\"totalW\"/>";
                                 echo "<col class=\"tRangeW\"/>";
                                 echo "<col class=\"rItemW\"/>";
                                 echo "<col class=\"cTotalW\"/>";
                                 echo "<col class=\"rTimeW\"/>";
                                 echo "<col class=\"actW\"/>";
                                 echo "</colgroup>";
                                 echo "<tr>";
                                 echo "<th>報表名稱</th>";
                                 echo "<th>極高風險</th>";
                                 echo "<th>高風險</th>";
                                 echo "<th>個資筆數 / 檔案總數</th>";
                                 echo "<th>時間區間</th>";
                                 echo "<th>掃描項目</th>";
                                 echo "<th>電腦</th>";
                                 echo "<th><span>產生日期</span></th>";
                                 echo "<th>動作</th>";
                                 echo "</tr>";              	  
                              }
                              if ($page_count < $page_size)
                              {
                                 echo "<tr>";
                                 echo "<td class=\"rNameW\"><span class=\"rName fixWidth\" OnClick=openReport($rID[$i]);><a>" . $rNameW[$i] . "</a></span></td>";
                                 echo "<td class=\"vHighW\">" . number_format($vHighW_file[$i]) . "</td>";
                                 echo "<td class=\"highW\">" . number_format($HighW_file[$i]) . "</td>";
                                 echo "<td class=\"totalW\">" . number_format($vHighW_data[$i] + $HighW_data[$i] + $MediumW_data[$i] + $LowW_data[$i]) . " / " . number_format($vHighW_file[$i] + $HighW_file[$i] + $MediumW_file[$i] + $LowW_file[$i]) . "</td>";
                                 echo "<td class=\"tRangeW\">" . $tRangeW_begin[$i] . " ~ " . $tRangeW_end[$i] . "</td>";
                                 echo "<td class=\"rItemW\"><span class=\"rItem fixWidth\">" . $rItemW[$i] . "</span></td>";
                                 echo "<td class=\"cTotalW\">" . number_format($cTotalW[$i]) . "</td>";
                                 echo "<td class=\"rTimeW\">" . $rTimeW[$i] . "</td>";
                                 echo "<td class=\"actW\"><a id=\"" . $rID[$i] . "_reportID\" class=\"del\" OnClick=\"deleteReport(this);\">刪除</a></td>";
                                 echo "</tr>";                    
                                 $i++;
                                 $page_count++;
                                 if ($page_count == $page_size)
                                 {
                                    echo "</table>";
                                    echo "</div>\n";
                                    $page_no++;
                                    $page_count = 0;
                                 }                    
                              }
                           }
                           if ($page_count > 0)
                           {
                              echo "</table>";
                              echo "</div>\n";
                           }               
                        }
                        echo "<div class=\"toolMenu\">";
                        echo "<span class=\"paging\">";
                           
                        //----- Print Report Pages -----
                        if ($page_num > 1)
                        {
                           for ($i = 0; $i < $page_num; $i++)
                           {
                              echo "<span class=\"page";
                              if ($i + 1 == $page_default_no)
                                 echo " active";
                              echo "\" id=page_end_no_" . ($i + 1) . " OnClick=clickPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
                           }
                        }
                        echo "</span>";
                        echo "<span class=\"btn new\" OnClick=\"newReportFunc();\">產生新的報表</span>";
                        echo "</div>";
                        */
                  ?>
                  <?php
                  
                     define(REFRESH_REPORT_PATH, "$working_path/refreshReportPages.php");
                     //echo REFRESH_REPORT_PATH;
                     if(file_exists(REFRESH_REPORT_PATH))
                     {
                        include_once(REFRESH_REPORT_PATH);
                     }
                     else
                     {
                        sleep(DELAY_SEC);
                        echo FILE_ERROR;
                        return;
                     }
                     $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
                     if (!$link)  //connect to server failure   
                     {   
                        sleep(DELAY_SEC);
                        echo DB_ERROR;                
                        return;
                     }
                     $refresh_report_str = refreshReportPages($link, $GUID, $login_level, $login_name);
                     if ($link)
                     {
                        mysqli_close($link);
                        $link = 0;
                     }
                     echo $refresh_report_str;
                  
                  ?>
                  <!-- refresh page end-->
               </div>
            </div>
         </div>
		 
         <!--快速查詢 從這裡開始-->
         <div class="container searchC" style="display:none;">
            <div class="searchW">
               <form>
                  <table class="searchField" border="0" cellspacing="0" cellpadding="0">
                     <tr>
                        <th>部門 ：</th>
                        <td>
                           <select id="searchDepartName" onchange="changeDepartment()">
                              <option selected value="">請選擇</option>
                              <?php
                                 if(count($departmentMapping) > 0){
                                    foreach ($departmentMapping as $optionDepartment)
                                    {
                                       echo "<option value=\"$optionDepartment\">$optionDepartment</option>";
                                    }
                                 }
                              ?>   
                           </select>
                        </td>
                        <th>電腦名稱 ：</th>
                        <td><input id="searchComputerName" type="text" maxlength="50"></td>
                     </tr>
                     <tr>
                        <th>人員名稱 ：</th>
                        <td>
                           <select id="searchEmployeeName">
                              <option selected value="">請選擇</option>
                              <?php
			      if(count($employee_name) > 0){
				      foreach ($employee_name as $optionEmployee)
				      {
					      echo "<option value=\"$optionEmployee\">$optionEmployee</option>";
				      }
			      }
                              ?>  
                           </select>
                        </td>
                        <!-- yaoan 20120514 modify -->
                        <!--<th>檔案名稱 ：</th>
                        <td><input id="searchFileName" type="text" maxlength="50"></td>-->
                        <input type='hidden' id='searchFileName' value=''>
                        <!-- yaoan end modify -->
                     
                     
                        <th>檔案風險等級 ：</th>
                        <td colspan="3">
                           <label><input id="searchCheckBox1" type="checkbox" checked> 極高風險</label>
                           <label><input id="searchCheckBox2" type="checkbox" checked> 高度風險</label>
                           <label><input id="searchCheckBox3" type="checkbox"> 中度風險</label>
                           <!-- Add the choice of listing encrypted files -->
                           <label><input id="encryptCheckBox" type="checkbox"> 未知（盤點失敗）</label>
                           <!-- <label><input id="searchCheckBox4" type="checkbox"> 低度風險</label> -->
                        </td>
                     </tr>
                     <tr>
                        <th>檔案最後修改時間 ：</th>
                        <td colspan="3">
                           <input id="from1" type="text" name="from" class="from" readonly="true"/> ~ <input id="to1" type="text" class="to" name="to" readonly="true"/>
                        </td>
                     </tr>
					 <tr>
                        <th>資料區間 ：</th>
                        <td colspan="3">
                           <input id="from2" type="text" name="from" class="from" readonly="true"/> ~ <input id="to2" type="text" class="to" name="to" readonly="true"/>
                        </td>
                     </tr>
                     <tr>
                        <th colspan="4" class="submitBtns">
<?php
   if ($status == TRIAL)
   {
?>
                           <input type="button" value="開始查詢(試用版無法查詢)" OnClick="alert('試用版不支援「查詢」功能，請升級正式版，謝謝！');">
<?php   
   }
   else
   {
?>
                           <a class="btn_submit_new search"><input type="button" value="開始查詢"></a>
<?php
   }
?>
                        </th>
                     </tr>
                  </table>
               </form>
          
               <!-- search pages-->
               <div id="sResultW" class="reportW" style="display:block;">
                  <div id="searchPages">
                     <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
                     <div class="toolMenu">
                        <span class="btn expandSR" OnClick="expandSearchContentFunc();">顯示過長內文</span>
                     </div>
                     <table class="report" border="0" cellspacing="0" cellpadding="0">
                        <colgroup>
                           <col class="num" />
                           <col class="comName" />
                           <col class="name" />
                           <col class="department" />
                           <col class="level" />
                           <col class="lastUpdate" />
                           <col class="fileType" />
                           <col class="pAmount" />
                           <col class="pType" />
                           <col class="path" />
                           <col class="action" />
                        </colgroup>
                        <tr>
                           <th>編號</th>
                           <th>網域名稱/電腦名稱</th>
                           <th>人員名稱</th>
                           <th>部門</th>
                           <th>風險等級</th>
                           <th>最後修改</th>
                           <th>類型</th>
                           <th>個資數量</th>
                           <th>
                              <!--<span class="fixP">-->個資種類
                              <!--
                                 <a class="typeDesBtn" OnMouseOver="showTypeDis();" OnMouseOut="hideTypeDis();">[?]</a>
                                 <span class="typeDes" style="display:none;">
                                    <ul>
                                       <div>個資種類說明 : </div>
                                       <li>N=姓名</li>
                                       <li>T=市話號碼</li>
                                       <li>M=手機號碼</li>
                                       <li>A=地址</li>
                                       <li>E=電子郵件地址</li>
                                       <li>I=身分證號碼</li>
                                       <li>C=信用卡</li>
                                    </ul>
                                 </span>
                              </span>
                              -->
                           </th>
                           <th>檔案路徑</th>
                           <th>動作</th>
                        </tr>
                        <tr>
                           <td colspan="11" class="empty">請輸入上方查詢條件，並點選"開始查詢"</td>
                        </tr>
                     </table>
                  </div>
               </div>
               <!-- search pages-->
            </div>      
         </div>
         <!--快速查詢 結束-->
<?php
   // #012 for system admin
   } 
?>      
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
<?php
   if($login_level == 1){
?>                        
               <tr>
                  <th>用戶端密碼</th>
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
<?php
   }
?>
               <tr>
<?php
   if($login_level == 1){
?>                        
                  <th>管理者密碼</th>

<?php
   }
   else if($login_level == 2){
?>                        
                  <th>部門管理者密碼</th>
<?php
   }
?>                        
                  <td>
                     <span id="curAdminPW" class="curPW">●●●●●●●●●● <a href="#" id="changeAdminPWBtn" >變更密碼</a></span>
                     <form name="formAdminPW">
                        <ul id="changeAdminPW" style="display:none;">
<?php
   //#012 for system admin
   if ($systemAdminFlag != 1) {
?>
                           <li><span class="title">舊密碼 :</span> <input id="oldAdminPW" type="password"></li>
<?php
   //#012 for system admin
   }
   else {
      echo "<input id='oldAdminPW' value='12345678' type='hidden'>";
   }
?>
                           <li><span class="title">新密碼 :</span> <input id="newAdminPW" type="password"></li>
                           <li><span class="title">確認新密碼 :</span> <input id="newAdminPWConfirm" type="password"></li>
                           <input id="loginLevel" type="hidden" value="<?php echo $login_level; ?>">
                           <li><button id="submitChangeAdminPW" type="button">確認</button><button id="cancelChangeAdminPW" type="button">取消</button></li>
                        </ul>
                     </form>
                  </td>
               </tr>
<?php
   if($login_level == 1){
?>                        
               <tr>
                  <th>管理者信箱</th>
                  <td><Input type=text size=50 maxlength=50 name=contact_email value="<?php echo $contact_email ?>"><button id="changeContactEmail" type="button">修改</button></td>
               </tr>
<?php
   }
?>
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
<?php
   //#012 for system admin
   if($login_level == 1 && $systemAdminFlag != 1){
?>                        
         <!-- 設定 開始 -->
         <div class="container settingC" style="display:none;">
            <div class="settingW">
<?php
   if ($entieFlag == 1){
      echo "<table class=sysInfo border=0 cellspacing=0 cellpadding=0>";
      echo "<tr><th>(部分設定只有總行系統管理者可以修改，分行管理者只能查看設定)</th></tr>";
      echo "<tr><th>(分行管理者可以修改 1.指定盤點路徑 2.部門設定 3.部門管理者設定)</th></tr>";
      echo "</table>";
   }
?>
<?php
      if($keyword_conf == 1){
?>
            <!-- #003 add begin-->
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
            <!-- #003 add end -->
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
                                 <label><input name="uploadMask" value="0" type="radio" 
                                       <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                             if ($uploadMask == 0) echo "checked";?>>
                                       僅記錄檔案路徑及個資筆數</label><br />
                                 <label><input name="uploadMask" value="1" type="radio" 
                                       <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                             if ($uploadMask == 1) echo "checked";?>> 
                                       記錄檔案路徑、個資筆數與資料片段</label><br />
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
                                 <label><input name="netDisk" type="checkbox" 
                                       <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                             if ($netDisk == 1) echo "checked";?>> 
                                       搜尋網路磁碟機</label><br/>
                                 <label><input name="removableDisk" type="checkbox" 
                                       <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                             if($removableDisk == 1) echo "checked";?>> 
                                       搜尋卸除式存放裝置(例如 USB、外接硬碟)</label>
                              </div>
                           </td>
                        </tr>
                        <!-- #010 begin -->
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
                        <!-- #010 end -->
                     </tbody>
                  </table>
               </div>
            <!-- #005 begin -->
            <div class="title">盤點模式設定</div>
               <div class="content">
                  <table>
                     <tbody>
                        <tr>
                           <td>
                              <div>
                                 <input name="scanMode" type="radio" 
                                    <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                          if ($scanMode == 1) echo "checked";?>> 
                                    快速模式<br/>
                                    <ul style="margin-left:20px;">
                                       <li> - 每項個資都找到 <span id="extremeNum"><?php echo $riskExtreme; ?></span> 筆後即停止盤點該檔案。（此設定值等於「極高風險」的門檻值，可至風險等級設定修改）</li>
                                       <li> - 每個檔案最多盤點
                                          <select id="scanTime" name="scanTime" 
                                             <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰 ?>
                                          >
                                             <option value="1" <?php if ($scanMode == 0 || $scanTime == 1) echo "selected"; ?>>1分鐘</option>
                                             <option value="2" <?php if ($scanMode == 1 && $scanTime == 2) echo "selected"; ?>>2分鐘</option>
                                             <option value="3" <?php if ($scanMode == 1 && $scanTime == 3) echo "selected"; ?>>3分鐘</option>
                                             <option value="4" <?php if ($scanMode == 1 && $scanTime == 4) echo "selected"; ?>>4分鐘</option>
                                             <option value="5" <?php if ($scanMode == 1 && $scanTime == 5) echo "selected"; ?>>5分鐘</option>
                                          </select>。</li>
                                    </ul>
                                 
                                 <input name="scanMode" type="radio" 
                                    <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                          if ($scanMode == 0) echo "checked";?>> 
                                          正常模式<br/>
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
            <!-- #005 begin -->
                                       
            <div class="title">風險等級設定</div>
               <div class="content">
                  <table>
                     <tr>
                        <th class="highest">極高度 風險設定</th>
                        <td>
                           <div class="subject">個資類型包含 <span class="hint">(最少要選2種)</span> : </div>
                           <div class="typesW clearfix">
                              <label name="Name"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[5]) echo "checked" ?>> 姓名</label>
                              <label name="ID"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[0]) echo "checked" ?>> 身分證</label>
                              <label name="Phone"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[6]) echo "checked" ?>> 市話號碼</label>
                              <label name="Address"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[2]) echo "checked" ?>> 地址</label>
                              <label name="CreditCard"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[4]) echo "checked" ?>> 信用卡號碼</label>
                              <label name="Email"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[3]) echo "checked" ?>> 電子郵件地址</label>
                              <label name="Cell"><input name="ExtremeCheckbox" type="checkbox" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰
                                       if($rItemW_default[1]) echo "checked" ?>> 手機號碼</label>
<!-- #007 -->
<?php       
   if ($type_number == 8)
   {
      echo "<label name=\"Account\"><input name=\"ExtremeCheckbox\" type=\"checkbox\" ";
      if($entieFlag == 1) //#012 安泰
         echo "disabled ";
      if($rItemW_default[7]) 
         echo "checked";
      echo "> $type8_name</label>";
   }
?>
<!-- #007 -->
                           </div>
                           <div class="conditions">
                              同時含其中
                              <select id="risktype" name="risktype"
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰 ?>
                              >
                                 <option value="2" <?php if($extremeTypeNumber == 2) echo "selected" ?>>2</option>
                                 <option value="3" <?php if($extremeTypeNumber == 3) echo "selected" ?>>3</option>
                                 <option value="4" <?php if($extremeTypeNumber == 4) echo "selected" ?>>4</option>
                                 <option value="5" <?php if($extremeTypeNumber == 5) echo "selected" ?>>5</option>
                                 <option value="6" <?php if($extremeTypeNumber == 6) echo "selected" ?>>6</option>
                                 <option value="7" <?php if($extremeTypeNumber == 7) echo "selected" ?>>7</option>
<!-- #007 -->
<?php
   if ($type_number == 8)
   {
      echo "<option value=\"8\" ";
      if($extremeTypeNumber == 8) 
         echo "selected";
      echo ">8</option>";
   }
?>
<!-- #007 -->
                              </select>
                              類，
                           </div>
                           <div class="conditions">
                              且各類型各達 <input id="riskExtreme" type="text" class="sInput hMin" maxlength="4" size="4" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰 ?>
                                 value="<?php echo $riskExtreme ?>"> 筆以上。 <span class="hint">(最少1筆)</span>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <th class="high">高度 風險設定</th>
                        <td>包含個資筆數高於 <input id="riskHigh" class="sInput max" type="text" maxlength="4" size="4" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰 ?>
                                 value="<?php echo $riskHigh ?>"> 筆以上。 <span class="hint">(最少20筆)</span></td>
                     </tr>
                     <tr>
                        <th class="mid">中度 風險設定</th>
                        <td>包含個資筆數介於 <span id="mediumRangeBegin"><?php echo ($riskLow + 1) ?></span> ~ <span id="mediumRangeEnd"><?php echo ($riskHigh - 1) ?></span>筆之間。</td>
                     </tr>
                     <tr> 
                        <th class="low">低度 風險設定</th>
                        <td>包含個資筆數低於 <input id="riskLow" class="sInput min" type="text" maxlength="4" size="4" 
                                 <?php if ($entieFlag == 1) echo "disabled "; //#012 安泰 ?>
                                 value="<?php echo $riskLow ?>"> 筆以下。 <span class="hint">(最少5筆)</span></td>
                     </tr>
                  </table>
               </div>
            </div>            
            <div class="settingW">
               <div class="title">部門設定</div>
               <div class="content">            
                  <div id="newDepart" class="newReport" style="display:none;">
                     <form name="formNewDepart">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                           <tr>
                              <th>部門名稱 : </th>
                              <td>
                                 <input id="newDepartName" class="reportName" type="text" maxlength="10">
                                 <div id="newDepartNameHint" class="maxHint">*已超過最大字數限制</div>
                              </td>
                           </tr>                  
                        </table>
                     </form>
                        <div class="submitW">
                           <a class="btn_submit_new new_depart_confirm"><input type="button" value="確定"></a>
                           <a class="btn_submit_new new_depart_cancel"><input type="button" value="取消"></a>
                        </div>
                  </div>
                  <div id="editDepart" class="newReport" style="display:none;">
                     <form name="formEditDepart">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                           <tr>
                              <th>部門名稱 : </th>
                              <td>
                                 <input id="editDepartName" class="reportName" type="text" value="" maxlength="10">
                                 <div id="editDepartNameHint" class="maxHint">*已超過最大字數限制</div>
                              </td>
                           </tr>                  
                        </table>
                        <div class="submitW">
                           <a class="btn_submit_new edit_depart_confirm"><input type="button" value="確定"></a>
                           <a class="btn_submit_new edit_depart_cancel"><input type="button" value="取消"></a>
                        </div>
                     </form>
                  </div>
                  <div id="departW" class="reportW" style="display:block;">
                     <div id="refreshDepartPages">
<!-- refresh department pages begin-->
<?php
      define(REFRESH_PATH, "$working_path/refreshDepartPage.php");

      if(file_exists(REFRESH_PATH))
      {
         include_once(REFRESH_PATH);
      }
      else
      {
         sleep(DELAY_SEC);
         echo FILE_ERROR;
         return;
      }
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link)  //connect to server failure   
      {   
         sleep(DELAY_SEC);
         echo DB_ERROR;                
         return;
      }
      $refresh_str = refreshDepartPage($link, $GUID);
      echo $refresh_str;
?>
<!-- refresh department pages end-->
                     </div>
                  </div>
               </div>      
            </div>
<!-- user pages, 20130503, By Phantom+Odie -->
            <div class="settingW">
               <div class="title">部門管理者設定</div>
               <div class="content">
                  <div id="departCheckbox">
<!-- refresh department checkbox begin-->
<?php      
      define(REFRESH_CHECKBOX_PATH, "$working_path/refreshDepartCheckbox.php");

      if(file_exists(REFRESH_CHECKBOX_PATH))
      {
         include_once(REFRESH_CHECKBOX_PATH);
      }
      else
      {
         sleep(DELAY_SEC);
         echo FILE_ERROR;
         return;
      }
      $refresh_str = refreshDepartCheckbox($link, $GUID);
      echo $refresh_str;       
?>
                  </div>
                  <div id="userW" class="reportW" style="display:block;">
                     <div id="refreshUserPages">
<!-- refresh user pages begin-->
<?php
      define(REFRESH_USER_PATH, "$working_path/refreshUserPage.php");

      if(file_exists(REFRESH_USER_PATH))
      {
         include_once(REFRESH_USER_PATH);
      }
      else
      {
         sleep(DELAY_SEC);
         echo FILE_ERROR;
         return;
      }
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link)  //connect to server failure   
      {   
         sleep(DELAY_SEC);
         echo DB_ERROR;                
         return;
      }
      $refresh_str = refreshUserPage($link, $GUID);
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo $refresh_str;
?>
<!-- refresh user pages end-->
                     </div>
                  </div>
               </div>      
            </div>
            <div class="submitW">
               <a class="btn_submit_new extreme_confirm"><input type="button" value="設定完成"></a>
      	    </div>  
         </div>
<?php
   }
?>
<?php
   //#012 for system admin
   if ($systemAdminFlag != 1) {
?>
         <!-- 20130411 added by Odie, 紀錄開始 -->
         <div class="container settingC" style="display:none;">
            <div class="settingW">
               <form>
                  <table class="searchField">
                     <tr>
                        <th>關鍵字搜尋: <input id="scanSearch" name="scan_history_keyword" class="searchBox empty" type="text" tabindex="4"> </th>
                        <th>搜尋時間區間: 
                           <input type="text" id="from4" name="from" readonly="true" size="7"/> ~ <input type="text" id="to4" name="to" readonly="true" size="7"/>
                        </th>
                        <th class="uSubmitW"><a class="btn_submit_new scan_history"><input name="scanHistoryButton" type="button" value="開始查詢"></a></th>
                     </tr>
                     <tr>
                        <th></th>
                     </tr>
                  </table>
               </form>
            <div class="uResultW" id="scanHistoryPages">
               <table class="report" border="0" cellspacing="0" cellpadding="0">
                  <colgroup>
                     <col class="cIndex" />
                     <col class="cName" />
                     <col class="cMember" />
                     <col class="cDpmt" />
                     <col class="time" />
                     <col class="time" />
                  </colgroup>
                  <tr>
                     <th>序號</th>
                     <th>電腦名稱</th>
                     <th>人員名稱</th>
                     <th>部門</th>
                     <th>開始時間</th>
                     <th>完成時間</th>
                  </tr>
                  <tr>
                    <td colspan="6">請輸入上方查詢條件，並點選右上方按鈕</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
      <!-- 紀錄結束 -->

      <!-- 白名單 -->
<?php
      if ($login_level != 2) //部門管理者不顯示
      {
         if (file_exists("OSC_whitelist.php")) include("OSC_whitelist.php"); //#011
      }

   //#012 for system admin
   }
?>
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
