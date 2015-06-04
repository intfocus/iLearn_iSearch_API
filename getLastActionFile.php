<?php
//////////////////////////////////////////////////
// 2013/02/19 created by Odie
//   由genExcel.pl呼叫，進資料庫找某一user在某一時間點前最近的action file
//   parameter: (1)hostname (2)domain_name (1+2表示某一特定user) (3)create_time
//////////////////////////////////////////////////

   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");                //account file name
   define(DELAY_SEC, 3);                                                //delay reply
   define(ACTION_DIR, "/usr/local/www/apache22/data/upload_old/");   //the directory of action file
   
   if(file_exists(FILE_NAME))
   {
      include_once(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo "";
      exit;
   }

   //----- Read connect information from DB.conf -----
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   $prog = $argv[0];
   $hostname = $argv[1];
   $domain_name = $argv[2];
   $create_time = $argv[3];
   $ret = "";

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo "";
      exit;
   }
   
   $str_query = "
      select XMLID, create_time, GUID from identityFound
      where hostname='$hostname' and domain_name='$domain_name' and create_time<'$create_time'
      order by create_time DESC";

   if($result = mysqli_query($link, $str_query))   //query success
   {
      while ($row = mysqli_fetch_assoc($result))
      {
         $new_xmlid = $row["XMLID"];
         $new_time = $row["create_time"];
         $new_guid = $row["GUID"];
         $year_month = substr($new_time, 0, 4). substr($new_time, 5, 2);
         $filepath = ACTION_DIR . "$new_guid/$year_month/$new_xmlid.action";
         if (file_exists($filepath))
         {
            $ret = $filepath;
            break;
         }
      }
      mysqli_free_result($result);
   }

   if($link)
   {
      mysqli_close($link);
   }
   echo $ret;
   exit;
?>
