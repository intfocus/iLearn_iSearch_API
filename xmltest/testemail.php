<?php
   //引入发送邮件类
   require("smtp.php"); 
   class EmailSmtp{
      function eSmtp($email){
         //使用163邮箱服务器
         $smtpserver = "smtp.126.com";
         //163邮箱服务器端口 
         $smtpserverport = 25;
         //你的163服务器邮箱账号
         $smtpusermail = "yy_lfy@126.com";
         //收件人邮箱
         $smtpemailto = $email;//"yy_lfy@126.com";
         //你的邮箱账号(去掉@163.com)
         $smtpuser = "yy_lfy";//SMTP服务器的用户帐号 
         //你的邮箱密码
         $smtppass = "yueyi@810718"; //SMTP服务器的用户密码 
         //邮件主题 
         $mailsubject = "测试邮件发送";
         //$mailsubject = '=?UTF-8?B?'.base64_encode($mailsubject).'?=';
         //邮件内容 
         $mailbody = "<p>PHP+MySQL测试邮件发送</p><p><a href=\"http://baidu.com\">baidu</a>";
         //邮件格式（HTML/TXT）,TXT为文本邮件 
         $mailtype = "HTML";
         //这里面的一个true是表示使用身份验证,否则不使用身份验证. 
         $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
         //是否显示发送的调试信息 
         $smtp->debug = true;
         //发送邮件
         $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
      }
   }

   $emailsmtp = new EmailSmtp();
   $emailsmtp->eSmtp("yy_lfy@126.com");
?>