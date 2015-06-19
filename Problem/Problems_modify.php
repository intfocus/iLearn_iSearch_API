<?php
   require_once('Problems_utility.php');
 
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
      if(strcmp($check_str, "read") && strcmp($check_str, "update"))
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
   $ProbId;

   //query
   $link;
   
   //1.get information from client 
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($ProbId = check_number($_GET["ProbId"])) == SYMBOL_ERROR)
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

   $TitleStr = MSG_PROBLEM_MODIFY;
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if (strcmp($cmd, "read") == 0) // Load
   {  
      $str_query1 = "Select * from Problems where ProblemId=$ProbId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $ProbId = $row["ProblemId"];
            $ProbType = $row["ProblemType"];
            $ProbDesc = $row["ProblemDesc"];
            $ProbSelA = $row["ProblemSelectA"];
            $ProbSelB = $row["ProblemSelectB"];
            $ProbSelC = $row["ProblemSelectC"];
            $ProbSelD = $row["ProblemSelectD"];
            $ProbSelE = $row["ProblemSelectE"];
            $ProbSelF = $row["ProblemSelectF"];
            $ProbSelG = $row["ProblemSelectG"];
            $ProbSelH = $row["ProblemSelectH"];
            $ProbSelI = $row["ProblemSelectI"];
            $ProbAnswer = $row["ProblemAnswer"];
            $ProbCategory = $row["ProblemCategory"];
            $ProbLevel = $row["ProblemLevel"];
            $ProbMemo = $row["ProblemMemo"];
            $ProbStatus = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";

            if ($ProbStatus == 1)
               $TitleStr = "题目查看 (上架状态无法修改)";
         }
         else
         {
            $DeptId = 0;
            $DeptName = "";
            $DeptCode = 0;
            $ParentId = 0;
            $PAList = "";
            $ProductList = "";
            $TitleStr = "部门新增";
            $Status = 0;
         }
      }
   }
   else if ($ProbId == 0) // Insert
   {
      /* read excel and parse question */
      /* check syntax, mark any wrong place */
      /* if all okay, insert it
      
      $DeptName = $_GET["DeptName"];
      $DeptCode = $_GET["DeptCode"];
      $PAList = $_GET["PAList"] == "" ? "All":$_GET["PAList"];
      $ProductList = $_GET["ProductList"] == ""?"All":$_GET["ProductList"];
      $ParentId = $_GET["ParentId"];
      $str_query1 = "Insert into Depts (DeptName,DeptCode,ParentId,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime,Status)" 
                  . " VALUES('$DeptName','$DeptCode',$ParentId,'$PAList','$ProductList',1,now(),1,now(),1)" ;
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
      */
   }
   else if ($cmd == "update")// Update
   {
      $ProbId = $_GET["ProbId"];
      $ProbDesc = $_GET["ProbDesc"];
      $ProbSelA = $_GET["ProbSelA"];
      $ProbSelB = $_GET["ProbSelB"];
      $ProbSelC = $_GET["ProbSelC"];
      $ProbSelD = $_GET["ProbSelD"];
      $ProbSelE = $_GET["ProbSelE"];
      $ProbSelF = $_GET["ProbSelF"];
      $ProbSelG = $_GET["ProbSelG"];
      $ProbSelH = $_GET["ProbSelH"];
      $ProbSelI = $_GET["ProbSelI"];
      $ProbAnswer = $_GET["ProbAnswer"];
      $ProbCategory = $_GET["ProbCategory"];
      $ProbLevel = $_GET["ProbLevel"];
      $ProbMemo = $_GET["ProbMemo"];

      // ID check
      $str_query = "Select * from problems where ProblemId='$ProbId'";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number == 0)
         {
            echo ERR_PROB_NOT_EXIST;
            return;
         }
      }
      // get problem type
      $row = mysqli_fetch_assoc($result);
      $ProbStatus = $row['Status'];
      $ProbType = $row['ProblemType'];

      if (!is_correct_prob_desc_format($ProbDesc))
      {
         echo ERR_PROB_DESC_FORMAT;
         return;
      }

      $selections = [$ProbSelA, $ProbSelB, $ProbSelC, $ProbSelD, $ProbSelE, $ProbSelF, $ProbSelG, $ProbSelH, $ProbSelI];
      if (!is_correct_prob_selection_format($selections, $ProbType))
      {
         echo ERR_PROB_SELECTOR_FORMAT;
         return;
      }   

      // Answer check, the number of answer depend on the type and selector
      if (!($ret=is_correct_prob_answer_format($ProbAnswer, $selections, $ProbType)))
      {
         echo $ret;
         echo ERR_PROB_ANSWER_FORMAT;
         return;
      }
      
      // Category check
      if (!is_correct_prob_category_format($ProbCategory))
      {
         echo ERR_PROB_CATEGORY_FORMAT;
         return;
      }

      // Level check
      if (!is_correct_prob_level_format($ProbLevel))
      {
         echo ERR_PROB_LEVEL_FORMAT;
         return;
      }

      $str_query1 = <<<EOD
                      Update Problems set ProblemDesc='$ProbDesc', ProblemSelectA='$ProbSelA',
                      ProblemSelectB='$ProbSelB', ProblemSelectC='$ProbSelC',
                      ProblemSelectD='$ProbSelD', ProblemSelectE='$ProbSelE',
                      ProblemSelectF='$ProbSelF', ProblemSelectG='$ProbSelG',
                      ProblemSelectH='$ProbSelH', ProblemSelectI='$ProbSelI',
                      ProblemAnswer='$ProbAnswer', ProblemCategory='$ProbCategory',
                      ProblemLevel='$ProbLevel', ProblemMemo='$ProbMemo' where ProblemId=$ProbId
