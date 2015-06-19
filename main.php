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
   // if (!session_is_registered("GUID"))
   // {
      // session_register("GUID");
   // }

   //////////////////////
   // Set session=empty, redirect to main.php
   //////////////////////
   $_SESSION["GUID"] = "";
   $cmd="";
   session_write_close();
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["cmd"])){
         $cmd = $_GET["cmd"];
         /* 20120522 Billy begin */
         if (strcmp($cmd , "err") != 0)
            $cmd = "";
         /* 20120522 Billy end */
      }
   }
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head>
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
            if (cmd == "err")
               document.getElementById("msg").style.display = "block";
         };
      </script>
   </head>
   <body>
      <table class="container" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <td>
               <table class="main" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                     <td>
                        <div class="leftPart">
                        <a href="<?php echo URL_PREFIX; ?>/main.php?cmd=login"><div class="logo"></div></a>
                           <div class="leftPart_TL"></div>
                           <div class="leftPart_TR"></div>
                           <div class="leftPart_BL"></div>
                           <div class="leftPart_BR"></div>
                        </div>
                     </td>
                     <td>
                        <table class="loginWrap" width="100%" border="0" cellspacing="0" cellpadding="0">
                           <tr>
                              <td class="TL"></td>
                              <td class="T"></td>
                              <td class="TR">
                                 <div class="relative">
                                 </div>
                              </td>
                           </tr>
                           <tr>
                              <td class="L"></td>
                              <td class="M" valign="middle">
                                 <table id="normal" class="loginContentWrap" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                       <td>
                                          <div class="listContent2">
                                             <div class="title"><strong style="font-size:13px;">欢迎使用<br>请输入账号密码进行登录</strong></div>
                                          </div>
                                          <form name=myForm method=POST action=login.php>
                                             <div class="listContent">
                                                <div class="title">账号 - Login Name :</div>
                                                <input type="text" name=login_name>
                                             </div>
                                             <div class="listContent">
                                                <div class="title">密码 - Password :</div>
                                                <input type="password" name=password>
                                             </div>
                                             <div class="listContent">
                                                <!-- <a href="<?php echo URL_PREFIX; ?>/password.html">忘记密码</a> -->
                                             </div>
                                             <a class="btn_submit_new relative" href="#">
                                                <input type="submit" value="登 入">
                                             <div id="msg" class="msg" style="display:none;">错误 -- 输入账号或密码错误，请重新输入</div>         
                                             </a>
                                          </form>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                              <td class="R"></td>
                           </tr>
                           <tr>
                              <td class="BL"></td>
                              <td class="B"><div class="copyrightWrap"><div class="copyright">Copyright © IntFocus Information Technology INC. All rights reserved</div></div></td>
                              <td class="BR"></td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
   </body>
</html>
