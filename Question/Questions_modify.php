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
   if(($QuestionId = check_number($_GET["QuestionId"])) == SYMBOL_ERROR)
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
   
   class StuQT{
      public $QTId;
      public $QTName;
   }
   
   $qts = array();
   
   $str_qt = "select QuestionTemplateId, QuestionTemplateName from wutian.questiontemplate where Status = 1;";
   if($rs = mysqli_query($link, $str_qt))
   {
      while ($row = mysqli_fetch_assoc($rs)) {
         $suqt = new StuQT();
         $suqt->QTId = $row["QuestionTemplateId"];
         $suqt->QTName = $row["QuestionTemplateName"];
         array_push($qts, $suqt);
      }
   }
   
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if ($cmd == "read") // Load
   {
      $str_query1 = "select * from question where QuestionId=$QuestionId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $QuestionId = $row["QuestionId"];
            $QuestionTemplateId = $row["QuestionTemplateId"];
            $QuestionName = $row["QuestionName"];
            $QuestionDesc = $row["QuestionDesc"];
            $StartTime = date("Y/m/d",strtotime($row["StartTime"]));
            $EndTime = date("Y/m/d",strtotime($row["EndTime"]));
            $Status = $row["Status"];
            $CreatedUser = $row["CreatedUser"];
            $CreatedTime = $row["CreatedTime"];
            $EditUser = $row["EditUser"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"];
            $CreatedTime = $row["CreatedTime"];
            $TitleStr = "问卷修改";
            if ($Status == 1)
               $TitleStr = "问卷查看 (上架状态无法修改)";
         }
         else
         {
            $QuestionId = 0;
            $QuestionTemplateId = 0;
            $QuestionName = "";
            $QuestionDesc = "";
            $StartTime = "";
            $EndTime = "";
            $Status = 0;
            $CreatedUser = "";
            $CreatedTime = "";
            $EditUser = "";
            $EditTime = "";
            $CreatedTime = "";
            $TitleStr = "问卷新增";
         }
      }
   }
   else if ($QuestionId == 0) // Insert
   {
      $QuestionName = $_POST["QuestionName"];
      $QuestionDesc = $_POST["QuestionDesc"];
      $QuestionTemplateId = $_POST["QuestionTemplateId"];
      $StartTime = $_POST["StartTime"];
      $EndTime = $_POST["EndTime"];
      $str_query1 = "Insert into question (QuestionTemplateId, QuestionName, QuestionDesc, StartTime, EndTime, Status, CreatedUser, CreatedTime, EditUser, EditTime)" 
                  . " VALUES($QuestionTemplateId,'$QuestionName','$QuestionDesc','$StartTime','$EndTime',1,$user_id,now(),$user_id,now());" ;
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
      $QuestionName = $_POST["QuestionName"];
      $QuestionDesc = $_POST["QuestionDesc"];
      $QuestionTemplateId = $_POST["QuestionTemplateId"];
      $StartTime = $_POST["StartTime"];
      $EndTime = $_POST["EndTime"];
      if ($StartTime == "")
         $StartTime = "NULL";
      if ($EndTime == "")
         $EndTime = "NULL";
      $str_query1 = "Update question set QuestionName='$QuestionName', QuestionDesc='$QuestionDesc', QuestionTemplateId=$QuestionTemplateId, StartTime='$StartTime', EndTime='$EndTime', EditUser=$user_id, EditTime=now() where QuestionId=$QuestionId";
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
      <!-- Bootstrap core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/bootstrap-reset.css" rel="stylesheet">


      <!-- Plugins css-->
      <link rel="stylesheet" type="text/css" href="assets/jquery-multi-select/multi-select.css" />
      <link rel="stylesheet" type="text/css" href="assets/select2/select2.css" />


      <!-- Custom styles for this template -->
      <link href="css/style.css" rel="stylesheet">
      <link href="css/helper.css" rel="stylesheet">
      <link href="css/style-responsive.css" rel="stylesheet" />
      <!-- End of tree view -->
      <!--[if lt IE 10]>
      <script type="text/javascript" src="lib/PIE.js"></script>
      <![endif]-->
      <title>武田 - 分类页面</title>
      <!-- BEG_ORISBOT_NOINDEX -->
      <!-- Billy 2012/2/3 -->
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
         function modifyQuestionsContent(QuestionId)
         {
            QuestionName = document.getElementsByName("QuestionNameModify")[0].value.trim();
            QuestionDesc = document.getElementsByName("QuestionDescModify")[0].value.trim();
            QuestionTemplateId = document.getElementsByName("QuestionTemplateIdModify")[0].value.trim();
            StartTime = document.getElementsByName("searchQuestionsfrom16")[0].value.trim();
            EndTime = document.getElementsByName("searchQuestionsto16")[0].value.trim();
            
            if (QuestionName.length == 0 && QuestionDesc.length == 0)
            {
               alert("问卷名称及问卷描述不可为空白");
               return;
            }
            
            if (QuestionName.length > 100 && QuestionDesc.length > 1000)
            {
               alert("问卷名称及问卷描述长度过长！请缩短后重新保存。");
               return;
            }
         
            if (StartTime.length > 0)
            {
               if (StartTime.length != 10)
               {
                  alert("日期格式必须为 yyyy/mm/dd");
                  return;
               }
               var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
               if (!reg.exec(StartTime))
               {
                  alert("日期格式必须为 yyyy/mm/dd " + StartTime);
                  return;
               }
            }
         
            if (EndTime.length > 0)
            {
               if (EndTime.length != 10)
               {
                  alert("日期格式必须为 yyyy/mm/dd");
                  return;
               }
               var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
               if (!reg.exec(StartTime))
               {
                  alert("日期格式必须为 yyyy/mm/dd " + StartTime);
                  return;
               }
            }
            
            str = "cmd=write&QuestionId=" + QuestionId;
            url_str = "../Question/Questions_modify.php?";
         
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
                  QuestionName:QuestionName,
                  QuestionDesc:QuestionDesc,
                  QuestionTemplateId:QuestionTemplateId,
                  StartTime:StartTime,
                  EndTime:EndTime
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
                     alert("问卷新增/修改成功，页面关闭后请自行刷新");
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
               <th>问卷名称：</th>
               <td><Input type=text name="QuestionNameModify" size=50 value="<?php echo $QuestionName;?>"></td>
            </tr>
            <tr>
               <th>问卷模板名称：</th>
               <td>
                  <section class="content">
                     <div class="row">
                        <div class="col-lg-6">
                           <div class="panel-body" style="width: 550px;">
                              <form action="#" class="form-horizontal">
                                 <div class="form-group">
                                    <div class="col-sm-9">
                                       <select name="QuestionTemplateIdModify" class="select2" data-placeholder="Choose a Country...">
                                          <option value="#">&nbsp;</option>
                                       <?php
                                          foreach ($qts as $qt) {
                                       ?>
                                          <option value="<?php echo $qt->QTId ?>"><?php echo $qt->QTName ?></option>
                                       <?php
                                          }
                                       ?>
                                       </select>
                                    </div>
                                 </div>
                              </form>
                           </div> <!-- panel-body -->
                        </div> <!-- col -->
                     </div> <!-- End row -->
                     <!-- Page Content Ends -->
                     <!-- ================== -->
                  </section>
               </td>
            </tr>
            <tr>
               <th>问卷描述：</th>
               <td><Textarea name="QuestionDescModify" rows=30 cols=100><?php echo $QuestionDesc;?></Textarea></td>
            </tr> 
            <tr>
               <th>问卷开始时间 ：</th>
               <td>
                  <input id="from16" type="text" name="searchQuestionsfrom16" class="from" readonly="true" value="<?php echo $StartTime; ?>"/>
               </td>
            </tr>
            <tr>
               <th>问卷截止时间：</th>
               <td>
                  <input id="to16" type="text" class="to" name="searchQuestionsto16" readonly="true" value="<?php echo $EndTime; ?>"/>
               </td>
            </tr>
            <?php
               if ($Status != 1)
               {
            ?>       
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new modifyQuestionsContent"><input name="modifyQuestionsButton" type="button" value="保存" OnClick="modifyQuestionsContent(<?php echo $QuestionId;?>)"></a>
               </th>
            </tr>      
            <?php
               }
            ?>   
         </table>
      </div>
      <!-- js placed at the end of the document so the pages load faster -->
      <!-- <script src="js/jquery.js"></script> -->

      <script src="assets/select2/select2.min.js" type="text/javascript"></script>


      <script>
          jQuery(document).ready(function() {
             $(".select2").find("option[value='<?php echo $QuestionTemplateId ?>']").attr("selected",true);
              // Select2
              jQuery(".select2").select2({
                  width: '100%'
              });
          });
      </script>
   </body>
</html>
<!--Step15 新增修改页面    结束 -->