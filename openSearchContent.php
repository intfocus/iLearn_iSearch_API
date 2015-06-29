<?php
///////////////////////////////
//openSearchContent.php
//
//1.get information of the file
//2.check GUID, search from XML
//3.return string of the file
//
// #001 modified by Odie, 2013/09/25
//      若檔名有連續空白，因為傳過來的參數無法正確表示空白的數量，會導致去parse XML時找不到對的檔案名稱。
//      已經修正傳過來的參數，這裡也一併修改空白的表示方式，讓它可以正確反應出空白的數量
//
///////////////////////////////

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
   
   session_start();
   if (!session_is_registered("GUID"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   session_write_close();
   
   //$GUID = "8f44a8ab_5c6c_6232_cd4f_642761007428";
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(MSG_NO_UPLOAD_DATA, "無資料。若需觀看搜尋結果，請至\"設定\"頁面\"勾選\"上傳已遮蔽資料\"後，再重新執行掃描。");
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(STR_LENGTH, 200);
   define(SEARCH_SIZE, 100);
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);

   date_default_timezone_set(TIME_ZONE);
   
   //check name
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {         
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt;", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt;", $check_str);
      }
      return $check_str;
   }
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "open_content"))
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
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $xmlID;
   $fileID;
   $total;
   $type_found;

   //query
   $link;
   $str_query1;
   $str_query2;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   
   //XML
   $$xmlCreateTime;
   $xmlGUID;
   $xmlHostname;
   $xmlDomainName;
   $xmlDepartment;
   $xmlEmployee;
   $xmlFilepath;
   $xmlFiletype;
   $xmlLastModifyTime;
   
   //1.get information of the file
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }   
   if(($xmlID = check_number($_GET["xmlID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($total = check_number($_GET["total"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($type_found = check_name($_GET["type_found"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   } 


   /////////////////////
   // yaoan 20120511 add
   ////////////////////

   $xmlLastModifyTime = $_GET["last_modify"];
   $xmlFilepath = $_GET["filepath"];
   $xmlFiletype = $_GET["filetype"];

   ////////////////
   // yaoan end add
   ////////////////

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo -__LINE__; 
      return;
   }

   //----- query -----
   $str_query1 = "
      select *
      from identityFound
      where GUID = '" . $GUID . "' and XMLID = '" . $xmlID . "'";

   $str_query2 = "
      select *
      from identityFile
      where fileID = $fileID";
	  
   //2.check GUID, search from XML
   //----- Connect to MySql ----- 
   if ($result = mysqli_query($link, $str_query1))
   {
      $row = mysqli_fetch_assoc($result);
      $xmlCreateTime = $row["create_time"];
      $xmlCreateTime = strtotime($xmlCreateTime);
      $xmlCreateTime = date('Ym', $xmlCreateTime);		 
      $xmlGUID = $row["GUID"];
      $xmlHostname = $row["hostname"];
      $xmlDomainName = $row["domain_name"];
      $xmlDepartment = $row["department"];
      $xmlEmployee = $row["employee_name"];
      $contentFilepath = $row["XMLFilePath"];
      mysqli_free_result($result);
      unset($row);	  
   }
   else //query failed
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo -__LINE__;
      return;
   }

   //if wrong guid, return
   if (strcmp($xmlGUID, $GUID) != 0) 
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo -__LINE__;
      return;
   }
   

   //close link
   if ($link)
   {
       mysqli_close($link);
       $link = 0;
   }

   //path in data
   //$filepath = "upload_old/$GUID/$xmlCreateTime/$xmlID-$GUID-$xmlHostname-$xmlDomainName.xml"; // original filename
   $filepath = "upload_old/$GUID/$xmlCreateTime/$xmlID-$GUID.xml";
   //path in trial
   //$filepath = "upload_old/$GUID/$xmlCreateTime/$GUID-$xmlHostname-$xmlDomainName.xml";
   //path from DB
   //$filepath =  "upload_old/$GUID/$contentFilepath";
   //echo $filepath;

   $filedata = "";
   if(!$xml = simplexml_load_file($filepath))
   {
      echo -__LINE__;
      return;
   }
   
   //if wrong guid, return
   if (strcmp($xml->GUID, $GUID) != 0)
   {
      echo -__LINE__;
      return;
   }
   
   $flag = 0;
   foreach($xml->file as $child)
   {
      if (strcmp($child->file_info->file_full_name, $xmlFilepath) == 0)
      {
         echo "cpu";
         $i = 0;
         $patternType = -1;
         foreach($child->pattern_list->pattern as $pattern)
         {
            if ($pattern == "")
            {
               $flag = 1;
            }
            
            if ((string)$pattern->attributes()->type == $patternType)
            {
               $i++;
            }
            else
            {
               $patternType = (string)$pattern->attributes()->type;
               $i = 1;
            }
            if ($i <= SEARCH_SIZE)  //每種只列100筆
               $filedata = $filedata . $pattern . ", ";
         }
      }
   }
   if (strcmp($filedata, "") == 0)
   {
      echo -__LINE__;
      return;
   }
   
   if ($flag == 1)  //使用者未上傳已屏蔽資料
      $filedata =  MSG_NO_UPLOAD_DATA;

   //3.return string of the file
   
   // #001 add
   $xmlFilepath = str_replace(" ", "&nbsp;", $xmlFilepath);

   $return_string = "";
   $return_string = $return_string . "<span class=\"dialog\">"
                                   . "<div class=\"header\">"
                                   . "<span id=\"closeDialog\" class=\"close\" OnClick=\"hideContent();\"></span>"
                                   . "<span class=\"title\">風險檔案內容</span>"
                                   . "</div>"
                                   . "<div class=\"content\">"
                                   . "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                   . "<tr>"
                                   . "<th class=\"title left\" colspan=\"2\"><span>檔案基本資料</span></th>"
                                   . "<th class=\"title right\">個資搜尋結果</th>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>電腦編號：</span></th>"
                                   . "<td>$xmlDomainName/$xmlHostname</td>"
                                   . "<td class=\"resultW\" rowspan=\"8\"><div class=\"result\">$filedata</div></td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>員工姓名：</span></th>"
                                   . "<td>$xmlEmployee</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>部門：</span></th>"
                                   . "<td>$xmlDepartment</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>檔案類型：</span></th>"
                                   . "<td>$xmlFiletype</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>最後編輯：</span></th>"
                                   . "<td>$xmlLastModifyTime</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>個資數量：</span></th>"
                                   . "<td>" . number_format($total) . "</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>個資種類：</span></th>"
                                   . "<td>$type_found</td>"
                                   . "</tr>"
                                   . "<tr>"
                                   . "<th><span>檔案路徑：</span></th>"
                                   . "<td>$xmlFilepath</td>"
                                   . "</tr>"
                                   . "</table>"
                                   . "</div>"
                                   . "</span>";
   echo $return_string;
   return;
   
?>