EOD;


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
<title>武田 - 题目页面</title>
<!-- BEG_ORISBOT_NOINDEX -->
<!-- Billy 2012/2/3 -->
<script type="text/javascript" src="../js/utility.js"></script>
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

function make_selection_modify_query_str()
{
   selA = document.getElementById("ProbSelA").value;
   selB = document.getElementById("ProbSelB").value;
   selC = document.getElementById("ProbSelC").value;
   selD = document.getElementById("ProbSelD").value;
   selE = document.getElementById("ProbSelE").value;
   selF = document.getElementById("ProbSelF").value;
   selG = document.getElementById("ProbSelG").value;
   selH = document.getElementById("ProbSelH").value;
   selI = document.getElementById("ProbSelI").value;
   
   return ("ProbSelA=" + encodeURIComponent(selA) + "&ProbSelB=" + encodeURIComponent(selB) + 
           "&ProbSelC=" + encodeURIComponent(selC) + "&ProbSelD=" + encodeURIComponent(selD) + 
           "&ProbSelE=" + encodeURIComponent(selE) + "&ProbSelF=" + encodeURIComponent(selF) +
           "&ProbSelG=" + encodeURIComponent(selG) + "&ProbSelH=" + encodeURIComponent(selH) +
           "&ProbSelI=" + encodeURIComponent(selI));
}

