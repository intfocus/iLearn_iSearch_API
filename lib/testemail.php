<?php
   //引入发送邮件类
   require("smtp.php"); 
   class EmailSmtp{
      function eSmtp($email,$username,$TrainingName){
         //使用163邮箱服务器
         $smtpserver = "smtp.163.com";
         //163邮箱服务器端口 
         $smtpserverport = 25;
         //你的163服务器邮箱账号
         $smtpusermail = "yy_lfy@163.com";
         //收件人邮箱
         $smtpemailto = $email;//"yy_lfy@126.com";
         //你的邮箱账号(去掉@163.com)
         $smtpuser = "yy_lfy";//SMTP服务器的用户帐号 
         //你的邮箱密码
         $smtppass = "yueyi@810718"; //SMTP服务器的用户密码 
         //邮件主题 
         $mailsubject = "报名审批通知信";
         //$mailsubject = '=?UTF-8?B?'.base64_encode($mailsubject).'?=';
         //邮件内容 <p>报名审核 报名人: " . $username . " 课程名称: " . $TrainingName . "</p><p><a href=\"http://tsa-china.takeda.com.cn/\">登录审核后台</a>
         $mailbody = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"><HTML xmlns=\"http://www.w3.org/1999/xhtml\"><HEAD></HEAD><BODY bgcolor=\"#FFFFFF\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" style=\"font-family:'Microsoft YaHei';\"><TABLE width=\"640\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#ffffff\" style=\"font-family:'Microsoft YaHei';\"><TBODY><TR><TD><TABLE width=\"800\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" height=\"40\"></TABLE></TD></TR><TR><TD><TABLE width=\"800\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\" border:1px solid #edecec; padding:0 20px;font-size:14px;color:#333333;\"><TBODY><TR><TD width=\"760\" height=\"56\" border=\"0\" align=\"left\" colspan=\"2\" style=\"font-family:'Microsoft YaHei'; font-size:16px;vertical-align:bottom;\">" . $email . "您好：</TD></TR><TR><TD width=\"760\" height=\"30\" border=\"0\" align=\"left\" colspan=\"2\">&nbsp;</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;font-family:'Microsoft YaHei';\">TSA系统收到 (" . $username . ")提交的参加 (" . $TrainingName . ")的申请，需要您审批。</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;\">&nbsp;</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;font-family:'Microsoft YaHei';\">	提醒：1. 需要您的Windows账号登陆后台审批。</br>在［报名审批］中点击［开始查询］,查看所有待审核的申请。</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;\">&nbsp;</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;font-family:'Microsoft YaHei';\">现在审批 <A target=\"_blank\" href=\"http://tsa-china.takeda.com.cn/\">登陆后台</A></TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;\">&nbsp;</TD></TR><TR><TD width=\"720\" height=\"32\" colspan=\"2\" style=\"padding-left:40px;\">&nbsp;</TD></TR><TR><TD width=\"720\" height=\"14\" colspan=\"2\" style=\"padding-bottom:16px; border-bottom:1px dashed #e5e5e5;font-family:'Microsoft YaHei';\">TSA 团队</TD></TR><TR><TD width=\"720\" height=\"14\" colspan=\"2\" style=\"padding:8px 0 28px;color:#999999; font-size:12px;font-family:'Microsoft YaHei';\">此为系统邮件请勿回复</TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></BODY></HTML>";
         //邮件格式（HTML/TXT）,TXT为文本邮件 
         $mailtype = "HTML";
         //这里面的一个true是表示使用身份验证,否则不使用身份验证. 
         $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
         //是否显示发送的调试信息 
         $smtp->debug = FALSE;
         //发送邮件
         $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
      }
   }
?>