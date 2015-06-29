<?php
/********************************************
 *downloadReport.php
 *1. Check session and reportID
 *2. Get information(GUID) from DB by reportID
 *   2.1 if not found => delay and return 404
 *3. Check seeion guid and report's guid
 *   3.1 if not match => delay and return 404
 *4. Download report
 *   2012/02/29 Jeffrey Chan
 ********************************************/
?>
<?php
   //----- Define -----
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf"); //account file name
   define(DELAY_SEC, 3);                                       //delay reply
   define(FILE_ERROR, -3);
   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      header("HTTP/1.1 404 Not Found");
      
      return;
   }
   define(REPORT_PATH, $report_path);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   //----- Return value -----
   define(SYMBOL_ERROR, -1);
   
   //----- Check number -----
   function check_number($check_str)
   {
      //----- check number -----
      if(!is_numeric($check_str))
      {

        return SYMBOL_ERROR; 
      }
   
      return $check_str;
   }
?>
<?php
   //----- Variable definition -----
   //get from client
   $guid;
   $reportID;
   $reportGuid;
   $fileFolder;
   $fileName;
   
   $str_query;
   $result;
   $row;

   $str_zip;
   $report;   
   $str_temp;   
   //-----------------------------------------
   //----- 1. Check session and reportID -----
   //-----------------------------------------
   
   //----- session check -----
   session_start();
   if(!session_is_registered("GUID"))  //check session
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if($_SESSION["GUID"] == "")
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $guid = $_SESSION["GUID"];
   session_write_close();
   //----- check reportID -----
   if(($reportID = check_number($_GET["reportID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC); 
      header("Location:main.php");
      exit();
   }

   //-----------------------------------------
   //----- 2. Check information from DB  -----
   //-----------------------------------------
   
   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)   //connect to server failure
   {
      sleep(DELAY_SEC);
      header("HTTP/1.1 404 Not Found");
      
      return;
   }

   //----- query report information -----
   $str_query = "
      select *
      from report
      where reportID = $reportID
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //have no matching report id
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         sleep(DELAY_SEC);
         header("HTTP/1.1 404 Not Found");
      
         return;
      }
      $row = mysqli_fetch_assoc($result);   
      $reportGuid = $row["GUID"];
      $fileFolder = $row["fileFolder"];
      $fileName = $row["fileName"];
      mysqli_free_result($result);
      unset($row);
   }
   else
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      header("HTTP/1.1 404 Not Found");
      
      return;
   }
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   }
   
   //--------------------------------------------------
   //----- 3. Check seeion guid and report's guid -----
   //--------------------------------------------------
   
   if(strcmp($guid, $reportGuid))
   {
      sleep(DELAY_SEC);
      header("HTTP/1.1 404 Not Found");
      
      return;
   }

   //------------------------------
   //----- 4. Download report -----
   //------------------------------

   $report = REPORT_PATH . "/$reportGuid/$fileFolder/$fileName.zip";
   if(file_exists($report))   //report exists
   {
      if(!@($str_zip = file_get_contents($report)))   
      {
         //read file error
         sleep(DELAY_SEC);
         header("HTTP/1.1 404 Not Found");
      
         return;         
      }       
      $fileLen = filesize($report); 
   }  
   else
   {
      sleep(DELAY_SEC);
      header("HTTP/1.1 404 Not Found");
      
      return;
   }   
   //header("Content-Type: application/zip");
   header("Pragma:");
   header("Content-Type: application/html");
   $str_temp = "Content-Disposition:attachment; filename=\"$fileName.zip\";";
   header($str_temp);
   //header("Content-Transfer-Encoding: binary");
   header("Content-Length: $fileLen");
   echo $str_zip; 
   
   return;
?>
