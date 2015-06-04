<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   define(FILE_ERROR, -3);
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
   define(URL_PREFIX, $webui_link);
   session_start();
   if (!session_is_registered("GUID"))
   {
      session_register("GUID");
   }
   if (!session_is_registered("GUID_ADM"))
   {
      session_register("GUID_ADM");
   }

   //////////////////////
   // Set session=empty, redirect to main.php
   //////////////////////
   $_SESSION["GUID"] = "";
   $_SESSION["GUID_ADM"] = "";
   session_write_close();
   $cmd = $_GET["cmd"];

   /* 20120522 Billy begin */
   if (strcmp($cmd , "err") != 0)
      $cmd = "";
   /* 20120522 Billy end */
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Openfind P-Marker</title>
<link href="css/style.css" rel="stylesheet" type="text/css" media="all">
<!--[if IE 6]> 
<link href="css/style_ie6.css" rel="stylesheet" type="text/css" media="all">
<![endif]-->
<script type="text/JavaScript" src="js/demoOnly.js"></script>
<script type="text/JavaScript" src="js/jquery-1.4.4.js"></script>
<script type="text/javascript" src="openflashchart/js/swfobject.js"></script>

<script type="text/javascript">
var MSG_NO_FLASH = "目前未安裝flash，產生報表可能會有問題";

var flashblock_flag = false;

function set_flashblock_flag(){
   flashblock_flag = true;
}

function setLoginType(type){
   document.getElementsByName("loginType")[0].value = type; 
}

window.onload = function()  //print flash
{
   var cmd = "<?php echo $cmd ?>";
   var playerVersion = swfobject.getFlashPlayerVersion();  //check version
   if (playerVersion.major == 0)
      alert(MSG_NO_FLASH);
   else if(flashblock_flag == true){
      alert("請停用 Flashblock，否則產生報表可能會有問題");
   }
   //alert(cmd);
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
                  <a href="<?php echo URL_PREFIX; ?>/index.html"><div class="logo"></div></a>
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
                                       <div class="title"><strong style="font-size:13px;">系統管理者登入頁面</strong></div>
                                    </div>
                                    <form name=myForm method=POST action=login_adm.php>
                                       <div class="listContent">
                                          <div class="title">系統管理者帳號 - Login Name :</div>
                                          <input type="text" name=login_name>
                                       </div>
                                       <div class="listContent">
                                          <div class="title">密碼 - Password :</div>
                                          <input type="password" name=password>
                                       </div>
<?php
                                       //只用一個 login button, 直接猜測是 system admin or user -->
                                       // <input type=hidden name=loginType value=1>
                                       //<a class="btn_submit_new relative" href="#">
                                       //   <input type="submit" value="管理者登入" OnClick="setLoginType(1);">
                                       //</a>
                                       //<a class="btn_submit_new relative" href="#">
                                       //   <input type="submit" value="使用者登入" OnClick="setLoginType(2);"><br>
?>
                                       <a class="btn_submit_new relative" href="#">
                                          <input type="submit" value="系統管理者登入">
                                       <div id="msg" class="msg_adm" style="display:none;">錯誤 -- 輸入帳號或密碼錯誤，請重新輸入</div>         
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
                        <td class="B"><div class="copyrightWrap"><div class="copyright">Copyright © Openfind Information Technology INC. All rights reserved</div></div></td>
                        <td class="BR"></td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </td>
   </tr>
</table>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" height="0" width="0"> 
   <param name="movie" value="PMark.swf"> 
   <param name="allowScriptAccess" value="always">
   <embed type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer" allowScriptAccess="always"></embed> 
</object>
<IMG SRC="chrome://flashblock/skin/flash-on-24.png" onload="set_flashblock_flag();" style="visibility:hidden">
</body>
</html>
