<?php
/**********************************************
 *getEntryID.php
 *Query entryID by GUID, hostname, domain_name, and status = waiting
 *1.if found => update last_modified_time and return entryID
 *
 *2.not found => Query DB(customer) by GUID, check remain > 0
 *   // 2.1 if remain > 0 then creat new and return entryID and remain - 1
 *   (20130308 modified)
 *   2.1     if conf setting is NOT MAC
 *   2.1.1     if remain > 0 then create new and return entryID and remain - 1
 *   2.1.2   else (without check remain > 0 or not), check MAC in the list (check $mac, if it is an empty string, return ERROR)
 *   2.1.2.1   if exist => create new and return entryID
 *   2.1.2.2   if not exist and remain > 1 => append MAC to the list, create new and return entryID and remain - 1
 *   2.1.2.3   if not exist and remain <= 1 => return entryID = error (must control remain>=1 to insure the previous N clients still can do scanning)
 *             (client 端會檢查 remain, 如果 remain <= 0 的話將會 grey button 無法開始掃描, 所以鎖 MAC 的狀況下必須保證 remain 永遠 >= 1)
 *
 *   2.2 if remain <= 0 then return entryID = error
 *
 * 2012/01/05 Jeffrey Chan
 * 
 * 2013/06/25 Odie modified #002
 *   1.Conf setting of MAC moved from "conf" to "customer" table, change the corresponding code,
 *     get MAC conf from "customer"
 *
 * 2013/09/02 Odie modified #003
 *   1.guid + domain_name + hostname exists but with different MAC, the remain will still be decreased
 *     => fix the problem, the remain won't be decreased
 *
 * 2013/12/06 Phantom+Odie modified #004
 *   1. If remain=1, still need to check if domain+hostname+GUID exist or not. If exist, still append mac
 *
 * 2014/11/13 Odie modified #005
 *   1. Entie customization, decrease remain from super user rather than branch
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");   //account file name
   define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php");   //#002
   define(DELAY_SEC, 3);                                   //delay reply
   //----- Read connect information from DB.conf -----
   //#002, also include config function
   if(file_exists(FILE_NAME) && file_exists(CONFIGFUNCTION_PHP))
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
   define(ILLEGAL_CHAR, "'-;<>");                          //illegal char
   define(TIME_ZONE, "Asia/Taipei");
   define(STR_LENGTH, 64);
   define(MAC_STR_LENGTH, 18);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(DB_ERROR, -1);       
   define(EMPTY_REMAIN, -2);   
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_GUID, -4);
   define(SYMBOL_ERROR_HOSTNAME, -5);
   define(SYMBOL_ERROR_DOMAIN_NAME, -6);
   define(SYMBOL_ERROR_MAC, -7);
   define(FILE_ERROR, -8);
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
   //----- Check domain name -----
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
   //----- Check mac address -----
   function check_mac($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > MAC_STR_LENGTH)
      {
         
         return SYMBOL_ERROR;
      }
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {

         return SYMBOL_ERROR; 
      }
      //----- check empty string -----
      if(trim($check_str) != "")
      {
         //todo: regexp for MAC format
      }

      return $check_str;
   }
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "scan"))
      { 

         return SYMBOL_ERROR;
      }

      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   $cmd;               //get command from client
   $guid;              //get guid from client
   $hostname;          //get hostname from client
   $domain_name;       //get domain from client
   $mac;               //get mac from client
   $link;              //connect to mysql
   $date_time;         //date time, yyyy-mm-dd hh:mm:ss
   $str_query;         //query command string
   $str_update;        //update command string
   $str_insert;        //insert command string
   $result;            //result object, receive query result from mysql
   $result_entryid;    //result entryid
   $row;               //array, put result into an array
   $remain;            //customer remain times

   //----- Check get information from client -----
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;

      return;
   }
   if(($guid = check($_GET["GUID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_GUID;

      return;
   }
   if(($hostname = check($_GET["hostname"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_HOSTNAME;

      return;
   }
   if(($domain_name = check_domain($_GET["domain_name"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_DOMAIN_NAME;

      return;
   }
   if(($mac = check_mac($_GET["mac"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_MAC;
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
   $str_query = "
      select entryID 
      from entry 
      where GUID = '$guid' 
         and hostname = '$hostname' 
         and domain_name = '$domain_name' 
         and status = " .  WAITING;
   if($result = mysqli_query($link, $str_query))   //query success
   {
      $row = mysqli_fetch_assoc($result);          
      $result_entryid = $row["entryID"];
      mysqli_free_result($result);    //free useless result
      date_default_timezone_set(TIME_ZONE); 
      $date_time = date("Y-m-d H:i:s");   //set date time
      if($result_entryid > 0)   //have entryid
      {
         //----- update last_modified_time -----
         $str_update = "
            update entry 
            set last_modified_time = '$date_time' 
            where entryID = $result_entryid";
         if(mysqli_query($link, $str_update))   //update success
         {   
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo $result_entryid;

            return;
         }
         else   //update failure
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
      else   //doesn't have entryID
      {
         /*
         *   2.1     if conf setting is NOT MAC
         *   2.1.1     if remain > 0 then create new and return entryID and remain - 1
         *   2.1.2   else (do not check remain > 0 or not), check MAC in the list
         *   2.1.2.1   if exist => create new and return entryID
         *   2.1.2.2   if not exist and remain > 1 => append MAC to the list, create new and return entryID and remain - 1
         *   2.1.2.3   if not exist and remain <= 1 => return entryID = error (must control remain>=1 to insure the previous N clients still can do scanning)
         */
         ////////////////////////
         // Get conf_mac setting 
         ////////////////////////
         /*
         $str_query = "select conf_value from conf where conf_name = 'MAC'";
         if($result = mysqli_query($link, $str_query))
         {
            $row = mysqli_fetch_assoc($result);
            $conf_mac = 0;
            $conf_mac = (int)$row["conf_value"];
            mysqli_free_result($result);    //free useless result               
         }
          */
         //#002, change the above "if" statement to the following
         $conf_mac = 0;
         if($conf_arr = get_all_config_name_and_value($link, $guid))
         {
            $conf_mac = (int)$conf_arr["MAC"];
         }
         else // cannot find the table conf, return DB_ERROR
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

         ////////////////////
         // Get remain
         ////////////////////
         
         // #005, if Entie config exists, use the remain of super user
         define("ANTIE_FILE_NAME", "/usr/local/www/apache22/entie.conf");
         define("ADM_GUID", "00000000_0000_0000_0000_000000000000");
         if (file_exists(ANTIE_FILE_NAME))
            $entieFlag = 1;
         else
            $entieFlag = 0;

         if ($entieFlag == 1)
         {
            $str_query = "select remain from customer where GUID = '" . ADM_GUID . "'";
         }
         else
         {
            $str_query = "
               select remain 
               from customer 
               where GUID = '$guid'";
         }

         if($result = mysqli_query($link, $str_query))   //check remain query success
         {
            $row = mysqli_fetch_assoc($result);
            $remain = $row["remain"];
            mysqli_free_result($result);    //free useless result
         }
         else   //check remain query failure
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

         //////////////////////////////////////////////////////////////////////////
         // 2.1     if conf setting is NOT MAC
         //   2.1.1     if remain > 0 then create new and return entryID and remain - 1
         //////////////////////////////////////////////////////////////////////////
         if ($conf_mac == 0) // not mac_address
         {
            if($remain > 0)   //remain > 0
            {
               //----- Create a new entry -----
               $str_insert = "
                  insert into entry (GUID, hostname, domain_name, create_time, last_modified_time, status)
                  values('$guid', '$hostname', '$domain_name', '$date_time', '$date_time'," . WAITING .  ")";
               if(mysqli_query($link, $str_insert))   //create success
               {
                  //----- Query entryID -----
                  $str_query = "
                     select entryID 
                     from entry
                     where GUID = '$guid' 
                        and hostname = '$hostname' 
                        and domain_name = '$domain_name' 
                        and status = " . WAITING;
                  if($result = mysqli_query($link, $str_query))   //query success
                  {
                     $row = mysqli_fetch_assoc($result);
                     $result_entryid = $row["entryID"];
                     mysqli_free_result($result);    //free useless result                             
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
                  //#005 ----- Update remain -----
                  $remain = $remain - 1;
                  if ($entieFlag == 1)
                  {
                     $str_update = "
                        update customer 
                        set remain = $remain 
                        where GUID = '" . ADM_GUID . "'";
                  }
                  else
                  {
                     $str_update = "
                        update customer 
                        set remain = $remain 
                        where GUID = '$guid'";
                  }
                  if(mysqli_query($link, $str_update))   //update success
                  {
                     if($link)
                     {
                        mysqli_close($link);
                        $link = 0;
                     }
                     //echo "entry id = " . $result_entryid . "<br>"; 
                     echo $result_entryid;

                     return;
                  }
                  else   //update failure
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
               else   //creat failure
               {
                  if($link)  //release sql connection
                  {
                     mysqli_close($link);
                     $link = 0;
                  }
                  sleep(DELAY_SEC);
                  echo DB_ERROR;

                  return;
               }
            }
            else   //remain <= 0
            {
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               sleep(DELAY_SEC);
               echo EMPTY_REMAIN;

               return;
            }
         } // end of 2.1, if conf_mac == 0
         else if ($conf_mac == 1)
         {
            ////////////////////////////////////////////////////////////
            //   2.1.2   else (without check remain > 0 or not), check MAC in the list (check $mac, if it is an empty string, return ERROR
            ////////////////////////////////////////////////////////////
            if ($mac == "")
            {
               echo SYMBOL_ERROR_MAC;

               return;
            }

            $str_query = "
               select *  
               from macAddress 
               where GUID = '$guid' 
                  and hostname = '$hostname' 
                  and domain_name = '$domain_name' 
                  and (MAC1='$mac' or MAC2='$mac' or MAC3='$mac' or MAC4='$mac')";
            if($result = mysqli_query($link, $str_query))   //check remain query success
            {
               $mac_found = mysqli_num_rows($result);
               mysqli_free_result($result);    //free useless result
            }
            else   //check remain query failure
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

            ////////////////////////////////////////////////////////////
            //   2.1.2.1   if exist => create new and return entryID
            ////////////////////////////////////////////////////////////
            if ($mac_found > 0)
            {
               //----- Create a new entry -----
               $str_insert = "
                  insert into entry (GUID, hostname, domain_name, create_time, last_modified_time, status)
                  values('$guid', '$hostname', '$domain_name', '$date_time', '$date_time'," . WAITING .  ")";
               if(mysqli_query($link, $str_insert))   //create success
               {
                  //----- Query entryID -----
                  $str_query = "
                     select entryID 
                     from entry
                     where GUID = '$guid' 
                        and hostname = '$hostname' 
                        and domain_name = '$domain_name' 
                        and status = " . WAITING;
                  if($result = mysqli_query($link, $str_query))   //query success
                  {
                     $row = mysqli_fetch_assoc($result);
                     $result_entryid = $row["entryID"];
                     mysqli_free_result($result);    //free useless result                             
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
               }
               else   //creat failure
               {
                  if($link)  //release sql connection
                  {
                     mysqli_close($link);
                     $link = 0;
                  }
                  sleep(DELAY_SEC);
                  echo DB_ERROR;

                  return;
               }
               // return the new entryID
               echo $result_entryid;
            }
            ////////////////////////////////////////////////////////////
            //   2.1.2.2   if not exist and remain > 1 => append MAC to the list, create new and return entryID and remain - 1
            ////////////////////////////////////////////////////////////
            else if ($remain > 1)
            {
               $remain_decrease = 0; //#003 add
               ////////////////////////////////////////////////////////////
               //(1)Insert new row OR (2)append $mac to MAC1 or MAC2 or MAC3 or MAC4 to the existing row
               ////////////////////////////////////////////////////////////
               $str_query = "
                  select * 
                  from macAddress
                  where GUID = '$guid' 
                     and hostname = '$hostname' 
                     and domain_name = '$domain_name'";
               if($result = mysqli_query($link, $str_query))   //query success
               {
                  if (mysqli_num_rows($result) == 0)
                  {
                     mysqli_free_result($result);    //free useless result
                     // (1)Insert new row
                     $str_insert = "
                        insert into macAddress (GUID, MAC1, hostname, domain_name, create_time, last_modified_time, status)
                        values('$guid', '$mac', '$hostname', '$domain_name', '$date_time', '$date_time',1)";
                     $remain_decrease = 1; //#003 add
                     if(mysqli_query($link, $str_insert))   //create success
                     {
                        // do nothing
                     }
                     else   //creat failure
                     {
                        if($link)  //release sql connection
                        {
                           mysqli_close($link);
                           $link = 0;
                        }
                        sleep(DELAY_SEC);
                        echo DB_ERROR;

                        return;
                     }
                  }
                  else  // GUID + hostname + domain_name doesn't exist
                  {
                     // (2)Append $mac to MAC1 or MAC2 or MAC3 or MAC4
                     $remain_decrease = 0; //#003 add
                     $row = mysqli_fetch_assoc($result);
                     $MAC1 = $row["MAC1"];
                     $MAC2 = $row["MAC2"];
                     $MAC3 = $row["MAC3"];
                     $MAC4 = $row["MAC4"];
                     mysqli_free_result($result);    //free useless result
                     if ($MAC1 == "")
                     {
                        $update_mac_str = "MAC1='$mac'";
                     }
                     else if ($MAC2 == "")
                     {
                        $update_mac_str = "MAC2='$mac'";
                     }
                     else if ($MAC3 == "")
                     {
                        $update_mac_str = "MAC3='$mac'";
                     }
                     else // if there are too many network cards, replace $mac to MAC4 directly
                     {
                        $update_mac_str = "MAC4='$mac'";
                     }
                     $str_update = "
                        update macAddress 
                           set last_modified_time='$date_time', $update_mac_str
                           where GUID = '$guid' AND hostname='$hostname' AND domain_name='$domain_name'";
                     if(mysqli_query($link, $str_update))   //update success
                     {
                        // do nothing
                     }
                     else   //update failure
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
               
               //----- Create a new entry -----
               $str_insert = "
                  insert into entry (GUID, hostname, domain_name, create_time, last_modified_time, status)
                  values('$guid', '$hostname', '$domain_name', '$date_time', '$date_time'," . WAITING .  ")";
               if(mysqli_query($link, $str_insert))   //create success
               {
                  //----- Query entryID -----
                  $str_query = "
                     select entryID 
                     from entry
                     where GUID = '$guid' 
                        and hostname = '$hostname' 
                        and domain_name = '$domain_name' 
                        and status = " . WAITING;
                  if($result = mysqli_query($link, $str_query))   //query success
                  {
                     $row = mysqli_fetch_assoc($result);
                     $result_entryid = $row["entryID"];
                     mysqli_free_result($result);    //free useless result                             
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
                  //----- Update remain -----
                  //$remain = $remain - 1;
                  $remain = $remain - $remain_decrease;  // #003

                  // #005 
                  if ($entieFlag == 1)
                  {
                     $str_update = "
                        update customer 
                        set remain = $remain 
                        where GUID = '" . ADM_GUID . "'";
                  }
                  else
                  {
                     $str_update = "
                        update customer 
                        set remain = $remain 
                        where GUID = '$guid'";
                  }
                  if(mysqli_query($link, $str_update))   //update success
                  {
                     if($link)
                     {
                        mysqli_close($link);
                        $link = 0;
                     }
                     //echo "entry id = " . $result_entryid . "<br>"; 
                     echo $result_entryid;

                     return;
                  }
                  else   //update failure
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
               else   //creat failure
               {
                  if($link)  //release sql connection
                  {
                     mysqli_close($link);
                     $link = 0;
                  }
                  sleep(DELAY_SEC);
                  echo DB_ERROR;

                  return;
               }
            }
            ////////////////////////////////////////////////////////////
            // If remain=1, still need to check if domain+hostname+GUID exist or not. If exist, still append mac
            // #004
            ////////////////////////////////////////////////////////////
            else if ($remain == 1)
            {
               $str_query = "
                  select * 
                  from macAddress
                  where GUID = '$guid' 
                     and hostname = '$hostname' 
                     and domain_name = '$domain_name'";
               if($result = mysqli_query($link, $str_query))   //query success
               {
                  if (mysqli_num_rows($result) > 0) //do append
                  {
                     // Append $mac to MAC1 or MAC2 or MAC3 or MAC4
                     $remain_decrease = 0; //#003 add
                     $row = mysqli_fetch_assoc($result);
                     $MAC1 = $row["MAC1"];
                     $MAC2 = $row["MAC2"];
                     $MAC3 = $row["MAC3"];
                     $MAC4 = $row["MAC4"];
                     mysqli_free_result($result);    //free useless result
                     if ($MAC1 == "")
                     {
                        $update_mac_str = "MAC1='$mac'";
                     }
                     else if ($MAC2 == "")
                     {
                        $update_mac_str = "MAC2='$mac'";
                     }
                     else if ($MAC3 == "")
                     {
                        $update_mac_str = "MAC3='$mac'";
                     }
                     else // if there are too many network cards, replace $mac to MAC4 directly
                     {
                        $update_mac_str = "MAC4='$mac'";
                     }
                     $str_update = "
                        update macAddress 
                           set last_modified_time='$date_time', $update_mac_str
                           where GUID = '$guid' AND hostname='$hostname' AND domain_name='$domain_name'";
                     if(mysqli_query($link, $str_update))   //update success
                     {
                        // do nothing
                     }
                     else   //update failure
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
                  else
                  {
                     if($link)   //release sql connection
                     {
                        mysqli_close($link);
                        $link = 0;
                     }  
                     sleep(DELAY_SEC);
                     echo EMPTY_REMAIN;

                     return;
                  }
               }
               else
               {
                  if($link)  //release sql connection
                  {
                     mysqli_close($link);
                     $link = 0;
                  }
                  sleep(DELAY_SEC);
                  echo DB_ERROR;

                  return;
               }
            }
            ////////////////////////////////////////////////////////////
            //   2.1.2.3   if not exist and remain <= 1 => return entryID = error (must control remain>=1 to ensure the previous N clients still can do scanning)
            ////////////////////////////////////////////////////////////
            else
            {
               if($link)   //release sql connection
               {
                  mysqli_close($link);
                  $link = 0;
               }  
               sleep(DELAY_SEC);
               echo EMPTY_REMAIN;

               return;
            } // end of 2.1.2.3 
         } // end of else (with mac setting)
      } // end of else (don't have entryID)
   } // end of if (query success)
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
