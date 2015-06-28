<?php
/**********************************************
 *validate.php
 *Query customer table by login_name, validcode and status = PAIDED or TRIAL 
 *1.if not found => delay and return 
 *2.if found =>
 *    2.1 receive GUID, remain, expire_time
 *    2.2 query all department names by GUID
 *        2.2.1 if have matching GUID then get all department names.
 *        2.2.2 if doesn't have matching GUID then get default GUID's department names.
 *    2.3 query risk category by GUID
 *        2.3.1 if have matching GUID then get risk category information.
 *        2.3.2 if doesn't have matching GUID then get default GUID's risk category information.
 *json outpute format:
 *    {"version":1, "flag":1, "GUID":"xxxxx", "remain":n,
 *        "expire_time":"yyyymmdd", "dep_names":[ "xxxx", "xxxx", ...], "low":n,
 *        "high":n, "extreme":n, "extreme_type_num":n, "extreme_type_str":"1,2,3,...", "uploadMask": 0 or 1}
 *
 *  2012/01/10 Jeffrey Chan
 *
 *  #001 2013/05/20  Phantom+Odie+Venser  add netDisk & removableDisk options
 *  #002 2013/06/04  Odie                 add keyword options in JSON
 *  #003 2013/09/09  Odie                 add expressEnable and expressTimeout in JSON
 *  #004 2014/09/03  Phantom              add systemScanDirEnabled, systemScanDirContent in JSON
 *  #005 2014/09/10  Phantom              add whiteListContent in JSON
 *  #006 2014/11/13  Odie                 Entie customization
 *                                        1. decrease remain from superuser rather than from each branch
 *                                        2. add mysqli_real_escape_string() to prevent SQL injection
 **********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");    //account file name
   define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php"); //#004 add
   define(SYSTEMSCANDIR_PATH, "/usr/local/www/apache22/data/upload_old"); //#004 add
   define(DELAY_SEC, 3);                                          //delay reply
   define(FILE_ERROR, -4);
   //----- Read connect information from DB.conf -----
   if (file_exists(FILE_NAME) && file_exists(CONFIGFUNCTION_PHP))
   {
      include(FILE_NAME);
      include(CONFIGFUNCTION_PHP);
   }
   else
   {
      $arr_json["flag"] = FILE_ERROR;
      sleep(DELAY_SEC);
      echo json_encode($arr_json);

      return;
   }
   define(ILLEGAL_CHAR, "'-<>");                           //illegal char
   define(ILLEGAL_VALIDCODE_CHAR, "'-");
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(VERSION, 1.0);                                   //program version
   define(FLAG_SUCCESS, 1);                                //success flag
   define(TIME_ZONE, "Asia/Taipei");
   define(STR_LENGTH, 50);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //return value
   define(DB_ERROR, -1);
   define(NOT_FOUND, -2);
   define(SYMBOL_ERROR, -3);
   //status
   define(PAIDED, 1);
   define(TRIAL, 0);
   define("ADM_GUID", "00000000_0000_0000_0000_000000000000");

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

   //----- Check valid code -----
   function checkValidCode($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {

         return SYMBOL_ERROR;
      }
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_VALIDCODE_CHAR) == true)
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
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "login"))
      {

         return SYMBOL_ERROR;
      }

      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   $arr_json;          //array, for json output format
   $arr_dep_names;     //array, all department names
   $cmd;               //get command from client
   $login_name;        //get login name from client
   $validcode;         //get validcode from client
   $guid;              //guid from customer table
   $name;              //name from customer table
   $remain;            //remain from customer table
   $expire_time;       //expire_time from customer table
   $uploadMask;        //upload_mask from customer table
   $low;               //low from risk category table
   $high;              //high from risk category table
   $extreme;           //extreme from risk category table
   $extreme_type_num;  //extreme type number from risk category table
   $extreme_type;  //extreme type from risk category table
   $temp;              
   $flag;
   $link;              //connect to mysql
   $str_query;         //query command string
   $result;            //result object, receive query result from mysql
   $row;               //array, put result into an array
   $row_dep;           //array, put department result into an array
   $row_risk;          //array, put riskCategory result into an array
   $netDisk;           //#001, for network disk option
   $removableDisk;     //#001, for removable disk option

   //---- Creat Json used array -----
   $arr_json = array("version" => VERSION, "flag" => 0, "GUID" => "0", "name" => "0", "remain" => 0, "expire_time" => "0");
   //----- Check receive information from client -----
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      $arr_json["flag"] = SYMBOL_ERROR;
      sleep(DELAY_SEC);
      echo json_encode($arr_json);

      return;
   }
   if(($login_name = check($_GET["login_name"])) == SYMBOL_ERROR)
   {
      $arr_json["flag"] = SYMBOL_ERROR;
      sleep(DELAY_SEC);
      echo json_encode($arr_json);

      return;
   }
   if(($validcode = checkValidCode($_GET["validcode"])) == SYMBOL_ERROR)
   {
      $arr_json["flag"] = SYMBOL_ERROR;
      sleep(DELAY_SEC);
      echo json_encode($arr_json);

      return;
   }
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      $arr_json["flag"] = DB_ERROR;
      sleep(DELAY_SEC);
      echo json_encode($arr_json);

      return;
   }

   // #006
   $login_name = mysqli_real_escape_string($link, $login_name);
   $validcode = mysqli_real_escape_string($link, $validcode);

   //----- Query customer table by login_name, validcode and status = PAIDED or TRIAL -----
   $str_query = "
      select * 
      from customer 
      where login_name = '$login_name' 
         and validcode = '$validcode' 
         and (status = " . PAIDED . " or status = " . TRIAL . ")";
   if($result = mysqli_query($link, $str_query))   //query customer table success
   {
      $row = mysqli_fetch_assoc($result);
      $guid = $row["GUID"];
      $name = $row["name"];
      $remain = (int)$row["remain"];
      $expire_time = $row["expire_time"];
      $uploadMask = $row["uploadMask"];
      date_default_timezone_set(TIME_ZONE);
      $temp = strtotime($expire_time);
      $expire_time = date("Ymd", $temp);
      $netDisk = $row["netDisk"]; //#001 add
      $removableDisk = $row["removableDisk"];      //#001 add
      $keyword1 = $row["keyword1"];                //#002 add
      $expressEnable = $row["expressEnable"];      //#003 add
      $expressTimeout = $row["expressTimeout"];    //#003 add
      $systemScanDirEnabled = get_config_by_name($link,$guid,"systemScanDirEnabled"); //#004 add
      mysqli_free_result($result);    //free useless result
      //----- doesn't have matching customer -----
      if(!$guid)
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         $arr_json["flag"] = NOT_FOUND;
         sleep(DELAY_SEC);
         echo json_encode($arr_json);

         return;
      }
      //----- Query department -----
      $str_query = "
         select * 
         from department 
         where GUID = '$guid'";
      if($result = mysqli_query($link, $str_query))   //query department success
      {
         $flag = 0;

         //----- department have this GUID -----
         while($row_dep = mysqli_fetch_assoc($result))
         {  
            $flag = 1;
            //----- add to department names array -----
            $arr_dep_names[] = $row_dep["dep_name"];
         } 
         mysqli_free_result($result);    //free useless result
         //----- department doesn't have this GUID -----
         if($flag == 0)
         {  
            $str_query = "
               select * 
               from department 
               where GUID = '" . DEFAULT_GUID . "'";
            if($result = mysqli_query($link, $str_query))    //query department by default guid success
            {

               while($row_dep = mysqli_fetch_assoc($result))
               {
                  //----- add to department names array -----
                  $arr_dep_names[] = $row_dep["dep_name"];
               }
               mysqli_free_result($result);
            }
            else    //query department by default guid failure
            {
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               $arr_json["flag"] = DB_ERROR;
               sleep(DELAY_SEC);
               echo json_encode($arr_json);

               return;
            }
         }
      } 
      else   //query department failure
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         $arr_json["flag"] = DB_ERROR;
         sleep(DELAY_SEC); 
         echo json_encode($arr_json);

         return;
      }
      //----- Query riskCategory -----
      $str_query = "
         select * 
         from riskCategory 
         where GUID = '$guid'";
      if($result = mysqli_query($link, $str_query))   //query riskCategory success
      {
         //----- riskCategory have this GUID -----
         if($row_risk = mysqli_fetch_assoc($result))
         {
            $low = (int)$row_risk["low"];
            $high = (int)$row_risk["high"];
            $extreme = (int)$row_risk["extreme"];
            $extreme_type_num = (int)$row_risk["extreme_type_num"];
            $extreme_type = $row_risk["extreme_type"];
            mysqli_free_result($result);    //free useless result
         }
         //----- riskCategory doesn't have this GUID -----
         else
         {
            mysqli_free_result($result);    //free useless result
            $str_query = "
               select * 
               from riskCategory 
               where GUID = '" . DEFAULT_GUID . "'";
            if($result = mysqli_query($link, $str_query))   //query riskCategory by default guid success
            {
               $row_risk = mysqli_fetch_assoc($result);
               $low = (int)$row_risk["low"];
               $high = (int)$row_risk["high"];
               $extreme = (int)$row_risk["extreme"];
               $extreme_type_num = (int)$row_risk["extreme_type_num"];
               $extreme_type = $row_risk["extreme_type"];
               mysqli_free_result($result);    //free useless result

            }
            else   //query riskCategory by default guid failure
            {   
               if($link)
               {
                  mysqli_close($link);
                  $link = 0;
               }
               $arr_json["flag"] = DB_ERROR;
               sleep(DELAY_SEC);
               echo json_encode($arr_json);

               return;
            }
         }
      }
      else   //query riskCategory failure
      {   
         if($link) 
         {
            mysqli_close($link);
            $link = 0;
         } 
         $arr_json["flag"] = DB_ERROR;
         sleep(DELAY_SEC);
         echo json_encode($arr_json);

         return;
      }
      
      //----- #006 begin -----
      //----- if Entie config exists, use the remain of super user ----
      define("ANTIE_FILE_NAME", "/usr/local/www/apache22/entie.conf");
      if (file_exists(ANTIE_FILE_NAME))
         $entieFlag = 1;
      else
         $entieFlag = 0;

      if ($entieFlag == 1)
      {
         $str_query = "select * from customer where GUID = '" . ADM_GUID . "'";
         if ($result = mysqli_query($link, $str_query))
         {
            $row = mysqli_fetch_assoc($result);
            $remain = (int)$row["remain"];
         }
      }
      //----- #006 end -----

      //----- output format -----
      $arr_json["flag"] = FLAG_SUCCESS;
      $arr_json["GUID"] = $guid;
      $arr_json["name"] = $name;
      $arr_json["remain"] = $remain;
      $arr_json["expire_time"] = $expire_time;
      $arr_json["dep_names"] = $arr_dep_names;
      $arr_json["low"] = $low;
      $arr_json["high"] = $high;
      $arr_json["extreme"] = $extreme;
      $arr_json["extreme_type_num"] = $extreme_type_num;
      $arr_json["extreme_type_str"] = $extreme_type;

      //#001, venser, add new parameter for network disk / usb disk
      $arr_json["net_disk"] = (int)$netDisk;
      $arr_json["removable_disk"] = (int)$removableDisk;
      
      //#002, add parameter for keyword
      $arr_json["keyword1"] = $keyword1;

      //#003, add parameters for express scan options
      $arr_json["expressEnable"] = (int)$expressEnable;
      $arr_json["expressTimeout"] = (int)$expressTimeout;

      //#004, add systemScanDirEnabled
      $arr_json["systemScanDirEnabled"] = (int)$systemScanDirEnabled;
      if ($systemScanDirEnabled == 1) {
         $systemScanDirPath = SYSTEMSCANDIR_PATH . "/$guid/systemScanDir.txt";
         $fp = fopen($systemScanDirPath,"r");
         $buf = "";
         if ($fp) {
            while(!feof($fp)){
               $buf = $buf . fgets($fp);
            } 
            fclose($fp);
            $arr_json["systemScanDirContent"] = urlencode($buf);
         }
         else
            $arr_json["systemScanDirContent"] = "";
      }
      else
         $arr_json["systemScanDirContent"] = "";

      //#005, add whiteListContent
      $whiteListPath = SYSTEMSCANDIR_PATH . "/$guid/whitelist.txt";
      $fp = fopen($whiteListPath,"r");
      $buf = "";
      if ($fp) {
         while(!feof($fp)){
            $buf = $buf . fgets($fp);
         }
         fclose($fp);
         $arr_json["whiteListContent"] = urlencode($buf);
      }
      else
         $arr_json["whiteListContent"] = "";

      // since pmarker console always consinder uploadMask as a last parameter, always insert new parameter before it
      $arr_json["uploadMask"] = (int)$uploadMask;
   }
   else   //query customer table failure
   {
      $arr_json["flag"] = DB_ERROR;        
      sleep(DELAY_SEC);    
   }
   echo json_encode($arr_json);
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }

   return;
?>
