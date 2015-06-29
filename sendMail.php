<?php
/********************************************
 *sendMail.php
 *1. Check session and reportID
 *2. Get information(GUID) from DB by reportID
 *   2.1 if not found => delay and return 404
 *3. Check seeion guid and report's guid
 *   3.1 if not match => delay and return 404
 *4. send report
 *   2012/03/15 Dylan Hsieh
 ********************************************/
?>
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
      header("HTTP/1.1 404 Not Found");
      
      return;
   }
   define(REPORT_PATH, $report_path);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(MAIL_MODULE, "/usr/local/www/apache22/web/mail.php");
   define(REPORT_LETTER, "$working_path/report_letter.html");
   define(WEBUI_LINK, $webui_link);   
   define(MESSAGE_SUCCESS, "寄送成功!");
   define(MESSAGE_ERROR, "寄送失敗!");   
   //----- Return value -----
   define(SYMBOL_ERROR, -1);
   
   //----- Check number -----
   function check_number($check_str)
   {
      //----- check number -----
      if(!is_numeric($check_str))
      {

        return SYMBOL_ERROR; 
      }
   
      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   //get from client
   $guid;
   $reportID;
   $reportGuid;
   $fileFolder;
   $fileName;
   
   $str_query;
   $result;
   $row;

   //-----------------------------------------
   //----- 1. Check session and reportID -----
   //-----------------------------------------
   
   header('Content-Type:text/html;charset=utf-8');    
   //----- session check -----
   session_start();
   if(!session_is_registered("GUID"))  //check session
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      exit();
   }
   if($_SESSION["GUID"] == "")
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      exit();
   }
   $guid = $_SESSION["GUID"];
   session_write_close();
   
   $reciever = $_POST["reciever"]; 
   $title = $_POST["title"];
   $content = $_POST["content"];
   $reportID = $_POST["reportID"];
   $title = trim($title);
   if($title == "")
   {
      $title = "Openfind P-Marker 個資盤點報表";
   }
   else
   {
      $title = "Openfind P-Marker 個資盤點報表($title)";
   }
   //----- check reportID -----
   if(check_number($reportID) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC); 
      echo MESSAGE_ERROR;
      exit();
   }
 
   //-----------------------------------------
   //----- 2. Check information from DB  -----
   //-----------------------------------------
    
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      
      return;
   }

   //----- query report information -----
   $str_query = "
      select *
      from report
      where reportID = $reportID
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //have no matching report id
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         sleep(DELAY_SEC);
         echo MESSAGE_ERROR;
      
         return;
      }
      $row = mysqli_fetch_assoc($result);   
      $reportGuid = $row["GUID"];
      $fileFolder = $row["fileFolder"];
      $fileName = $row["fileName"];
      mysqli_free_result($result);
      unset($row);
   }
   else
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      
      return;
   }
   //----- query customer information -----
   $str_query = "
      select *
      from customer
      where GUID = '$guid'
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //have no matching report id
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         sleep(DELAY_SEC);
         echo MESSAGE_ERROR;
      
         return;
      }
      $row = mysqli_fetch_assoc($result);   
      $name = $row["name"];
      mysqli_free_result($result);
      unset($row);
   }
   else
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      
      return;
   }   
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }
    
   //--------------------------------------------------
   //----- 3. Check seeion guid and report's guid -----
   //--------------------------------------------------
    
   if(strcmp($guid, $reportGuid))
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;
      
      return;
   } 

   //-----------------------------------------
   //----- 4 send an e-mail to $receiver
   //        refer password_letter.html to construct a *.eml 
   //        put the file in the mailerd folder 
   //----------------------------------------- 
   
   //----- Read mail module -----
   if(file_exists(MAIL_MODULE))
   {
      include_once(MAIL_MODULE); 
   }
   else
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;

      return;
   }
   //----- Read report letter -----
   if(file_exists(REPORT_LETTER))
   {
      if(!@($str_report_letter = file_get_contents(REPORT_LETTER)))
      {
         sleep(DELAY_SEC);
         echo MESSAGE_ERROR;

         return;
      }
   }
   else
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;

      return;
   }
   //----- Replace letter information -----
 
   $str_report_letter = str_replace("\$link_prefix", $webui_link, $str_report_letter);
   $str_report_letter = str_replace("\$name", $name, $str_report_letter);   
   $str_report_letter = str_replace("\$reciever", $reciever, $str_report_letter);   
   $str_report_letter = str_replace("\$content", $content, $str_report_letter);      
   $params["from_title"] = "Openfind P-Marker 系統通知";
   $params["from_mail"] = "p-marker@openfind.com.tw";
   $params["mail_list"] = explode(",", $reciever);
   $params["subject"] = $title;
   $params["msg_body"] = $str_report_letter;
   $params["file_path"] = REPORT_PATH ."/$guid/$fileFolder/$fileName.zip";

   //----- send mail -----
   if((int)mail_func($params) < 0)
   {
      sleep(DELAY_SEC);
      echo MESSAGE_ERROR;

      return;
   }
   //header("Location:showHtml.php?reportID=$reportID");
  
   echo MESSAGE_SUCCESS;
   exit();
?>

