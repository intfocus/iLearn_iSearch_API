<?php
///////////////////////////////
//deleteXML.php
//
// According to the submit criteria, find all computer status in the computerList and identityFound
// 2012/06/18 created by Phantom
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
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEFAULT_GUID, "000000000000000000000000000000000000");

   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(MAPPING_ERROR, -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "deleteXML"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //----- Check name -----
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
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
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str <= 0 || $check_str > SEARCH_RISK_LIMIT)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }


   //get data from client
   $cmd;
   $XMLID;
   $entryID;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   
   //data
   
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($XMLID = check_number($_GET["XMLID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($entryID = check_number($_GET["entryID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   ////////////////
   // move the XML file to a recycle bin
   ////////////////
   $sql = "select * from identityFound
           where GUID='$GUID' and XMLID='$XMLID'";
   if ($result = mysqli_query($link, $sql))
   {
      $row = mysqli_fetch_assoc($result);
      $xmlCreateTime = $row["create_time"];
      $xmlCreateTime = strtotime($xmlCreateTime);
      $xmlCreateTime = date('Ym', $xmlCreateTime);		 
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

   $cmdstr = "mv /usr/local/www/apache22/data/upload_old/$GUID/$xmlCreateTime/$XMLID* /usr/local/www/apache22/data/upload_recycle/.";
   system($cmdstr);
   

   ////////////////
   // delete the corresponding XMLID in identityFound 
   ///////////////
   $sql = "delete from identityFound where XMLID=$XMLID";

   if($result = mysqli_query($link, $sql)){
      mysqli_free_result($result);
   }
   else{
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   ////////////////
   // update stauts=-1 in the entry table
   ////////////////
   $sql = "update entry set status=-1 where entryID=$entryID";

   if($result = mysqli_query($link, $sql)){
      mysqli_free_result($result);
   }
   else{
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   //close link
   if ($link)
   {
       mysqli_close($link);
       $link = 0;
   }

   //////////////////
   // For Success 
   //////////////////
   echo "0";
?>
