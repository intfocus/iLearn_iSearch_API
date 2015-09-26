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
   if(($CoursePacketId = check_number($_GET["CoursePacketId"])) == SYMBOL_ERROR)
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
   
   function CWList($coursewareList)
   {
      if(strlen($coursewareList)>0){
         $coursewareList = substr($coursewareList,1);
         $coursewareList = substr($coursewareList,0,-1);
         $coursewareLists = explode(",,",$coursewareList);
      }
      else{
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
      
      $return_string = ""; 
      //----- query -----
      //***Step16 页面搜索SQl语句 起始
      $i = 0;
      foreach ($coursewareLists as $cl) {
         $top = $i * 60;
         $str_query1 = "select * from coursewares where CoursewareId=$cl";
         if($result = mysqli_query($link, $str_query1)){
            $row = mysqli_fetch_assoc($result);
            $return_string = $return_string . "<div class='brick small' name='" . $cl . "'  style='position: absolute; left: 0px; top: " . $top . "px;'>" . $row["CoursewareName"] . "<div class='delete'>&times;</div></div>";
         }
         else
         {
            if($link){
               mysqli_close($link);
            }
            sleep(DELAY_SEC);
            echo -__LINE__;
            return;
         }
         $i = $i + 1;
      }
      return $return_string;
   }
   
   $dataPPTs = array();
   $dataExam = array();
   $dataQuestion = array();
   
   class StuPPT{
      public $PPTId;
      public $PPTName;
   }
   
   class StuExam{
      public $ExamId;
      public $ExamName;
   }
   
   class StuQuestion{
      public $QuestionId;
      public $QuestionName;
   }
   
   $str_ppts = "select PPTId, PPTName from ppts where Status=1";
   if($result = mysqli_query($link, $str_ppts))
   {
      $row_number = mysqli_num_rows($result);
      if($row_number > 0)
      {
         while($row = mysqli_fetch_assoc($result))
         {
            $ps = new StuPPT();
            $ps->PPTId = $row["PPTId"];
            $ps->PPTName = $row["PPTName"];
            array_push($dataPPTs, $ps);
         }
      }
   }
   
   
   $str_exams = "select ExamId, ExamName from exams where ExamType=2";
   if($result = mysqli_query($link, $str_exams))
   {
      $row_number = mysqli_num_rows($result);
      if($row_number > 0)
      {
         while($row = mysqli_fetch_assoc($result))
         {
            $es = new StuExam();
            $es->ExamId = $row["ExamId"];
            $es->ExamName = $row["ExamName"];
            array_push($dataExam, $es);
         }
      }
   }
   
   $str_questions = "select QuestionId, QuestionName from question where Status=1";
   if($result = mysqli_query($link, $str_questions))
   {
      $row_number = mysqli_num_rows($result);
      if($row_number > 0)
      {
         while($row = mysqli_fetch_assoc($result))
         {
            $qs = new StuQuestion();
            $qs->QuestionId = $row["QuestionId"];
            $qs->QuestionName = $row["QuestionName"];
            array_push($dataQuestion, $qs);
         }
      }
   }
   
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if ($cmd == "read") // Load
   {
      $str_query1 = "Select * from coursepacket where CoursePacketId=$CoursePacketId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $CoursePacketId = $row["CoursePacketId"];
            $CoursePacketName = $row["CoursePacketName"];
            $CoursePacketDesc = $row["CoursePacketDesc"];
            $CoursewarePacketList = $row["CoursewarePacketList"];
            $CoursewareList = $row["CoursewareList"];
            $Status = $row["Status"];
            $ExamList = $row["ExamList"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $QuestionnaireList = $row["QuestionnaireList"];
            $AvailableTimeBegin = $row["AvailableTimeBegin"]== null ? '' : date("Y/m/d",strtotime($row["AvailableTimeBegin"]));
            $AvailableTimeEnd = $row["AvailableTimeEnd"]== null ? '' : date("Y/m/d",strtotime($row["AvailableTimeEnd"]));
            $EditTime = $row["EditTime"] == null ? '' : date("Y/m/d",strtotime($row["EditTime"]));
            $TitleStr = "课程包修改";
            if ($Status == 1)
               $TitleStr = "课程包查看 (上架状态无法修改)";
            $cplist = CWList($CoursewareList);
         }
         else
         {
            $CoursePacketId = 0;
            $CoursePacketName = "";
            $CoursePacketDesc = "";
            $CoursewarePacketList = "";
            $CoursewareList = "";
            $TitleStr = "课程包新增";
            $Status = 0;
            $ExamList = "";
            $StatusStr = "";
            $QuestionnaireList = "";
            $AvailableTimeBegin = "";
            $AvailableTimeEnd = "";
            $cplist = "";
         }
      }
   }
   else if ($CoursePacketId == 0) // Insert
   {
      $CoursePacketName = $_POST["CoursePacketName"];
      $CoursePacketDesc = $_POST["CoursePacketDesc"];
      $CoursewarePacketList = $_POST["CoursewarePacketList"];
      $CoursewareList = $_POST["CoursewareList"];
      $ExamList = $_POST["ExamList"];
      $QuestionnaireList = $_POST["QuestionnaireList"];
      $AvailableTimeBegin = "'" . $_POST["AvailableTimeBegin"] . "'";
      $AvailableTimeEnd = "'" . $_POST["AvailableTimeEnd"] . "'";
      $str_query1 = "Insert into CoursePacket (CoursePacketName,CoursePacketDesc,CoursewarePacketList,CoursewareList,ExamList,QuestionnaireList,AvailableTimeBegin,AvailableTimeEnd,CreatedUser,CreatedTime,EditUser,EditTime,Status)" 
                  . " VALUES('$CoursePacketName','$CoursePacketDesc','$CoursewarePacketList','$CoursewareList','$ExamList','$QuestionnaireList',$AvailableTimeBegin,$AvailableTimeEnd,$user_id,now(),$user_id,now(),0)" ;
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
      
      $CoursePacketName = $_POST["CoursePacketName"];
      $CoursePacketDesc = $_POST["CoursePacketDesc"];
      $CoursewarePacketList = $_POST["CoursewarePacketList"];
      $CoursewareList = $_POST["CoursewareList"];
      $ExamList = $_POST["ExamList"];
      $QuestionnaireList = $_POST["QuestionnaireList"];
      $AvailableTimeBegin = "'" . $_POST["AvailableTimeBegin"] . "'";
      $AvailableTimeEnd = "'" . $_POST["AvailableTimeEnd"] . "'";
      $str_query1 = "Update CoursePacket set CoursePacketName='$CoursePacketName', CoursePacketDesc='$CoursePacketDesc', 
         CoursewarePacketList='$CoursewarePacketList', CoursewareList='$CoursewareList', ExamList='$ExamList', 
         QuestionnaireList='$QuestionnaireList', AvailableTimeBegin=$AvailableTimeBegin, AvailableTimeEnd=$AvailableTimeEnd, 
         EditUser=$user_id, EditTime=now() where CoursePacketId=$CoursePacketId";
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
      
      <!-- Bootstrap core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/bootstrap-reset.css" rel="stylesheet">
      <!--Animation css-->
      <link href="css/animate.css" rel="stylesheet">
      <!--Icon-fonts css-->
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/ionicon/css/ionicons.min.css" rel="stylesheet" />
      <!-- DataTables -->
      <link href="assets/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
      <!--Form Wizard-->
      <link rel="stylesheet" type="text/css" href="assets/form-wizard/jquery.steps.css" />
      <!-- Plugins css-->
      <link href="assets/timepicker/bootstrap-datepicker.min.css" rel="stylesheet" />
      <link rel="stylesheet" type="text/css" href="assets/select2/select2.css" />
      <!-- Custom styles for this template -->
      <link href="css/style.css" rel="stylesheet">
      <link href="css/helper.css" rel="stylesheet">
      <link href="css/style-responsive.css" rel="stylesheet" />
      <link href='css/jquery.gridly.css' rel='stylesheet' type='text/css'>
      <link href='css/sample.css' rel='stylesheet' type='text/css'>
      <script src='js/jquery.js' type='text/javascript'></script>
      <script src='js/jquery.gridly.js' type='text/javascript'></script>
      <script src='js/sample.js' type='text/javascript'></script>
      <script src='js/rainbow.js' type='text/javascript'></script>
      <!-- End of tree view -->
      <!--[if lt IE 10]>
      <script type="text/javascript" src="lib/PIE.js"></script>
      <![endif]-->
      <title>武田 - 课程包页面</title>
      <Script Language=JavaScript>
         function cnm()
         {
            var num = $("div[class='brick small']").length;
            var cnm = "";
            for(var i = 0; i<num; i++)
            {
               var n = i * 60;
               $("div[class='brick small']").each(function () {
                  var m = $(this).attr("style").replace('position: absolute; left: 0px; top: ','').replace('px;','');
                  if(n == m)
                  {
                     cnm = cnm + "," + $(this).attr("name") + ",";
                  }
               });
            }
            $("input[name='CoursewareListModify']").val(cnm);
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
            $('.gridly').append("<?php echo $cplist;?>");
            var num = $("div [class='brick small']").length;
            num = num * 60;
            var s = "height: " + num + "px;";
            $("div[class='gridly']").attr("style",s);
         }
         
         function test()
         {
            alert("OK");
            cpName = document.getElementsByName("cpModify")
            alert(cpName.length);
         }

         //***Step12 修改页面点击保存按钮出发Ajax动作
         function modifyCoursePacketsContent(CoursePacketId)
         {
            cnm();
            CoursePacketName = document.getElementsByName("CoursePacketNameModify")[0].value.trim();
            CoursePacketDesc = document.getElementsByName("CoursePacketDescModify")[0].value.trim();
            AvailableTimeBegin = document.getElementsByName("AvailableTimeBegin")[0].value.trim();
            AvailableTimeEnd = document.getElementsByName("AvailableTimeEnd")[0].value.trim();
            CPListModify = document.getElementsByName("cpListModify")[0].value.trim();
            EListModify = document.getElementsByName("eListModify")[0].value.trim();
            QListModify = document.getElementsByName("qListModify")[0].value.trim();
            CoursewareListModify = document.getElementsByName("CoursewareListModify")[0].value.trim();
            
            if (CoursePacketName.length == 0 || CoursePacketDesc.length == 0)
            {
               alert("课程包名称及课程包备注不可为空白");
               return;
            }
            
            if (CoursePacketName.length > 255 || CoursePacketDesc.length > 255){
               alert("课程包名称及课程包备注长度过长！请缩短后重新保存。");
               return;
            }
            
            if (AvailableTimeBegin.length > 0)
            {
               if (AvailableTimeBegin.length != 10)
               {
                  alert("日期格式必须为 yyyy/mm/dd");
                  return;
               }
               var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
               if (!reg.exec(AvailableTimeBegin))
               {
                  alert("日期格式必须为 yyyy/mm/dd " + AvailableTimeBegin);
                  return;
               }
            }
            
            if (AvailableTimeEnd.length > 0)
            {
               if (AvailableTimeEnd.length != 10)
               {
                  alert("日期格式必须为 yyyy/mm/dd");
                  return;
               }
               var reg=/2[0-9]{3}\/(01|02|03|04|05|06|07|08|09|10|11|12)\/(([0-2][1-9])|([1-3][0-1]))/;
               if (!reg.exec(AvailableTimeEnd))
               {
                  alert("日期格式必须为 yyyy/mm/dd " + AvailableTimeEnd);
                  return;
               }
            }
            
            url_str = "CoursePackets_modify.php?cmd=write&CoursePacketId=" + CoursePacketId;
         
            // alert(url_str);
            $.ajax
            ({
               beforeSend: function()
               {
                  //alert(str);
               },
               type: "POST",
               url: url_str,
               data:{
                  CoursePacketName:CoursePacketName,
                  CoursePacketDesc:CoursePacketDesc,
                  CoursewarePacketList:CPListModify,
                  CoursewareList:CoursewareListModify,
                  ExamList:EListModify,
                  QuestionnaireList:QListModify,
                  AvailableTimeBegin:AvailableTimeBegin,
                  AvailableTimeEnd:AvailableTimeEnd
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
                     alert("课程包新增/修改成功，页面关闭后请自行刷新");
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
      <!--Main Content Start -->
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
      <!-- Page Content Start -->
      <!-- ================== -->
      <div id="content">
         <input type="hidden" id="cpListModify" name="cpListModify" value="<?php echo $CoursewarePacketList; ?>" />
         <input type="hidden" id="eListModify" name="eListModify" value="<?php echo $ExamList; ?>" />
         <input type="hidden" id="qListModify" name="qListModify" value="<?php echo $QuestionnaireList; ?>" />
         <INPUT type="hidden" name="CoursewareListModify" value="<?php echo $CoursewareList;?>">
         <h3>课程包、练习考卷、问卷管理</h3>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th><label  style="width: 100px;">课程包名称 *</label></th>
               <td><input style="width: 200px;" name="CoursePacketNameModify" type="text" value="<?php echo $CoursePacketName; ?>"></td>
            </tr>
            <tr>
               <th><label style="width: 100px;">课程包说明 *</label></th>
               <td>
                  <input style="width: 200px;" name="CoursePacketDescModify" type="text" value="<?php echo $CoursePacketDesc; ?>">
               </td>
            </tr>
            <tr>
               <th><label style="width: 200px;">课程包有效起始时间 *</label></th>
               <td style="width: 100px;">
                  <input type="text" style="width: 200px;" placeholder="yyyy/mm/dd" id="datepicker1" name="AvailableTimeBegin" value="<?php echo $AvailableTimeBegin; ?>">
               </td>
               <td>
                  <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
               </td>
            </tr>
            <tr>
               <th><label style="width: 200px;">课程包有效截止时间 *</label></th>
               <td style="width: 100px;">
                  <input type="text"  style="width: 200px;" placeholder="yyyy/mm/dd" id="datepicker2" name="AvailableTimeEnd" value="<?php echo $AvailableTimeBegin; ?>">
               </td>
               <td>
                  <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
               </td>
            </tr>
            <tr>
               <th><label style="width: 100px;">课程包 *</label></th>
               <td>
                  <select id="cpList" class="select1" multiple data-placeholder="Choose a Country..." style="width: 100px;">
                     <option value="#">&nbsp;</option>
                     <?php
                       foreach ($dataPPTs as $ppt) {
                          $pptidtmp = "," . $ppt->PPTId . ",";
                          $tmp = strpos($CoursewarePacketList, $pptidtmp);
                          if($tmp !== false)
                          {
                     ?>
                     <option value="<?php echo $ppt->PPTId; ?>" selected="selected"><?php echo $ppt->PPTName; ?></option>
                     <?php
                          }
                          else 
                          {
                     ?>
                     <option value="<?php echo $ppt->PPTId; ?>"><?php echo $ppt->PPTName; ?></option>
                     <?php
                          }
                       }
                     ?>
                  </select>
               </td>
            </tr>
            <tr>
               <th><label style="width: 100px;">练习考试 *</label></th>
               <td>
                  <select id="eList" class="select2" multiple data-placeholder="Choose a Country..." style="width: 100px;">
                     <option value="#">&nbsp;</option>
                     <?php
                       foreach ($dataExam as $exam) {
                          $examidtmp = "," . $exam->ExamId . ",";
                          $tmp = strpos($ExamList, $examidtmp);
                          if($tmp !== false)
                          {
                     ?>
                     <option value="<?php echo $exam->ExamId; ?>" selected="selected"><?php echo $exam->ExamName; ?></option>
                     <?php
                          }
                          else 
                          {
                     ?>
                     <option value="<?php echo $exam->ExamId; ?>"><?php echo $exam->ExamName; ?></option>
                     <?php
                          }
                       }
                     ?>
                  </select>
               </td>
            </tr>
            <tr>
               <th><label style="width: 100px;">问卷管理 *</label></th>
               <td>
                  <select id="qList" class="select2" multiple data-placeholder="Choose a Country..." style="width: 100px;">
                     <option value="#">&nbsp;</option>
                     <?php
                       foreach ($dataQuestion as $question) {
                          $questionidtmp = "," . $question->QuestionId . ",";
                          $tmp = strpos($QuestionnaireList, $questionidtmp);
                          if($tmp !== false)
                          {
                     ?>
                     <option value="<?php echo $question->QuestionId; ?>" selected="selected"><?php echo $question->QuestionName; ?></option>
                     <?php
                          }
                          else 
                          {
                     ?>
                     <option value="<?php echo $question->QuestionId; ?>"><?php echo $question->QuestionName; ?></option>
                     <?php
                          }
                       }
                     ?>
                  </select>
               </td>
            </tr>
            <tr>
               <td colspan="3" style="width: 100%">
                  <div class="form-group clearfix">
                     <section class='example'>
                        <div class='gridly'></div>
                        <p class='actions'>
                           <!-- <input type="button" id="addshow" class='button' value="Show" /> -->
                        </p>
                     </section>
                  </div>
                  <section class="content">
                     <!-- Page Content Start -->
                     <!-- ================== -->
                     <div class="wraper container-fluid">
                        <div class="page-title"> 
                           <h3 class="title">Datatable</h3> 
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="panel panel-default">
                                 <div class="panel-heading">
                                    <h3 class="panel-title">Datatable</h3>
                                    <a class='add button' href='#'>Add</a>
                                 </div>
                                 <div class="panel-body">
                                    <div class="row">
                                       <div class="col-md-12 col-sm-12 col-xs-12">
                                          <table id="datatable" class="table table-striped table-bordered">
                                             <thead>
                                                <tr>
                                                   <th>动作</th>
                                                   <th>课件名称</th>
                                                   <th>课件备注</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <?php
                                                   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
                                                   if (!$link)  //connect to server failure    
                                                   {
                                                      sleep(DELAY_SEC);
                                                      echo DB_ERROR;       
                                                      return;
                                                   }  
                                                   $str_query1 = "SELECT CoursewareId,CoursewareName,CoursewareDesc,PAList,ProductList FROM coursewares";
                                                   $result = mysqli_query($link, $str_query1);
                                                   $rownum = mysqli_num_rows($result);
                                                   while($row = mysqli_fetch_assoc($result))
                                                   {
                                                      $CoursewareDesc = $row["CoursewareDesc"];
                                          $CoursewareName = $row["CoursewareName"];
                                          $CoursewareId = $row["CoursewareId"];
                                          
                                          echo "<tr>";
                                          echo "<td><input type='checkbox' name='cbcw' value='". $CoursewareId . "&&" . $CoursewareName ."' class='checkbox'></td>";
                                          echo "<td>$CoursewareName</td>";
                                          echo "<td>$CoursewareDesc</td>";
                                          echo "</tr>";
                                       }
                                    ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div> <!-- End Row -->
         </div>
         <!-- Page Content Ends -->
         <!-- ================== -->
      </section>
               </td>
            </tr>
            <tr>
               <td colspan="3"><label style="width: 100px;">(*) Mandatory</label></td>
            </tr>
            <tr>
               <td><a class="btn_submit_new modifyQuestionsContent"><input name="modifyQuestionsButton" type="button" value="保存" OnClick="modifyCoursePacketsContent(<?php echo $CoursePacketId;?>)"></a></td>
            </tr>
         </table>
      </div>
      <!-- Footer Start -->
      <footer class="footer">
         2015 © Velonic.
      </footer>
      <!-- Footer Ends -->
      <!-- Main Content Ends -->
      <!-- js placed at the end of the document so the pages load faster -->
      <script type="text/javascript" src="assets/jquery-multi-select/jquery.multi-select.js"></script>
      <script src="assets/select2/select2.min.js" type="text/javascript"></script>
      <!--Form Wizard-->
      <script src="assets/form-wizard/jquery.steps.min.js" type="text/javascript"></script>
      <script type="text/javascript" src="assets/jquery.validate/jquery.validate.min.js"></script>
      <!--wizard initialization-->
      <script src="assets/timepicker/bootstrap-datepicker.js"></script>
      <script src="assets/form-wizard/wizard-init.js" type="text/javascript"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/pace.min.js"></script>
      <script src="js/wow.min.js"></script>
      <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
      <script src="js/jquery.app.js"></script>
      <script src="assets/datatables/jquery.dataTables.min.js"></script>
      <script src="assets/datatables/dataTables.bootstrap.js"></script>
      <script>
         $("#cpList").change(function(){
            var cplm = "";
            $(this).find("option:selected").each(function(){
               cplm = cplm + "," + $(this).val() + ",";
            });
            $("#cpListModify").val(cplm);
         });
         
         $("#eList").change(function(){
            var elm = "";
            $(this).find("option:selected").each(function(){
               elm = elm + "," + $(this).val() + ",";
            });
            $("#eListModify").val(elm);
         });
         
         $("#qList").change(function(){
            var qlm = "";
            $(this).find("option:selected").each(function(){
               qlm = qlm + "," + $(this).val() + ",";
            });
            $("#qListModify").val(qlm);
         });
         
         jQuery('#datepicker1').datepicker();
         jQuery('#datepicker2').datepicker();
         jQuery(document).ready(function() {
            $('#datatable').dataTable();
            // Select2
            jQuery(".select1").select2({
               width: '100%'
            });
            jQuery(".select2").select2({
               width: '100%'
            });
         });
      </script>
   </body>
</html>
<!--Step15 新增修改页面    结束 -->