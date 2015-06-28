<?php
///////////////////////////////////////////////////////
//setDefaultExtreme.php
//
//1.get information of the extreme type to be set
//2.upload default extreme type
//
//#001   2013/05/20  Phantom+Odie      Add netDisk and removableDisk options
//#002   2013/09/06  Odie              Add 8th type data
//#003   2014/09/03  Phantom           Add systemScanDirEnabled and systemScanDirContent
//#004   2014/09/15  Phantom           安泰銀行客製, 分行管理者只能修改 1.指定盤點路徑 2.部門設定 3.部門管理者設定
///////////////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php"); //#003 add
   define(SYSTEMSCANDIR_PATH, "/usr/local/www/apache22/data/upload_old"); //#003 add
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   if (file_exists(FILE_NAME) && file_exists(CONFIGFUNCTION_PHP))
   {
      include(FILE_NAME);
      include(CONFIGFUNCTION_PHP);
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

   //////////////////////////////
   // #004 Check 安泰銀行 conf 檔案是否存在 
   //////////////////////////////
   define(ANTIE_FILE_NAME, "/usr/local/www/apache22/entie.conf");
   if (file_exists(ANTIE_FILE_NAME))
      $entieFlag = 1;
   else
      $entieFlag = 0;
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define  
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");                      //time zone
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(STR_LENGTH, 50);
   define(RISK_TYPE_LIMIT_1, 2);                          //極高度風險最少2種
   define(RISK_TYPE_LIMIT_2, 8);                          //極高度風險最多8種  #002
   define(EXTREME_TYPE_LIMIT, 1);                         //極高度風險最少1筆
   define(HIGH_TYPE_LIMIT, 20);                           //高度風險最少20筆
   define(MEDIUM_RANGE, 3);                               //高 - 低 >= 3
   define(LOW_TYPE_LIMIT, 5);                             //低度風險最少5筆
   define(RISK_LIMIT_2, '8');                             // #002
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(SYMBOL_ERROR_REPORT_ID, -5);
   
   //check checkbox
   function check_checkbox($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {         
         return SYMBOL_ERROR;
      }
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "set_default_extreme"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //check number
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
   $defaultExtremeTypeTemp;
   $defaultExtremeType;

   //query
   $link;
   $db_host; 
   $str_query;
   $connect_db;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   
   //1.get information of the extreme type to be set   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
	//20120316 Dylan begin
	$uploadMask = $_GET["uploadMask"];
 	if($uploadMask != "0" && $uploadMask != "1")
	{
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
	} 
   //20120316 Dylan end

   //#001 begin
   $netDisk = $_GET["netDisk"];
 	if($netDisk != "0" && $netDisk != "1")
	{
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
	} 
   $removableDisk = $_GET["removableDisk"];
 	if($removableDisk != "0" && $removableDisk != "1")
	{
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
	} 
   //#001 end
   //#003 begin
   $systemScanDirEnabled = $_GET["systemScanDirEnabled"];
 	if($systemScanDirEnabled != "0" && $systemScanDirEnabled != "1")
	{
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
   }
   $systemScanDirContent = $_GET["systemScanDirContent"];
   //#003 end
   if(($defaultExtremeTypeTemp = check_checkbox($_GET["defaultExtremeType"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   $defaultExtremeTypeTemp = explode(",", $defaultExtremeTypeTemp);
   $defaultExtremeType = "";
   foreach ($defaultExtremeTypeTemp as $index => $value)
   {
      if ($value >= '0' && $value < RISK_LIMIT_2)  //check number between 0 ~ 7
      {
         if ($index == 0)
            $defaultExtremeType = $defaultExtremeType . $value;
         else
            $defaultExtremeType = $defaultExtremeType . "," . $value;
      }
      else
      {
         //echo $value;
         sleep(DELAY_SEC);
         echo SYMBOL_ERROR;
         return;
      }   
   }
   
   if($index < RISK_TYPE_LIMIT_1 - 1)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   //echo $defaultExtremeType;
   
   if(($riskTypeNumber = check_number($_GET["riskTypeNumber"])) == SYMBOL_ERROR || $riskTypeNumber < RISK_TYPE_LIMIT_1 || $riskTypeNumber > RISK_TYPE_LIMIT_2)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   if(($extremeNumber = check_number($_GET["extremeNumber"])) == SYMBOL_ERROR || $extremeNumber < EXTREME_TYPE_LIMIT)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   if(($highNumber = check_number($_GET["highNumber"])) == SYMBOL_ERROR || $highNumber < HIGH_TYPE_LIMIT)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   if(($lowNumber = check_number($_GET["lowNumber"])) == SYMBOL_ERROR || $lowNumber < LOW_TYPE_LIMIT || $highNumber < $lowNumber + MEDIUM_RANGE)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   $expressEnable = $_GET["expressEnable"];
 	if(!($expressEnable == "0" || $expressEnable == "1"))
	{
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
   }
   
   $expressTimeout = $_GET["expressTimeout"];
   if(check_number($expressTimeout) == SYMBOL_ERROR || $expressTimeout < 1 || $expressTimeout > 5)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;	
   }

   //2.upload default extreme type
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   //20120316 Dylan begin
   //#001 modified, add netDisk and removableDisk
   //----- update customer ----- 
   /*
   $str_update = "
      update customer
      set uploadMask = $uploadMask, netDisk = $netDisk, removableDisk = $removableDisk
      where GUID = '$GUID' ";
    */
   $str_update = "
      update customer
      set uploadMask = $uploadMask, netDisk = $netDisk, removableDisk = $removableDisk, expressEnable = $expressEnable, expressTimeout = $expressTimeout
      where GUID = '$GUID' ";

   if($entieFlag != 1) // #004 安泰
   { 
      //----- Connect to MySql ----- 
      if (!mysqli_query($link, $str_update))  //update fail
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
   } 
	//20120316 Dylan end	 
   //----- query -----

   $str_update = "
      update riskCategory
      set low = $lowNumber, high = $highNumber, extreme = $extremeNumber, extreme_type_num = $riskTypeNumber, 
      extreme_type_num = $riskTypeNumber, extreme_type = '$defaultExtremeType'" .
      " where GUID = '" . $GUID . "'";      
   
   //----- Connect to MySql ----- 
   if($entieFlag != 1) // #004 安泰
   { 
      if (!mysqli_query($link, $str_update))  //update successfully
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
   }

   //#003 begin
   set_config_by_name($link,$GUID,"systemScanDirEnabled",$systemScanDirEnabled);
   $systemScanDirPath = SYSTEMSCANDIR_PATH . "/$GUID/systemScanDir.txt";
   $fp = fopen($systemScanDirPath,"w");
   if ($fp) {
      fprintf($fp,"%s",$systemScanDirContent);
      fclose($fp);
      system("chmod 777 $systemScanDirPath");
   }
   //#003 end
   
   //close link
   if ($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   echo SUCCESS;
   return;
?>
