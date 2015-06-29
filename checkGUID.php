<?php
/*********************************
 *checkGUID.php
 * 1. Check client IP in white list
 * 2. Get GUID and check formate
 * 3. Query customer table by GUID 
 *  2012/03/21 Jeffrey Chan
 *********************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3); 
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      $ret =  -__LINE__;
      $errMsg = "Can't find CONF file.";
      goto errExit;
   }
   define(CHECKIP_PHP, "$working_path/checkIP.php");
   define(ILLEGAL_CHAR, "'-;<>");                        //illegal char
   define(STR_LENGTH, 64);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(SUCCESS, 0); 
   define(SYMBOL_ERROR, -1);

   //----- Check string -----
   function check($check_str)
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

      return SUCCESS;
   }

?>
<?php
   $ret;
   $errMsg;
   $guid;
   $company_no;
   $link;
   $result;
   $row;
   $str_query;

   //--------------------------------------------
   //----- 1. Check client IP in white list -----
   //--------------------------------------------
   //----- Include checkIP.php -----
   if(file_exists(CHECKIP_PHP))
   {
      include(CHECKIP_PHP);
   } 
   else
   {
      $ret = -__LINE__;
      $errMsg = "Check IP error.";
      goto errExit;
   }
   //----- Check client IP in white list -----
   $ret_checkIP = checkIP($_SERVER["REMOTE_ADDR"]);
   if($ret_checkIP == FALSE)
   {
      $ret = -__LINE__;
      $errMsg = "Not in available list.";
      goto errExit;
   }

   //-----------------------------------------
   //----- 2. Get GUID and check formate -----
   //-----------------------------------------
   $guid = $_GET["GUID"];
   if(check($guid) != SUCCESS)
   {
      $ret = -__LINE__;
      $errMsg = "Check GUID error.";
      goto errExit;
   }

   //-------------------------------------------
   //----- 3. Query customer table by GUID -----
   //-------------------------------------------
   //----- Connect to MySQL -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB); 
   if(!$link)   //connect to server failure
   {
      $ret = -__LINE__;
      $errMsg = "Connect to DB error.";
      goto errExit;
   }
   //----- Query customer table by GUID -----   
   $str_query = "
      select company_no
      from customer
      where GUID = '$guid'
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(mysqli_num_rows($result))    //have matching customer_no
      {
         $row = mysqli_fetch_assoc($result); 
         $company_no = $row["company_no"];
         mysqli_free_result($result);
         unset($row);
                  
      }
      else
      {
         $ret = -__LINE__;
         $errMsg = "No matching GUID.";
         goto errExit;
      }
   }
   else
   {
      $ret = -__LINE__;
      $errMsg = "Query error.";
      goto errExit;
   }
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   echo $company_no;

   return; 

   errExit:
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   $ret = $ret . "," . $errMsg; 
   sleep(DELAY_SEC);
   echo $ret;

   return;
?>
