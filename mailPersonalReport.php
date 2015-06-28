<?php
/********************************************
 *mailPersonalReport.php
 *1. Check session and reportID
 *2. Get information(GUID,fileFolder) from DB by reportID
 *   2.1 if not found => delay and return 404
 *3. Check seeion guid and report's guid
 *   3.1 if not match => delay and return 404
 *4. Unzip *.zip to todo.../GUID/fileFolder/personal 
 *5. Find sheet1.tsv, skip the first row, get domain/hostname
 *      id<tab>domain/hostname<tab>...
 *6. With GUID+domain+hostname, get employee_email from computer_list
 *      if not found, get contact_email from customer
 *7. Open sheet2.tsv, for each different domain/hostname
 *      create P-Marker_xxx(todo).csv
 *      save "filepath","filename","risk_level",total,id,mobile,address,email,ccard,name,tel
 *      If the end of a domain/hostname => send out the personal mail
 *8. Log if encounter any format error
 *
 *   2012/11/19 Jeffrey,Phantom 
 *
 * #001  2013/12/11  Phantom     strtolowe. change e-mail addresses to lower case.
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
      echo __LINE__;
      
      return;
   }
   define(REPORT_PATH, $report_path);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(MAIL_MODULE, "/usr/local/www/apache22/web/mailPersonalReport2.php");
   define(DISPLAY_TEMPLATE, "/usr/local/www/apache22/data/mailPersonalReport.html");
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
   $customerName;
   $reportID;
   $reportGuid;
   $fileFolder;
   $fileName;
   
   $str_query;
   $result;
   $row;

   $str_zip;
   $report;   
   $str_temp;   
   //-----------------------------------------
   //----- 1. Check session and reportID -----
   //-----------------------------------------
   
   //----- session check -----
   session_start();
   if(!session_is_registered("GUID"))  //check session
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if($_SESSION["GUID"] == "")
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $guid = $_SESSION["GUID"];
   session_write_close();
   //----- check reportID -----
   if(($reportID = check_number($_GET["reportID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC); 
      header("Location:main.php");
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
      echo __LINE__;
      
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
         echo __LINE__;
      
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
      echo __LINE__;
      
      return;
   }
   
   //--------------------------------------------------
   //----- 3. Check seeion guid and report's guid -----
   //--------------------------------------------------
   
   if(strcmp($guid, $reportGuid))
   {
      sleep(DELAY_SEC);
      echo __LINE__;

      return;
   }

   //------------------------------
   //----- 4. Unzip *.zip to full_path/GUID/fileFolder/personal
   //------------------------------

   $report = REPORT_PATH . "/$reportGuid/$fileFolder/$fileName.zip";
   $report_path_temp = REPORT_PATH . "/$reportGuid/$fileFolder";
   if(file_exists($report))   //report exists
   {
      $unzip_cmd = "mkdir $report_path_temp/personal; cp $report $report_path_temp/personal/$fileName.zip; cd $report_path_temp/personal; /usr/local/bin/unzip -q $fileName.zip;" . 
                   "cp \"$report_path_temp/personal/P-Marker Report/sheet1.tsv\" $report_path_temp/personal/.;" .
                   "cp \"$report_path_temp/personal/P-Marker Report/sheet2.tsv\" $report_path_temp/personal/.;" .
                   "cp \"$report_path_temp/personal/P-Marker Report/pattern_list.tsv\" $report_path_temp/personal/.;" .
                   "rm -rf \"$report_path_temp/personal/P-Marker Report\"";
      system($unzip_cmd);
   }  
   else
   {
      sleep(DELAY_SEC);
      echo __LINE__;
      
      return;
   }  

   //------------------------------
   //----- 5. Find sheet1.tsv, skip the first row, get domain/hostname
   //-----       id<tab>domain/hostname<tab>..... 
   //------------------------------
   
   $sheet1 = $report_path_temp . "/personal/sheet1.tsv";
   $sheet2 = $report_path_temp . "/personal/sheet2.tsv";
   $pattern_list = $report_path_temp . "/personal/pattern_list.tsv";
   if(!file_exists($sheet1) || !file_exists($sheet2)) 
   {
      sleep(DELAY_SEC);
      echo __LINE__;
      
      return;
   }

   //-----------------------------
   //----- Get contact_email
   //-----------------------------
   $str_query = "
      select contact_email,name from customer
         where GUID = '$guid'";
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
         echo __LINE__;
      
         return;
      }
      $row = mysqli_fetch_assoc($result);   
      $contact_email = $row["contact_email"];
      $customerName = iconv("UTF-8","big5",$row["name"]);
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
      echo __LINE__;
      
      return;
   }

   //-----------------------------
   //----- Get all employee_email(s) from computer_list with GUID=$guid 
   //-----------------------------
   $str_query = "
      select domain_name,hostname,employee_email from computerList
         where GUID = '$guid'";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //have no matching report id
      {
         $computer_name = "OPENFIND_OPENFIND";
         $computerList[$computer_name] = "nosuchuser@openfind.com.tw";
         /*
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         sleep(DELAY_SEC);
         echo __LINE__;
      
         return;
          */
      }
      else
      {
         //$row_number = mysqli_num_rows($result);
         while ($row = mysqli_fetch_assoc($result))
         {
            $computer_name = $row["domain_name"] . "_" . $row["hostname"];
            $computerList[$computer_name] = $row["employee_email"];
         }
      }
      mysqli_free_result($result);
   }
   else
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo __LINE__;

      return;
   }
      
   //----- Read mail module -----
   if(file_exists(MAIL_MODULE))
   {
      include_once(MAIL_MODULE); 
   }
   else
   {
      sleep(DELAY_SEC);
      echo __LINE__;

      return;
   }

   $sheet1_fp = fopen($sheet1,"r");
   fgets($sheet1_fp); // skip the first row

   while(!feof($sheet1_fp))
   {
      //------------------------------
      //----- 6. With GUID+domain+hostname, get employee_email from computer_list
      //-----        if not found, get employee_email from identityFound
      //-----        if employee_email='employee@com.tw'(the default value), use contact_email instead 
      //------------------------------
      $buf1 = fgets($sheet1_fp);
      //echo $buf1 . "<BR>";
      $arr1 = explode("\t",$buf1);
      $arr13 = explode("/",$arr1[3]); // split domain/hostname from arr1[3], arr13[0] = domain, arr13[1] = hostname
      $arr1[3] = str_replace("/","_",$arr1[3]);
      $sheet1_endtime[$arr1[3]] = $arr1[18];
      if (!isset($computerList[$arr1[3]]))
      {
         $employee_email = "employee@com.tw";
         $str_query = "
            select employee_email from identityFound where GUID='$guid' AND domain_name='$arr13[0]' AND hostname='$arr13[1]' order by XMLID DESC";
         //echo $str_query . "<BR>";
         if($result = mysqli_query($link, $str_query))
         {
            if(mysqli_num_rows($result))    //have no matching report id
            {
               if ($row = mysqli_fetch_assoc($result))
               {
                  $employee_email = $row["employee_email"];
               }
            }   
            mysqli_free_result($result);
         }
         if (strcmp($employee_email,"employee@com.tw") == 0)
            $computerList[$arr1[3]] = $contact_email;
         else
            $computerList[$arr1[3]] = $employee_email;
      }
      $reportSheet1List[$arr1[3]] = 0; //Add each hostname in sheet1 to the list, 20121227 By Phantom
   }
   fclose($sheet1_fp);

   $sheet2_fp = fopen($sheet2,"r");
   $pattern_list_fp = fopen($pattern_list,"r");
   $old_domain_hostname = "";
   $display_content = "";
   $reportUserCount = 0;
   $reportUserCount2 = 0;
   $sn = 0;

   //------------------------------
   //----- 7. Open sheet2.tsv, for each different domain/hostname 
   //-----       create P-Marker_domain_hostname.csv
   //-----       save "filepath","filename","risk_level",total,id.mobile,address,email,ccard,name,tel\n
   //-----       v2 for MOEA, 序號,部門/單位,員工姓名,電腦名稱,檔案名稱,檔案路徑,\"個資類別\n(括號內數字為該類別數量)\",\"個資內容\n(每項個資類別僅顯示五筆)\",個資總數量\n
   //-----       If meet the end of a domain_hostname => send out the personal mail 
   //------------------------------
   while(!feof($sheet2_fp))
   {
      $buf1 = fgets($sheet2_fp);
      $bufPattern = fgets($pattern_list_fp);
      $arr2 = explode("\t",$buf1);
      $dept = $arr2[1];
      $username = $arr2[2];
      $domain_hostname = str_replace("/","_",$arr2[3]);
      $domain_hostname_origin = $arr2[3];
      $filename = $arr2[5];
      $filepath = $arr2[6];
      $ext = $arr2[7];
      $risk_level = $arr2[8];
      $pattern = $arr2[9];
      $total = (int)$arr2[10];

      $pat_array = explode(",",$pattern);
      $nCount = 0;
      $pat_name_number = 0;
      $pat_tel_number = 0;
      $pat_email_number = 0;
      $pat_address_number = 0;
      $pat_ccard_number = 0;
      $pat_id_number = 0;
      $pat_mobile_number = 0;
      $sn ++;

      while (isset($pat_array[$nCount]))
      {
         $pat_name_pos = strpos($pat_array[$nCount],"(");
         $pat_name_str = $pat_array[$nCount];
         $pat_name = substr_replace($pat_name_str,"",$pat_name_pos);
         $pat_value_str = strchr($pat_array[$nCount],"(");
         sscanf($pat_value_str,"(%d)",$pat_value);
         //echo "pat_name=[$pat_name],pat_value=[$pat_value]<BR>";
         if (strcmp($pat_name,"�m�W") == 0)
            $pat_name_number = $pat_value;
         else if (strcmp($pat_name,"���ܸ��X") == 0)
            $pat_tel_number = $pat_value;
         else if (strcmp($pat_name,"�q�l�l��a�}") == 0)
            $pat_email_number = $pat_value;
         else if (strcmp($pat_name,"�a�}") == 0)
            $pat_address_number = $pat_value;
         else if (strcmp($pat_name,"�H�Υd���X") == 0)
            $pat_ccard_number = $pat_value;
         else if (strcmp($pat_name,"������") == 0)
            $pat_id_number = $pat_value;
         else if (strcmp($pat_name,"������X") == 0)
            $pat_mobile_number = $pat_value;
         else if ($pat_name != "")
            echo __LINE__ . " pat_name=[$pat_name]<BR>";
         $nCount ++;
      }
      

      if ($old_domain_hostname == "")
      {
         $old_domain_hostname = $domain_hostname;
         $fp = fopen("$report_path_temp/personal/P-Marker_$domain_hostname.csv","w");
         if ($fp == NULL)
         {
            //echo "$report_path_temp/personal/P-Marker_$domain_hostname.csv<BR>";
            echo __LINE__;
            return;
         }
         /* Begin of old report format */
         //fprintf($fp,"\"路徑\",\"檔名\",\"風險\",\"個資總數\",\"身分證\",\"手機\",\"地址\",\"信箱\",\"信用卡\",\"姓名\",\"市話\"\n");
         //fprintf($fp,"���|,�ɦW,���I,�Ӹ��`��,������,���,�a�},�H�c,�H�Υd,�m�W,����\n");
         //fprintf($fp,"\"%s\",\"%s\",\"%s\",%d,%d,%d,%d,%d,%d,%d,%d\n",$filepath,$filename,$risk_level,$total,$pat_id_number,$pat_mobile_number,$pat_address_number,$pat_email_number,$pat_ccard_number,$pat_name_number,$pat_tel_number);
         /* End of old report format */

         //fprintf($fp,"$customerName - 個人電腦中疑似含有個人資料電子檔案掃描結果清單\n 資料日期：$endtime(掃描完成日期)\n");
         //fprintf($fp,"序號,部門/單位,員工姓名,電腦名稱,檔案名稱,檔案路徑,\"個資類別\n(括號內數字為該類別數量)\",\"個資內容\n(每項個資類別僅顯示五筆)\",個資總數量\n");
         fprintf($fp,"\"%s\" - �ӤH�q�����æ��t���ӤH��ƹq�l�ɮױ��y���G�M��\n ��Ƥ���G%s(���y�������)\n",$customerName,$sheet1_endtime[$domain_hostname]);
         fprintf($fp,"�Ǹ�,����/���,���u�m�W,�q���W��,�ɮצW��,�ɮ׸��|,\"�Ӹ����O\n(�A�����Ʀr�������O�ƶq)\",\"�Ӹꤺ�e\n(�C���Ӹ����O����ܤ���)\",�Ӹ��`�ƶq\n");
         fprintf($fp,"%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d\n",$sn,$dept,$username,$domain_hostname_origin,$filename,$filepath,$pattern,$bufPattern,$total);
      }
      //echo "1.000 $old_domain_hostname --- $domain_hostname<BR>";
      if (strcmp($old_domain_hostname,$domain_hostname) != 0) // a new computer begins, must send an mail for old data, and open a new csv file
      {
         fclose($fp);
         ////////////////////////
         // send an mail
         ////////////////////////
         $str_report_letter = "<html><head><meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">
            </head><body>檢送個人電腦[$old_domain_hostname]中疑似含有個人資料電子檔案掃描結果清單，如附件，請參考。
            <BR>請逕行決定「保留」或「刪除」該檔案，以降低個人資料外洩風險。
            <BR><HR><BR>
            因為系統功能限制報表僅能以 CSV 格式提供，若您需要將報表調整為更易閱讀的格式，<BR>
            請參考本頁說明，<A href=http://www.p-marker.com.tw/person_csv_format.html>若無法順利讀取，請點選本連結參考P-Marker網站說明。</A><BR>
            <BR>
            <I>(本說明以 Microsoft Office Excel 2010 說明調整格式之範例，您亦可用其它軟體開啟 CSV 檔案並自行調整格式)</I><BR>
            <BR>
            下載您所收到的信件當中附檔的 CSV 檔案後，請雙擊或自 Excel 軟體中開啟此檔案，在 Excel 當中可執行以下步驟：<BR>
            <BR>
            <B>1. 在圈選處拖曳滑鼠，選取 D 到 G 共 4 直欄(會呈現反白如下圖)</B><BR>
            <BR>
            <B>2. 再點選上方「自動換列」的按鈕</B><BR>
            <B>&nbsp;&nbsp;<A href=http://support.microsoft.com/kb/2473659/zh-tw>如果無法找到自動換列請參考此處說明</A><BR>
            <BR>
            <img src=\"http://www.p-marker.com.tw/images/pmarker_csv_arrange_01.png\"><BR>
            <BR>
            <B>3. 完成後，再用同樣方式重新選取 F 到 H 共 3 直欄，<BR>
            &nbsp;&nbsp;並於反白區按滑鼠右鍵，選擇「欄寬」<br>&nbsp; 並輸入「25」，如下圖<BR>
            <BR>
            <img src=\"http://www.p-marker.com.tw/images/pmarker_csv_arrange_02.png\"><BR>
            <BR>
            <B>4. 完成後，請點一下最左上角的空格，或按 Ctrl A 全選整個表格</B><BR>
            <BR>
            5. 在最左邊 1 與 2 之間 <br>&nbsp;&nbsp;
         滑鼠游標變成 如圖黑色十字型 的時候 <br>&nbsp;&nbsp;
         滑鼠連點 2 下，如下圖所示<BR>
            <BR>
            <img src=\"http://www.p-marker.com.tw/images/pmarker_csv_arrange_03.png\"><BR>
            <BR>
            <B>6. 此時報表應該已呈現比較方便閱讀的格式。您可以再加上所需的格式調整，<BR>
            最後請務必將檔案「另存新檔」為 Excel 活頁簿 (*.xlsx 或 *.xls)，以保留格式。<BR>
            之後需使用報表時，請使用另存之 Excel 檔案，即可保有以上調整過的格式。</B><BR>
            <BR>
            <img src=\"http://www.p-marker.com.tw/images/pmarker_csv_arrange_04.png\">
            </body></html>";
         $params["from_title"] = "個人資料電子檔案掃描結果"; 
         //$params["from_mail"] = $contact_email;
         $params["from_mail"] = "MAILER-DAEMON"; // empty from_mail means it is the returned mail, can skip the SPF and AUTH problem (supposedly). 2012/11/26 By Phantom
         if (strcmp($contact_email,$computerList[$old_domain_hostname]) == 0) //no need to cc to admin, since the recipient is admin already
            $params["mail_list"] = array(strtolower($computerList[$old_domain_hostname])); //#001
         else if (strcmp($contact_email,"") != 0) 
            $params["mail_list"] = array(strtolower($computerList[$old_domain_hostname]),strtolower($contact_email)); //#001
         else // contact_email is empty, (the bug of MAC), just send the mail to recipient.
            $params["mail_list"] = array(strtolower($computerList[$old_domain_hostname])); //#001
         $reportSheet1List[$old_domain_hostname] = 1; // mark the computer *.csv mail is sent, 2012/12/27 By Phantom
         $params["subject"] = "檢送個人電腦 [" . $old_domain_hostname . "] 中疑似含有個人資料電子檔案掃描結果清單，請參考";
         $params["msg_body"] = $str_report_letter;
         $params["file_path"] = "$report_path_temp/personal/P-Marker_$old_domain_hostname.csv";

         $display_content = $display_content . "從 [" . $params["from_mail"] . "] 寄送 P-Marker_$old_domain_hostname.csv 到 [" . $computerList[$old_domain_hostname] . "]<BR>";
         $reportUserCount = $reportUserCount + 1;
         //sleep(1);

         //----- send mail -----
         if((int)mail_func($params) < 0)
         {
            sleep(DELAY_SEC);
            echo __LINE__;

            return;
         }
          
         ////////////////////////
         // prepare for next mail
         ////////////////////////
         $fp = fopen("$report_path_temp/personal/P-Marker_$domain_hostname.csv","w");
         $old_domain_hostname = $domain_hostname;
         $sn = 1;

         /* Begin of old report format */
         //fprintf($fp,"\"路徑\",\"檔名\",\"風險\",\"個資總數\",\"身分證\",\"手機\",\"地址\",\"信箱\",\"信用卡\",\"姓名\",\"市話\"\n");
         //fprintf($fp,"���|,�ɦW,���I,�Ӹ��`��,������,���,�a�},�H�c,�H�Υd,�m�W,����\n");
         //fprintf($fp,"\"%s\",\"%s\",\"%s\",%d,%d,%d,%d,%d,%d,%d,%d\n",$filepath,$filename,$risk_level,$total,$pat_id_number,$pat_mobile_number,$pat_address_number,$pat_email_number,$pat_ccard_number,$pat_name_number,$pat_tel_number);
         /* End of old report format */

         //fprintf($fp,"$customerName - 個人電腦中疑似含有個人資料電子檔案掃描結果清單\n 資料日期：$endtime(掃描完成日期)\n");
         //fprintf($fp,"序號,部門/單位,員工姓名,電腦名稱,檔案名稱,檔案路徑,\"個資類別\n(括號內數字為該類別數量)\",\"個資內容\n(每項個資類別僅顯示五筆)\",個資總數量\n");
         //fprintf($fp "$customerName - �ӤH�q�����æ��t���ӤH��ƹq�l�ɮױ��y���G�M��\n ��Ƥ���G$sheet1_endtime[$domain_hostname](���y�������)\n");
         fprintf($fp,"\"%s\" - �ӤH�q�����æ��t���ӤH��ƹq�l�ɮױ��y���G�M��\n ��Ƥ���G%s(���y�������)\n",$customerName,$sheet1_endtime[$domain_hostname]);
         fprintf($fp,"�Ǹ�,����/���,���u�m�W,�q���W��,�ɮצW��,�ɮ׸��|,\"�Ӹ����O\n(�A�����Ʀr�������O�ƶq)\",\"�Ӹꤺ�e\n(�C���Ӹ����O����ܤ���)\",�Ӹ��`�ƶq\n");
         fprintf($fp,"%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d\n",$sn,$dept,$username,$domain_hostname_origin,$filename,$filepath,$pattern,$bufPattern,$total);
      }
      else
      {
         //fprintf($fp,"\"%s\",\"%s\",\"%s\",%d,%d,%d,%d,%d,%d,%d,%d\n",$filepath,$filename,$risk_level,$total,$pat_id_number,$pat_mobile_number,$pat_address_number,$pat_email_number,$pat_ccard_number,$pat_name_number,$pat_tel_number);
         fprintf($fp,"%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d\n",$sn,$dept,$username,$domain_hostname_origin,$filename,$filepath,$pattern,$bufPattern,$total);
      }
   }
   fclose($fp); // the last row is empty, do nothing here
   fclose($sheet2_fp);
   fclose($pattern_list_fp);

   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }

   //////////////////////////////////////
   // 找到所有未被寄信的 reportSheet1List 裡面的 domain/hostname, 加上此機器因為沒有極高/高 風險所以未寄出
   // 20121227 By Phantom
   //////////////////////////////////////
   foreach ($reportSheet1List as $key => $value)
   {
      if ($value == 0 && strlen($key) >= 2)
      {
         $display_content = $display_content . "[$key] 未含有符合風險等級設定之個資檔案，所以不需寄送報表<BR>";
         $reportUserCount2 = $reportUserCount2 + 1;
      }
   }

   $reportTotalCount = $reportUserCount + $reportUserCount2;
   $display_content = $display_content . "<BR><BR>簡易報表已寄送完成(寄送 $reportUserCount 台，未符合風險等級設定不需寄送 $reportUserCount2 台，共 " . $reportTotalCount . " 台)，所有信件皆有寄送副本至 " .  $contact_email . " ，確認內容後可關閉視窗<BR>";
   if(file_exists(DISPLAY_TEMPLATE))
   {
      if(!@($str_display_content = file_get_contents(DISPLAY_TEMPLATE)))
      {
         sleep(DELAY_SEC);
         echo __LINE__;

         return;
      }
      $str_display_content = str_replace("\$\$content\$\$", $display_content, $str_display_content);
      echo $str_display_content;
   }
   else
   {
      echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
      echo "</head><body>";
      echo $display_content;
      echo "</body></html>";
   }
   return;
?>
