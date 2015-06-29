<?php
/*************************************************
 *deleteEntryID.php
 *Query Entry table by GUID, entryID, hostname, domain_name
 *    and status = waiting or dropped.
 *1.if found
 *    1.1 if status = waiting then update customer table, remain = remain + 1.
 *    1.2 update Entry table, status = deleted.
 *2.not found => delay and return
 *
 *  2012/02/01 Jeffrey Chan
 *************************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(DELAY_SEC, 3);                                   //delay reply
   define(FILE_ERROR, -4);
   //----- Read connect information from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include_once(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;

      return;
   }
   define(ILLEGAL_CHAR, "'-;<>");                          //illegal char
   define(STR_LENGTH, 64);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(EMPTY_REMAIN, -2);
   define(SYMBOL_ERROR, -3);
   define(UPLOAD_FAILED, -5);
   define(NO_MATCHING_DATA, -6);
   //status
   define(DELETED, -1);
   define(WAITING, 0);
   define(COMPLETED, 1);
   define(DROPPED, 2);

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

      return $check_str;
   }
   //----- Check domain -----
   function check_domain($check_str)
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

      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   $guid;              //get guid from client
   $entryid;           //get entryid from client
   $hostname;          //get hostname from client
   $domain_name;       //get domain from client    
   $count;             //result count
   $status;            //status of result
   $link;              //connect to mysql
   $str_query;         //query command string
   $str_update;        //update command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array

   //----- Check receive information from client -----
   if(($guid = check($_GET["GUID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;

      return;
   }
   if(($entryid = check($_GET["EntryID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;

      return;
   }
   if(($hostname = check($_GET["hostname"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;

      return;
   }
   if(($domain_name = check_domain($_GET["domain_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;

      return;
   }
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)  //connect to server failure
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;

      return;
   }
   //----- query by entryid, guid, host, domain, and status = waiting or dropped -----
   $str_query = "
      select status, count(*) as count 
      from entry 
      where entryID = $entryid 
         and GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name' 
         and ( status = " . WAITING . " or status = " . DROPPED . " )";
   if($result = mysqli_query($link, $str_query))   //query success
   {
      $row = mysqli_fetch_assoc($result);
      $status = $row["status"];
      $count = $row["count"];
      mysqli_free_result($result);    //free useless result
      if($count > 0)   //have matching data
      {
         //----- status is waiting, remain = remain + 1 -----
         if($status == WAITING)
         {
            $str_update = "
               update customer
               set remain = remain + 1
               where GUID = '$guid'";
            if(!mysqli_query($link, $str_update))   //update remain failure
            {
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               sleep(DELAY_SEC);
               echo DB_ERROR;

               return;
            }
         }
         //----- set status = dropped -----
         $str_update = "
            update entry
            set status = " . DELETED . "
            where entryID = $entryid";
         if(!mysqli_query($link, $str_update))   //update entry status failure
         {         
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo DB_ERROR;

            return;
         }
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         echo SUCCESS;

         return; 
      }
      else   //doesn't have matching data
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         //----- delete the upload file ----- 
         sleep(DELAY_SEC);
         echo NO_MATCHING_DATA;

         return;
      }
   }
   else   //query failure
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;

      return;
   }
?>
