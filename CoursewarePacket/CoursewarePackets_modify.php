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
   $db_host ;
   $connect_db;
   $admin_account;
   $admin_password;
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
   if(($PPTId = check_number($_GET["pptId"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   //link    
   // $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
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
         $str_query1 = "select * from coursewares where Status=1 and CoursewareId=$cl";
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
   
 
   //----- query -----
   //***Step14 如果cmd为读取通过ID获取要修改内容信息，如果cmd不为读取并且ID为零为新增动作，如果不为读取和新增则为修改动作
   if ($cmd == "read") // Load
   {
      $str_query1 = "Select * from ppts where Status = 1 and PPTId=$PPTId";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $PPTId = $row["PPTId"];
            $PPTName = $row["PPTName"];
            $CoursewareList = $row["CoursewareList"];
            $PPTDesc = $row["PPTDesc"];
            $Status = $row["Status"];
            $StatusStr = $row["Status"] == 0 ? "下架" : "上架";
            $EditTime = $row["EditTime"] == null ? '' : date("Y/m/d",strtotime($row["EditTime"]));
            $TitleStr = "课件包修改";
            if ($Status == 1)
               $TitleStr = "课件包查看 (上架状态无法修改)";
            $cplist = CWList($CoursewareList);
         }
         else
         {
            $PPTId = 0;
            $PPTName = "";
            $CoursewareList = "";
            $PPTDesc = "";
            $EditTime = "";
            $TitleStr = "课件包新增";
            $Status = 0;
            $cplist = "";
         }
      }
   }
   else if ($PPTId == 0) // Insert
   {
      $PPTName = $_POST["PPTName"];
      $CoursewareList = $_POST["CoursewareList"];
      $PPTDesc = $_POST["PPTDesc"];
      $str_query1 = "Insert into ppts (PPTName,CoursewareList,PPTDesc,Status,CreatedUser,CreatedTime,EditUser,EditTime)" 
                  . " VALUES('$PPTName','$CoursewareList','$PPTDesc',1,$user_id,now(),$user_id,now())" ;
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
      $PPTName = $_POST["PPTName"];
      $CoursewareList = $_POST["CoursewareList"];
      $PPTDesc = $_POST["PPTDesc"];
      
      $str_query1 = "Update ppts set PPTName='$PPTName', CoursewareList='$CoursewareList', PPTDesc='$PPTDesc', EditUser=$user_id, EditTime=now() where PPTId=$PPTId";
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
  
  
   
   $checkbox_prefix = "__chk";
   $checkbox_prefix_len = strlen($checkbox_prefix);
  
      

   if ($_SERVER['REQUEST_METHOD'] === 'POST')  
   {
	  $result_string = $idstr;
	 
	  $ppts_condition = "coursewareList";  
	  $ppts_table_name = "ppts";
	  $query = "UPDATE `".$ppts_table_name."` SET `CoursewareList`='".$result_string."' WHERE ".$ppts_condition;
	  if(mysqli_query($link, $query))
	  {
		 $err = "成功.";
	  }
	  else
	  {
	     $err = "失敗";
	  }
	 
	  echo $err;
	  return;
   }
   else
   {
	
      // $display_list = get_courseware_to_display();
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
      <!--Animation css-->
      <link href="css/animate.css" rel="stylesheet">
      <!--Icon-fonts css-->
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/ionicon/css/ionicons.min.css" rel="stylesheet" />
      <!-- DataTables -->
      <link href="assets/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
      <!--Form Wizard-->
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

<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/OSC_layout.js"></script>
<link rel="stylesheet" type="text/css" href="../css/demo.css">

<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../lib/jquery.easyui.min.js"></script>
<link type="text/css" href="../lib/jQueryDatePicker/jquery-ui.custom.css" rel="stylesheet" />
<style>
.searchField{width:960px;}
</style>
<script type="text/javascript" src="../lib/jquery-ui.min.js"></script>
      <!-- End of tree view -->
      <!--[if lt IE 10]>
      <script type="text/javascript" src="lib/PIE.js"></script>
      <![endif]-->
      <title>武田 - 课件包页面</title>
      <Script Language=JavaScript>
         $(document).on("click", "#addshow", function(event) {
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
            $("input[name='CoursewareNameModify']").val(cnm);
         });
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
            $("input[name='CoursewareNameModify']").val(cnm);
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

         //***Step12 修改页面点击保存按钮出发Ajax动作
         function modifyPPTsContent(PPTId)
         {
			cnm();
            PPTName = document.getElementsByName("PPTNameModify")[0].value.trim();
            PPTDesc = document.getElementsByName("PPTDescModify")[0].value.trim();
            CoursewareList = document.getElementsByName("CoursewareNameModify")[0].value.trim();
            
            if (PPTName.length == 0 || PPTDesc.length == 0)
            {
               alert("课件包名称及课件包备注不可为空白");
               return;
            }
            
            if (PPTName.length > 100 || PPTDesc.length > 255){
               alert("课件包名称及课件包备注长度过长！请缩短后重新保存。");
               return;
            }
            
            
            url_str = "CoursewarePackets_modify.php?cmd=write&pptId=" + PPTId;
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
                  PPTName:PPTName,
                  PPTDesc:PPTDesc,
                  CoursewareList:CoursewareList
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
         
         function test(cpid,cpname){
            
            alert(cpid);
            alert(cpname);
         }


         // $(function() {
             // $( "#sortable" ).sortable();
             // $( "#sortable" ).disableSelection();
         // 
             // $("#console-log").append("<span>NO</span>");
         //     
             // $(document).on("change",'#datatable input[type="checkbox"]', function(){
                 // var text = this.parentNode.textContent;
                 // if(this.checked) {
                     // $("#sortable").append("<li class=\"ui-state-default\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span>" + text + "</li>");
                 // }
                 // else {
                     // var deleted = $("#sortable li");
                     // for(var i in deleted) {
                         // var deletedText = deleted[i].textContent;
                         // if(deletedText === text) {
                             // deleted[i].remove();
                         // }
                     // }
                 // }
         //         
             // });
         //       
         // });
         
         
         				// var idstr = "";
         				// $("#sortable li").each(function() {
         				// idstr += $(this).attr("id") + ",";							
         				// });
         				// if (idstr.length > 0) {							
         				// idstr = idstr.substring(0, idstr.length - 1)						
         				// }														
      </Script>
      <!--Step15 新增修改页面    起始 -->
   </head>
   <body Onload="loaded();" style="padding-top:62px!important;background: rgb(255, 255, 255);">
      <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand hidden-sm" href="/uat/index.php" onclick="_hmt.push(['_trackEvent', 'navbar', 'click', 'navbar-首页'])">武田学习与工作辅助平台</a>
        </div>
        <div class="navbar-collapse collapse" role="navigation">
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown text-center">
                    	
								   <form name="logoutform" action="logout.php">
								   </form>
				<a class="dropdown-toggle" href="#" aria-expanded="false">
					<i class="fa fa-user"></i>
					<span class="username">使用者 : <?php echo $login_name ?> </span> <!--<span class="caret"></span>-->
				</a>
			</li>
			</ul>
        </div>
      </div>
    </div>
	<div class="container">
	<ol class="breadcrumb">
	  <li class="active">后台功能名称</li>
	  <li class="active"><?php echo $TitleStr; ?></li>
	</ol>
      <div id="content">
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2">
					<form class="cmxform form-horizontal tasi-form searchField">
						<div class="form-group ">
							<label for="cname" class="control-label col-lg-2">课件包名称：</label>
							<div class="col-lg-7">
								<input class=" form-control" name="PPTNameModify" type="text" value="<?php echo $PPTName;?>">
							</div>
						</div>
						<div class="form-group ">
							<label for="cname" class="control-label col-lg-2">课件包备注：</label>
							<div class="col-lg-7">
								<textarea class=" form-control" style="height:150px;" name="PPTDescModify"><?php echo $PPTDesc;?></textarea>
							</div>
						</div>
						<input type="hidden" name="CoursewareNameModify" value="<?php echo $CoursewareList;?>">
					</form>
				</td>
			</tr>
            <!--<tr>
               <th>课件包名称：</th>
               <td><Input type="text" name="PPTNameModify" size=50 value="<?php echo $PPTName;?>"></td>
            </tr>
            <tr>
               <th>课件包备注：</th>
               <td><Textarea name="PPTDescModify" rows=30 cols=100><?php echo $PPTDesc;?></textarea></td>
            </tr>
      	  <tr>
               <th>课件名称：</th>
               <td><Input type=text name="CoursewareNameModify" size=50 value="<?php echo $CoursewareList;?>"></td>
            </tr>-->
         </table>
      </div>
      <!--Main Content Start -->
      <section class='example'>
         <div class='gridly'>
         </div>
         <p class='actions'>
            
            <!-- <input type="button" id="addshow" class='button' value="Show" /> -->
         </p>
      </section>
      <section class="content">
         <!-- Page Content Start -->
         <!-- ================== -->
         <div class="wraper container-fluid">
            <div class="page-title"> 
               <h3 class="title">选择课件</h3> 
            </div>
            <div class="row">
               <div class="col-md-12">
                  <div class="panel panel-default">
                     <div class="panel-heading">
                        <h3 class="panel-title"></h3>
                        <a class="add button btn btn-success" href="#" style="padding:10px 0; width:100px;">Add</a>
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
                                       $str_query1 = "SELECT CoursewareId,CoursewareName,CoursewareDesc,PAList,ProductList FROM coursewares where Status = 1";
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
			<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
			   <table style="width:100%; margin:20px 0;">
					<tbody>
						<tr>
							<td colspan="3" align="right">
									<?php
					 
					if ($Status != 1)
					 {
				  ?>    
									<a class="btn_submit_new modifyPPTsContent"><input class="btn btn-success" name="modifyPPTsButton" type="button" value="保存" OnClick="modifyPPTsContent(<?php echo $PPTId;?>)"></a>
			 <?php
					 }
				  ?>   
							</td>
						</tr>
					 </tbody>
				 </table>
				 </div>
				 </div>
         </div>
         <!-- Page Content Ends -->
         <!-- ================== -->
      </section>
	  </div>
	  </div>
      <!-- Main Content Ends -->
      <!-- js placed at the end of the document so the pages load faster -->
      <!--Form Wizard-->
      <script src="js/bootstrap.min.js"></script>
      <script src="js/pace.min.js"></script>
      <script src="js/wow.min.js"></script>
      <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
      <script src="js/jquery.app.js"></script>
      <script src="assets/datatables/jquery.dataTables.min.js"></script>
      <script src="assets/datatables/dataTables.bootstrap.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
             $('#datatable').dataTable();
         } );
      </script>
   </body>
</html>
<!--Step15 新增修改页面    结束 -->