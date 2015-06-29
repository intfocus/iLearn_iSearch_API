<?php
/**********************************************
 *checkEntryID.php
 *Query upload status for entryID + GUID,
 *1.if found and status=1(already uploaded successfully) => return 0 
 *
 *2.not found => delay 3 seconds and return -n 
 *
 * #000   2012/10/26 Phantom     file created
 * #001   2013/05/10 Phantom     Add for status, when P-Marker reader checking the entry status, status=4 should be considered also.
 * #002   2013/08/09 Phantom     checkEntryID 的時候也要找到最大的那個 entryID, 因為上傳的時候使用鋸箭法, 明明 entryID=100, 
 *                               但是因為有 entryID=102 的存在, 最後會修改 entryID=102 的 status, identityFound 裡面存放的也是 102
 *                               因此這邊同步要找到 同樣 domain_name, hostname, 目前最大的 entryID (且 status=1 or status=4).
 *                               如果沒有 check status=1 or status=4, 萬一 reader 正在做上次掃描結果處置, 但是已經有新的一次掃描, 會導致錯亂.
 *
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(DELAY_SEC, 3);                                   //delay reply
   define(FILE_ERROR, -7);
   //----- Read connect information from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;

      return;
   }
   define(ILLEGAL_CHAR, "'-;<>");                          //illegal char
   define(TIME_ZONE, "Asia/Taipei");
   define(STR_LENGTH, 64);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(DB_ERROR, -1); 
   define(NOT_FOUND, -2);   
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_GUID, -4);
   define(SYMBOL_ERROR_ENTRYID, -5);
   define(STATUS_NOT_COMPLETE, -6);
   //status
   define(WAITING, 0); 

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
?>
<?php
   //----- Variable definition -----
   $guid;              //get guid from client
   $entryid;           //get entryid from client

   //----- Check get information from client -----
   if(($guid = check($_GET["GUID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_GUID;

      return;
   }
   if(($entryid = check($_GET["EntryID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_ENTRYID;

      return;
   }
   //----- Connect to MySQL -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB); 
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;

      return;
   }
   //----- Query entryID by GUID, hostname, domainname -----
   //#002, 先拿 hostname + domain_name
   $str_query = "
      select hostname,domain_name 
      from entry 
      where GUID = '$guid' 
      and entryID = '$entryid'";
   if($result = mysqli_query($link, $str_query))
   {
      $row = mysqli_fetch_assoc($result);
      $rownum = mysqli_num_rows($result);
      if ($rownum == 0)
      {
         sleep(DELAY_SEC);
         echo NOT_FOUND;

         if($link)   //release sql connection
         {
            mysqli_close($link);
            $link = 0;
         }
         return;
      }
      $hostname = $row["hostname"];
      $domain_name = $row["domain_name"];
      mysqli_free_result($result);    //free useless result
   }
   else
   {
      if($link)   //release sql connection
      {
         mysqli_close($link);
         $link = 0;
      } 
      sleep(DELAY_SEC);
      echo DB_ERROR;

      return;
   }
   //end of #002

   //#002, 試著一筆一筆從最大的 entryID 開始倒著往回找, 找到有 status=1 or status=4 的代表有上傳成功
   //      這樣可以解決 entryID=100 掃到一半關機, 然後過了 weekend 繼續掃描, 因為 entryID=100 已經被判斷成 expired, 會直接拿到 entryID=102.
   //      最後的上傳結果會把最大的 entryID(102) 變成 status=1 or status=4, identityFound 裡面記錄的也是 entryID=102 上傳成功.
   //      但是 XML 裡面的 entryID 還是 100, PMarkerReader 打開 XML 永遠會拿 entryID=100 來詢問
   $str_query = "
      select status,entryID 
      from entry 
      where GUID = '$guid'
         and hostname = '$hostname'
         and domain_name = '$domain_name'
         and entryID >= '$entryid'
      order by entryID DESC";
   if($result = mysqli_query($link, $str_query))   //query success
   {
      $rownum = mysqli_num_rows($result);
      if ($rownum == 0)
      {
         sleep(DELAY_SEC);
         echo NOT_FOUND;

         if($link)   //release sql connection
         {
            mysqli_close($link);
            $link = 0;
         }
         return;
      }
      $count=0;
      while ($count < $rownum)
      {
         $count ++;
         $row = mysqli_fetch_assoc($result);
         $status = $row["status"];
         $newEntryID = $row["entryID"]; //其實用不到, #002
         if($status == 1 || $status == 4)   //have entryid and status=1 or 4, #001 modified, add status=4
         {
            echo "0";
            mysqli_free_result($result);    //free useless result
            if($link)   //release sql connection
            {
               mysqli_close($link);
               $link = 0;
            }
            return;
         }
         else if ($count == $rownum) //have entryid and status <> 1
         {
            echo STATUS_NOT_COMPLETE;
            mysqli_free_result($result);    //free useless result
            if($link)   //release sql connection
            {
               mysqli_close($link);
               $link = 0;
            }
            return;
         }
      }
   }
   else   //query failure
   {
      if($link)   //release sql connection
      {
         mysqli_close($link);
         $link = 0;
      } 
      sleep(DELAY_SEC);
      echo DB_ERROR;

      return;
   }
?>
