<?php
///////////////////////////////////////////////////////
//modifyWhiteList.php
//
//1.get content of whitelist 
//2.save the content to /usr/local/www/apache22/data/upload_old/$GUID/whitelist.txt
//
//#000   2014/09/10  Phantom           file created 
//#001   2014/09/15  Phantom           系統管理者的話, copy whitelist.txt to all GUIDs  
///////////////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(WHITELIST_PATH, "/usr/local/www/apache22/data/upload_old"); 
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
   if (!session_is_registered("GUID") || !session_is_registered("GUID_ADM"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      echo -__LINE__;
      exit();
   }
   if ($_SESSION["GUID"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      echo -__LINE__;
      exit();
   }
   $GUID = $_SESSION["GUID"];
   $GUID_ADM = $_SESSION["GUID_ADM"];
   session_write_close();

   //////////////////////////////
   // #001 Check GUID_ADM and set systemAdm flag
   //////////////////////////////
   if ($GUID_ADM == "")
      $systemAdmFlag = 0;
   else if ($GUID_ADM == "00000000_0000_0000_0000_000000000000")
      $systemAdmFlag = 1;
   else
   {
      sleep(DELAY_SEC);
      echo -__LINE__;
      exit();
   }

   header('Content-Type:text/html;charset=utf-8');
   
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "modify_whitelist"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;

   //1.get information of the extreme type to be set   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   $whiteListContent = $_GET["modify_whitelist_content"];

   $whiteListPath = WHITELIST_PATH . "/$GUID/whitelist.txt";
   $fp = fopen($whiteListPath,"w");
   if ($fp) 
   {
      fprintf($fp,"%s",$whiteListContent);
      fclose($fp);
      system("chmod 777 $whiteListPath");
   }
   else 
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if ($systemAdmFlag == 0)
   {
      echo SUCCESS;
      return;
   }

   ///////////////////////////
   // #001 Begin, try to find all the GUID and copy whitelist.txt 
   /////////////////////////// 
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);

   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   $str_query = "select GUID from customer where status=1";
   if ($result = mysqli_query($link, $str_query))
   {
      while ($row = mysqli_fetch_assoc($result))
      {
         $tmpGUID = $row["GUID"];
         $whiteListPath2 = WHITELIST_PATH . "/$tmpGUID";
         if (!file_exists($whiteListPath))
            system("mkdir -p -m 0774 $whiteListPath");
         $whiteListPath2 = WHITELIST_PATH . "/$tmpGUID/whitelist.txt";
         system("cp $whiteListPath $whiteListPath2");
      }
      mysqli_free_result($result);
   }
   ///////////////////////////
   // #001 End 
   /////////////////////////// 
   
   echo SUCCESS;
   return;
?>
