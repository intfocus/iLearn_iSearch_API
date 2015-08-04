<?php
   require_once('QuestionTemplates_utility.php');
 
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
   if(($QTId = check_number($_GET["QTId"])) == SYMBOL_ERROR)
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
   
   class StuQD{
      public $QUESTIONTEMPLATEID;
      public $PROBLEMTYPE;
      public $PROBLEMDESC;
      public $PROBLEMSELECTA;
      public $PROBLEMSELECTB;
      public $PROBLEMSELECTC;
      public $PROBLEMSELECTD;
      public $PROBLEMSELECTE;
      public $PROBLEMSELECTF;
      public $PROBLEMSELECTG;
      public $PROBLEMSELECTH;
      public $PROBLEMSELECTI;
      public $GROUPNAME;
   }
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if (strcmp($cmd, "read") == 0) // Load
   {  
      $str_query1 = "Select * from questiontemplate where questiontemplateid=$QTId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $QTId = $row["QuestionTemplateId"];
            $QTName = $row["QuestionTemplateName"];
            $QTDesc = $row["QuestionTemplateDesc"];
            $QTStatus = $row["Status"];

            if ($QTStatus == 1)
               $TitleStr = "问卷模板查看 (上架状态无法修改)";
         }
         else
         {
            $QTId = 0;
            $QTName = "";
            $QTDesc = "";
            $TitleStr = "问卷模板新增";
            $QTStatus = 0;
         }
      }
      $str_query2 = "SELECT QUESTIONTEMPLATEID, PROBLEMTYPE, PROBLEMDESC, PROBLEMSELECTA, PROBLEMSELECTB, PROBLEMSELECTC, 
            PROBLEMSELECTD, PROBLEMSELECTE, PROBLEMSELECTF, PROBLEMSELECTG, PROBLEMSELECTH, PROBLEMSELECTI, GROUPNAME 
            FROM QUESTIONDETAIL where questiontemplateid=$QTId";
      $QDs = array();
      if($rs = mysqli_query($link, $str_query2))
      {
         $row_number = mysqli_num_rows($rs);
         while($row = mysqli_fetch_assoc($rs)){      
            $sn = new StuQD();
            $sn->QUESTIONTEMPLATEID = $row['QUESTIONTEMPLATEID'];
            $sn->PROBLEMTYPE = $row['PROBLEMTYPE'];
            $sn->PROBLEMDESC = $row['PROBLEMDESC'];
            $sn->PROBLEMSELECTA = $row['PROBLEMSELECTA'];
            $sn->PROBLEMSELECTB = $row['PROBLEMSELECTB'];
            $sn->PROBLEMSELECTC = $row['PROBLEMSELECTC'];
            $sn->PROBLEMSELECTD = $row['PROBLEMSELECTD'];
            $sn->PROBLEMSELECTE = $row['PROBLEMSELECTE'];
            $sn->PROBLEMSELECTF = $row['PROBLEMSELECTF'];
            $sn->PROBLEMSELECTG = $row['PROBLEMSELECTG'];
            $sn->PROBLEMSELECTH = $row['PROBLEMSELECTH'];
            $sn->PROBLEMSELECTI = $row['PROBLEMSELECTI'];
            $sn->GROUPNAME = $row['GROUPNAME'];
            array_push($QDs,$sn);
         }
      }
   }
   else if ($cmd == "update")// Update
   {
      $QTId = $_GET["QTId"];
      $QTDesc = $_POST["QTDesc"];
      $QTName = $_POST["QTName"];
      
      $str_query1 = "Update questiontemplate set QuestionTemplateDesc='$QTDesc' where QuestionTemplateId =$QTId";
      
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

<!DOCTYPE html>
<html lang="zh-CN">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
      <meta http-equiv="Pragma" content="no-cache">
      <meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
      <link type="image/x-icon" href="../images/wutian.ico" rel="shortcut icon">
      <link rel="stylesheet" type="text/css" href="../lib/yui-cssreset-min.css">
      <link rel="stylesheet" type="text/css" href="../lib/yui-cssfonts-min.css">
      <link rel="stylesheet" type="text/css" href="../css/OSC_layout_new.css">
      <link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
      <!-- for tree view -->
      <!--[if lt IE 10]>
      <script type="text/javascript" src="lib/PIE.js"></script>
      <![endif]-->
      <!-- Bootstrap core CSS -->
      <link href="../newui/css/bootstrap.min.css" rel="stylesheet">
      <link href="../newui/css/bootstrap-reset.css" rel="stylesheet">

      <!--Animation css-->
      <link href="../newui/css/animate.css" rel="stylesheet">

      <!--Icon-fonts css-->
      <link href="../newui/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="../newui/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

      <!--Morris Chart CSS -->
      <link rel="stylesheet" href="../newui/assets/morris/morris.css">

      <!-- sweet alerts -->
      <link href="../newui/assets/sweet-alert/sweet-alert.min.css" rel="stylesheet">

      <!-- Custom styles for this template -->
      <link href="../newui/css/style.css" rel="stylesheet">
      <link href="../newui/css/helper.css" rel="stylesheet">
      <link href="../newui/css/style-responsive.css" rel="stylesheet" />
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
            
            return ("ProbSelA=" + encodeURIComponent(selA) + "&ProbSelB=" + encodeURIComponent(selB) + 
                    "&ProbSelC=" + encodeURIComponent(selC) + "&ProbSelD=" + encodeURIComponent(selD) + 
                    "&ProbSelE=" + encodeURIComponent(selE) + "&ProbSelF=" + encodeURIComponent(selF) +
                    "&ProbSelG=" + encodeURIComponent(selG) + "&ProbSelH=" + encodeURIComponent(selH));
         }
   
         //***Step12 修改页面点击保存按钮出发Ajax动作
         function modifyQTContent(QTId)
         {
            QTDesc = document.getElementsByName("QTDescModify")[0].value.trim();
            QTName = document.getElementsByName("QTNameModify")[0].value.trim();
            
            if (QTDesc.length == 0)
            {
               alert("问卷模板说明不可为空白！");
               return;
            }
            
            str = "cmd=update&QTId=" + QTId;
            url_str = "QuestionTemplates_modify.php?";
         
            $.ajax
            ({
               beforeSend: function()
               {
                  //alert(url_str + str);
               },
               // should be POST
               type: "POST",
               url: url_str + str,
               data:{
                  QTDesc:QTDesc,
                  QTName:QTName
               },
               cache: false,
               dataType: 'json',
               success: function(res)
               {
                  res = String(res);
                  if (res.match(/^-\d+$/))  //failed
                  {
                     alert(MSG_OPEN_CONTENT_ERROR);
                  }
                  else  //success
                  {
                     alert("问卷模板修改成功，页面关闭后请自行刷新");
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
      <div class="" id="content">
      <!-- Header -->
         <header class="top-head container-fluid">
            <button type="button" class="navbar-toggle pull-left">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
            <!-- Left navbar -->
            <nav class=" navbar-default hidden-xs" role="navigation">
               <ul class="nav navbar-nav">
                  <li><a href="#"><?php echo date('Y-m-d',time()); ?></a></li>
               </ul>
            </nav>
            
            <!-- Right navbar -->
            <ul class="list-inline navbar-right top-menu top-right-menu">
               <!-- user login dropdown start-->
               <li class="dropdown text-center">
   				   <input type="hidden" id="userid" value="<?php echo $user_id ?>" />
   				   <form name=logoutform action=logout.php>
   				   </form>
                  <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                      <i class="fa fa-user"></i>
                      <span class="username"><?php echo $login_name ?> </span> <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu extended pro-menu fadeInUp animated" tabindex="5003" style="overflow: hidden; outline: none; display:none;">
                      <li><a href="javascript:void(0)" OnClick="click_logout();"><i class="fa fa-sign-out"></i> 退出</a></li>
                  </ul>
               </li>
               <!-- user login dropdown end -->       
            </ul>
            <!-- End right navbar -->
         </header>
         <!-- Header Ends -->
         <!-- Page Content Start -->
         <!-- ================== -->
         <div class="wraper container-fluid">
            <div class="page-title"> 
               <h3 class="title"><?php echo $TitleStr; ?></h3> 
            </div>
            <!-- Basic Form Wizard -->
            <div class="row">
               <div class="col-md-12">
                  <div class="panel panel-default">
                     <div class="panel-body"> 
                        <table class="searchField" border="0" cellspacing="0" cellpadding="0">
                           <div id="problem_overview">
                              <tr>
                                 <th>问卷模板名称：</th>
                                 <td><Input type=text name="QTNameModify" size=100 disabled="disabled" value="<?php echo $QTName ?>"></td>
                              </tr>
                              <tr>
                                 <th>问卷模板说明：</th>
                                 <td><textarea name="QTDescModify" rows=3 cols=100><?php echo $QTDesc;?></textarea>
                                 </td>
                              </tr>
                              <div class="probem_submit">
                              <?php
                                 if ($QTStatus != 1)
                                 {?>         
                                    <tr>
                                       <th colspan="4" class="submitBtns">
                                          <a class="btn_submit_new modifyProbsContent"><input name="modifyProbsButton" type="button" value="保存" OnClick="modifyQTContent(<?php echo $QTId;?>)"></a>
                                       </th>
                                    </tr>      
                              <?php
                                 }?>
                              </div>
                           </div>
                        </table>
                        <table class="searchQD" border="1" cellspacing="0" cellpadding="0">
                           <thead>
                              <tr>
                                 <th>编号</th>
                                 <th>问题类型</th>
                                 <th>问题描述</th>
                                 <th>问题分组</th>
                                 <th>问题选型A</th>
                                 <th>问题选型B</th>
                                 <th>问题选型C</th>
                                 <th>问题选型D</th>
                                 <th>问题选型E</th>
                                 <th>问题选型F</th>
                                 <th>问题选型G</th>
                                 <th>问题选型H</th>
                                 <th>问题选型I</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php
                                 $i = 1;
                                 foreach ($QDs as $qd) {
                              ?>
                              <tr>
                                 <td><?php echo $i++ ?></td>
                                 <td>
                                 <?php
                                    if ($qd->PROBLEMTYPE == TRUE_FALSE_QT) 
                                    {
                                       echo TRUE_FALSE_CHINESE;
                                    }
                                    else if ($qd->PROBLEMTYPE == SINGLE_CHOICE_QT)
                                    {
                                       echo SINGLE_CHOICE_CHINESE;
                                    }
                                    else if ($qd->PROBLEMTYPE == MULTI_CHOICE_QT)
                                    {
                                       echo MULTI_CHOICE_CHINESE;
                                    }
                                    else if ($qd->PROBLEMTYPE == FILL_VACANT_POSITION_QT)
                                    {
                                       echo FILL_VACANT_POSITION;
                                    }
                                    else if ($qd->PROBLEMTYPE == FAQ_QT)
                                    {
                                       echo FAQ;
                                    }
                                 ?>
                                 </td>
                                 <td><?php echo $qd->PROBLEMDESC ?></td>
                                 <td><?php echo $qd->GROUPNAME ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTA ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTB ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTC ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTD ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTE ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTF ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTG ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTH ?></td>
                                 <td><?php echo $qd->PROBLEMSELECTI ?></td>
                              </tr> 
                              <?php
                                 }
                              ?>                             
                           </tbody>
                        </table>
							</div>  <!-- End panel-body -->
                  </div> <!-- End panel -->
               </div> <!-- end col -->
            </div> <!-- End row -->
         </div>
         <!-- Page Content Ends -->
         <!-- ================== -->
         <!-- Footer Start -->
         <footer class="footer">
             2015 © Takeda.
         </footer>
         <!-- Footer Ends -->
      </div>
      <!-- Main Content Ends -->
      <script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
      <script type="text/javascript" src="../lib/jquery.min.js"></script>
      <script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
      <script type="text/javascript" src="../js/OSC_layout.js"></script>
   </body>
</html>
<!--Step15 新增修改页面    结束 -->