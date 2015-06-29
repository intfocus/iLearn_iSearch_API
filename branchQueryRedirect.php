<?php
///////////////////////////////////////////////////////
//branchQueryRedirect.php
//For system admin to redirect to a specific customer
//
//#000   2014/09/15  Phantom           file created 
///////////////////////////////////////////////////////

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
   if (!session_is_registered("GUID_ADM"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit();
   }
   if ($_SESSION["GUID_ADM"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit();
   }
   $GUID = $_GET["GUID"];
   session_write_close();
   
   //define
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(STR_LENGTH, 50);
   define(EXTREME_TYPE_NUMBER, '7');                      //個資類型
   define(SEARCH_RISK_LIMIT, 3);                          //極高+高
   define(SEARCH_SIZE, 1000);                             //上限1000筆
   define(PAGE_SIZE, 1000);
   define(SEARCH_EXTREME, 2);
   define(SEARCH_HIGH, 1);
   define(TRIAL, 0);

   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(MAPPING_ERROR, -5);

   function check_GUID($check_str)
   {
      if (strchr($check_str,"-"))
         return SYMBOL_ERROR;
      if (strchr($check_str,"'"))
         return SYMBOL_ERROR;
      return SUCCESS;
   }

   //query
   $link;
   $str_query;
   $result;                 //query result

   if (check_GUID($GUID) != SUCCESS)
   {
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit();
   }

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   ///////////////////////////////////////////////
   // check GUID in DB
   ///////////////////////////////////////////////
   $str_query = "select * from customer where GUID='$GUID'";
   if ($result = mysqli_query($link,$str_query)) {
      $row_num = mysqli_num_rows($result);
      if ($row_num > 0) {

         ///////////////////////////////////////////////
         // GUID is okay, redirect to OSC_index.php
         ///////////////////////////////////////////////
         mysqli_free_result($result);
         mysqli_close($link);
         session_start();
         $_SESSION["GUID"] = $GUID;
         $_SESSION["loginLevel"] = 1;
         $_SESSION["loginName"] = "SystemAdm";
         session_write_close();
         header("Location:OSC_index.php");
         exit();
      }
   }
   else {
      if ($link)
        mysqli_close($link);
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   if ($link)
      mysqli_close($link);

   ///////////////////////////////////
   // GUID not exist, delay replay  
   ///////////////////////////////////
   sleep(DELAY_SEC);
   header("Location:main_adm.php");
   return;
?>
