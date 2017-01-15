<?php
   //----- Define -----
   define("FILE_NAME", "./DB.conf"); //account file name
   define("DELAY_SEC", 3);                                       //delay reply
   define("FILE_ERROR", -3);
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;

      return;
   }
   define("URL_PREFIX", $webui_link);
   session_start();
   $_SESSION["GUID"] = "";
   $_SESSION["loginName"] = ""; //#001 Add
   $_SESSION["username"] = "";
   $_SESSION["login_name"] = "";
   $cmd = "";
   session_write_close();
   //header("Location: https://tsa-china.takeda.com.cn");
   //return;
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["cmd"])){
         $cmd = $_GET["cmd"];
         /* 20120522 Billy begin */
         //if (strcmp($cmd , "err") != 0)
         //   $cmd = "";
         /* 20120522 Billy end */
      }
   }
?>

<!DOCTYPE HTML>
<html lang="zh-CN">
   <head>
        <meta charset="utf-8">
      <meta http-equiv="Pragma" content="no-cache">
      <meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title></title>
      <link href="css/style.css" rel="stylesheet" type="text/css" media="all">
      <link type="image/x-icon" href="images/wutian.ico" rel="shortcut icon">
      <!--[if IE 6]> 
      <link href="css/style_ie6.css" rel="stylesheet" type="text/css" media="all">
      <![endif]-->
      <script type="text/JavaScript" src="js/demoOnly.js"></script>
      <script type="text/JavaScript" src="js/jquery-1.4.4.js"></script>
      <script type="text/javascript" src="openflashchart/js/swfobject.js"></script>
      <script type="text/javascript">
         window.onload = function()  //print flash
         {
            var cmd = "<?php echo $cmd ?>";
            if (cmd == "err") {
               document.getElementById("msg").style.display = "block";
			}
			else if(cmd == "account_not_exist") {
               var msg = document.getElementById("msg");
			   msg.style.display = "block";
			   msg.innerHTML = "帐号不存在或已下架";
			}
         };
      </script>
	  
        <!-- Bootstrap core CSS -->
        <link href="newui/css/bootstrap.min.css" rel="stylesheet">
        <link href="newui/css/bootstrap-reset.css" rel="stylesheet">

        <!--Animation css-->
        <link href="newui/css/animate.css" rel="stylesheet">

        <!--Icon-fonts css-->
        <link href="newui/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="newui/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

        <!--Morris Chart CSS -->
        <link rel="newui/stylesheet" href="assets/morris/morris.css">


        <!-- Custom styles for this template -->
        <link href="newui/css/style.css" rel="stylesheet">
        <link href="newui/css/helper.css" rel="stylesheet">
        <link href="newui/css/style-responsive.css" rel="stylesheet" />

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->

   </head>
   <body>
   
   
        <div class="wrapper-page animated fadeInDown">
            <div class="panel panel-color panel-primary">
                <div class="panel-heading"> 
                   <h3 class="text-center m-t-10"> 武田销售学院 </strong> </h3>
                </div> 

				<form name=myForm method=POST  class="form-horizontal m-t-40" action=login.php>
                                            
                    <div class="form-group "  style="display:none;">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" name=login_name placeholder="用户帐号">
                        </div>
                    </div>
                    <div class="form-group "  style="display:none;">
                        
                        <div class="col-xs-12">
                            <input class="form-control" type="password" name=password placeholder="密码">
                        </div>
                    </div>

                    <div class="form-group ">
                        <div class="col-xs-12">
						
								<div id="msg" class="msg" style="display:none;">错误 -- 请重新登录</div>    
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <div class="col-xs-12">
                               <!-- <input type="submit" class="btn btn-purple w-md" value="登录">    -->
                                <a href="https://tsa-china.takeda.com.cn/uat/saml/spapp/index.php?sso" class="btn btn-purple w-md" >  重新登录 </a>
                        </div>
                    </div>
                    <div class="form-group m-t-30"
                        <div class="text-right">
                            <a href="#">服务电话：400 882 2731</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>


        <!-- js placed at the end of the document so the pages load faster -->
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/pace.min.js"></script>
        <script src="js/wow.min.js"></script>
        <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
            

        <!--common script for all pages-->
        <script src="js/jquery.app.js"></script>

    
   </body>
</html>
