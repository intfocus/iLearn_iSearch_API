<?php
///////////////////////////////////////////////
//deleteReportID.php
//
//1.get information of the report to be deleted
//2.set the status of report DELETED
//3.return string of the refreshed report table
// 
// #001 modified by Odie 2013/04/26
//  To support new feature: mutli-level admin
//     1. Add $_SESSION["loginLevel"] and $_SESSION["loginName"]
//        admin => 1
//        user  => 2
//     2. If user, restrict the departments he can see
///////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
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
   
   // #001, add checking $_SESSION["loginLevel"] and $_SESSION["loginName"]
   session_start();
   if (!session_is_registered("GUID") || !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "" || $_SESSION["loginLevel"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   $login_level = $_SESSION["loginLevel"];
   $login_name = $_SESSION["loginName"];
   session_write_close();
   
   header('Content-Type:text/html;charset=utf-8');
    
   //define
   define(REFRESH_REPORT_PATH, "$working_path/refreshReportPages.php");
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");                      //time zone
   define(PAGE_SIZE, 100);                                //page size
   define(EXTREME_TYPE_NUMBER, '7');                      //個資類型
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   //define(GUID, "'8f44a8ab_5c6c_6232_cd4f_642761007428'");
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(SYMBOL_ERROR_REPORT_ID, -5);
   
   //status
   define(AVAILABLE, 0);
   define(DELETED, -1);   
  
   //msg
   define(MSG_REPORT_1, "目前沒有任何報表，請點選&quot;<a>產生新的報表</a>&quot;");
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "delete_report"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   //check number
   function check_number($check_str)
   {
      //----- check number -----
      if(!is_numeric($check_str))
      {
        return SYMBOL_ERROR; 
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $reportID;               //要刪除的報表編號

   //query
   $link;
   $db_host; 
   $str_query;
   $connect_db;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   
   //page
   $page_default_no;        //預設頁數
   $page_size;              //每頁報表數
   $page_num;
   
   //report
   $rID;                    //報表編號
   $rNameW;                 //報表名稱
   $temp_time;
   $rTimeW;                 //產生日期
   $vHighW_file;            //極高風險-檔案
   $HighW_file;             //高風險-檔案
   $MediumW_file;           //中風險-檔案
   $LowW_file;              //低風險-檔案
   $totalW_file;            //檔案總數
   $vHighW_data;            //極高風險-個資
   $HighW_data;             //高風險-個資
   $MediumW_data;           //中風險-個資
   $LowW_data;              //低風險-個資
   $totalW_data;            //個資總數
   $rItemW;                 //掃描項目
   $rItemW_temp1;
   $rItemW_temp2;
   $rItemW_map = array("身分證", "手機號碼", "地址", "電子郵件地址", "信用卡號碼", "姓名", "市話號碼");  //掃描項目對應
   $rItemW_default = array(0, 0, 0, 0, 0, 0, 0);  //預設掃描項目
   $rItemW_default_temp;
   $temp_begin;
   $tRangeW_begin;          //產生區間-開始
   $temp_end;
   $tRangeW_end;            //產生區間-結束
   $cTotalW;                //電腦
   $i;
   $flag;
   
   //set time
   date_default_timezone_set(TIME_ZONE);  //set timezone
   $date_time = date("Y-m-d H:i:s");

   //1.get information of the report to be deleted  
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }

   if(($reportID = check_number($_GET["reportID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_REPORT_ID;
      return;
   }
    
   //----- query -----     
   $str_update = "
      update report
      set status = " . DELETED . ", last_modified_time = '" . $date_time .
      "' where GUID = '" . $GUID . "' and reportID = $reportID";      
   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }

   //2.set the status of report DELETED
   if (!mysqli_query($link, $str_update))  //delete failed
   { 
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;
      return;
   }              
   else
   {  
      //3.return string of the refreshed report table
      if(file_exists(REFRESH_REPORT_PATH))
      {
         include_once(REFRESH_REPORT_PATH);
      }
      else
      {
         sleep(DELAY_SEC);
         echo FILE_ERROR;
         return;
      }
      //$link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      $refresh_report_str = refreshReportPages($link, $GUID, $login_level, $login_name);

      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo $refresh_report_str;
   }                    

   return;
?>
