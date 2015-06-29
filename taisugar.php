<?php
/**********************************************
 * A script used to update (correct) the remains fo taisugar.
 *
 * The numbers of remain are incorrect due to the bugs that 
 * the remain will still be decreased in MAC-locked version 
 * when a dropped scan is done and uploaded.
 *
 * 20130905 created by Odie
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(DELAY_SEC, 3);                                   //delay reply
   define(FILE_ERROR, -4);
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
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   
   //----- Variable definition -----
   $guid;              //get guid from client
   $link;              //connect to mysql
   $sql;               //query command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array
   $remain;
   $mac_count;
   $correct_count;

   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB); 
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      echo "Connet to DB error at line " . __LINE__ . "\n";
      return;
   }
   
   
   //--------------------------------------------------------------------------
   // 1. define the hash {"guid" => numbers of authentication}
   //--------------------------------------------------------------------------
   $remain_arr = array();
   
   $auth_arr = array(
      "897420DC_29C6_842B_C5C1_21937B955693" => 660,
      "897420DC_29C6_842B_C5C1_21937B955694" => 225,
      "897420DC_29C6_842B_C5C1_21937B955695" => 110,
      "897420DC_29C6_842B_C5C1_21937B955696" =>  90,
      "897420DC_29C6_842B_C5C1_21937B955697" => 160,
      "897420DC_29C6_842B_C5C1_21937B955698" => 140,
      "897420DC_29C6_842B_C5C1_21937B955699" =>  55,
      "897420DC_29C6_842B_C5C1_21937B955700" => 110,
      "897420DC_29C6_842B_C5C1_21937B955701" =>  10
   );
   /*
   $auth_arr = array(
      "8F44A8AB_5C6C_6232_CD4F_642761007428" => 100000,
      "897420DC_29C6_842B_C5C1_21937B955693" => 660
   */
   
   //--------------------------------------------------------------------------
   // 2. count remains for each guid
   //--------------------------------------------------------------------------

   $sql = "SELECT GUID, remain FROM customer";
   if ($result = mysqli_query($link, $sql))
   {
      while ($row = mysqli_fetch_assoc($result))
      {
         $guid = strtoupper($row["GUID"]);
         $remain_arr[$guid] = (int)$row["remain"];
      }
   }
   else
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
         echo "Count remain error at line " . __LINE__ . "\n";
         return;
      }
   }
   
   //--------------------------------------------------------------------------
   // 3. execute SQL to find numbers of mac and update remain
   //--------------------------------------------------------------------------
   $sql = "SELECT GUID, COUNT(*) AS mac_count FROM macAddress GROUP BY GUID";
      
   if ($result = mysqli_query($link, $sql))
   {
      //--------------------------------------------------------------------------
      // 3.1. if remain <= auth - numbers of mac, update remain to (auth - numbers of mac)
      //--------------------------------------------------------------------------
      while ($row = mysqli_fetch_assoc($result))
      {
         $guid = strtoupper($row["GUID"]);
         $mac_count = (int)$row["mac_count"];
         
         if (array_key_exists($guid, $remain_arr) && array_key_exists($guid, $auth_arr))
         {
            $remain = $remain_arr[$guid];
            $correct_count = $auth_arr[$guid] - $mac_count + 1;
            if ($remain < $correct_count)
            {   
               $sql = "UPDATE customer SET remain = $correct_count WHERE GUID = '$guid'";
               mysqli_query($link, $sql);
            }
            else
            {
               $correct_count = $remain;
            }
            echo "$guid\t$remain\t$mac_count\t$correct_count\n";
         }
      }
      mysqli_free_result($result);
      echo "Update successfully!\n";
      return;
   }
   else
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
         echo "Count mac_address error at line " . __LINE__ . "\n";
         return;
      }
   }
?>     