//***Step12 修改页面点击保存按钮出发Ajax动作
function modifyProbsContent(ProbId)
{
   ProbDesc = document.getElementsByName("ProbDescModify")[0].value.trim();
   ProbAnswer = document.getElementsByName("ProbAnswerModify")[0].value.trim();
   ProbLevel = document.getElementsByName("ProbLevelModify")[0].value;
   ProbMemo = document.getElementsByName("ProbMemoModify")[0].value.trim();
   ProbFuncs = get_checkbox_checked_values("ProbCategoryModify");
   
   if (ProbDesc.length == 0 || ProbAnswer.length == 0)
   {
      alert("题目描述及题目答案不可为空白");
      return;
   }
   
   ProbFuncStr = output_category_str_from_func_array(ProbFuncs);
   str = "cmd=update&ProbId=" + ProbId + "&ProbDesc=" + encodeURIComponent(ProbDesc) + 
         "&ProbAnswer=" + encodeURIComponent(ProbAnswer) + "&ProbCategory=" + encodeURIComponent(ProbFuncStr) +         
         "&ProbLevel=" + encodeURIComponent(ProbLevel) + "&ProbMemo=" + ProbMemo + "&" + make_selection_modify_query_str();
   url_str = "Problems_modify.php?";

   $.ajax
   ({
      beforeSend: function()
      {
         //alert(url_str + str);
      },
      // should be POST
      type: "GET",
      url: url_str + str,
      cache: false,
      
      success: function(res)
      {
         if (res.match(/^-\d+$/))  //failed
         {
            if (res == <?php echo ERR_PROB_NOT_EXIST ?>)
            {
               alert("<?php echo MSG_ERR_PROB_NOT_EXIST ?>");
            }
            else if (res == <?php echo ERR_PROB_DESC_FORMAT ?>)
            {
               alert("<?php echo MSG_ERR_PROB_DESC_FORMAT ?>");
            }
            else if (res == <?php echo ERR_PROB_SELECTOR_FORMAT ?>)
            {
               alert("<?php echo MSG_ERR_PROB_SELECTOR_FORMAT ?>");
            }
            else if (res == <?php echo ERR_PROB_ANSWER_FORMAT ?>)
            {
               alert("<?php echo MSG_ERR_PROB_ANSWER_FORMAT ?>");
            }
            else if (res == <?php echo ERR_PROB_CATEGORY_FORMAT ?>)
            {
               alert("<?php echo MSG_ERR_PROB_CATEGORY_FORMAT ?>");
            }
            else if (res == <?php echo ERR_PROB_LEVEL_FORMAT ?>)
            {
               alert("<?php echo MSG_ERR_PROB_LEVEL_FORMAT ?>");
            }
         }
         else  //success
         {
            alert("题目新增/修改成功，页面关闭后请自行刷新");
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
         <th>题目描述：</th>
         <td><Input type=text name=ProbDescModify size=50 value="<?php echo $ProbDesc;?>"></td>
      </tr>
      <tr>
         <th>题目类型：</th>
         <td><Input type=text name=ProbType size=50 disabled="disabled" value="<?php
            if ($ProbType == TRUE_FALSE_PROB) 
            {
               echo "是非";
            }
            else if ($ProbType == SINGLE_CHOICE_PROB)
            {
               echo "单选";
            }
            else if ($ProbType == MULTI_CHOICE_PROB)
            {
               echo "多选";
            }
            ?>">
         </td>
      </tr>
   <?php
      for ($i=ord("A"); $i<=ord("I"); $i++)
      {
         $selector = "ProbSel".chr($i);

         echo "<tr><th>题目".chr($i)."</th>";
         echo "<td><Input class=ProbSel id=ProbSel".chr($i)." type=text size=100 value=\"".$$selector."\"</td></tr>";
      }
   ?>
      <tr>
         <th>题目答案:</th>
         <td><Input type=text name=ProbAnswerModify size=50 value="<?php echo $ProbAnswer?>"></td>
      </tr>
      <?php
         // Function Adaptation
         $func_type = FUNCTION_ADAPTATION;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               $func_ids = get_function_id($ProbCategory);
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  if (in_array($row['FunctionId'], $func_ids))
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify checked value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  else
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  $i++;
               }
               echo "</tr>";
            }
         }
 
         // Function Product
         $func_type = FUNCTION_PRODUCT;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               $func_ids = get_function_id($ProbCategory);
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  if (in_array($row['FunctionId'], $func_ids))
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify checked value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  else
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  $i++;
               }
               echo "</tr>";
            }
         }
 
         // Function Other
         $func_type = FUNCTION_OTHER;
         $str_query = "Select * from functions where FunctionType=$func_type";
         
         if($result = mysqli_query($link, $str_query))
         { 
            $row_number = mysqli_num_rows($result);
            if ($row_number > 0)
            {
               $func_ids = get_function_id($ProbCategory);
               echo "<tr><th>".get_func_type_name($func_type)."</th><td>";
 
               $i = 0;
               while ($i < $row_number)
               {
                  $row = mysqli_fetch_assoc($result);
                  if (in_array($row['FunctionId'], $func_ids))
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify checked value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  else
                  {
                     echo "<Input type=checkbox name=ProbCategoryModify value=\"".$row['FunctionId']."\">".$row['FunctionName'].'&nbsp;';
                  }
                  $i++;
               }
               echo "</tr>";
            }
         }
      ?>
      <tr>
         <th>题目难易:</th>
         <td>
            <select name="ProbLevelModify">
               <option value=<?php echo EASY_LEVEL?> <?php if ($ProbLevel == EASY_LEVEL) {echo "selected";}?>/>易
               <option value=<?php echo MID_LEVEL?> <?php if ($ProbLevel == MID_LEVEL) {echo "selected";}?>/>中
               <option value=<?php echo HIGH_LEVEL?> <?php if ($ProbLevel == HIGH_LEVEL) {echo "selected";}?>/>难
            <select>
         </td>
      </tr>
      <tr>
         <th>题目备注：</th>
         <td><Input type=text name=ProbMemoModify size=50 value="<?php echo $ProbMemo?>"></td>
      </tr>
      <tr>
      </tr>
<?php
   if ($ProbStatus != 1)
   {
?>       
      <tr>
         <th colspan="4" class="submitBtns">
            <a class="btn_submit_new modifyProbsContent"><input name="modifyProbsButton" type="button" value="保存" OnClick="modifyProbsContent(<?php echo $ProbId;?>)"></a>
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