<?php
/********************************************
 *showHtml.php
 *1. Check session and reportID
 *2. Get information(GUID) from DB by reportID
 *   2.1 if not found => delay and goto errExit
 *3. Check seeion guid and report's guid
 *   3.1 if not match => delay and goto errExit
 *4. Open report and response
 *5. errExit => read error page and response
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
      $ret =  -__LINE__;
      goto errExit;
   }
   define(REPORT_PATH, $report_path);
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(ERRORPAGE_HTML, "$working_path/tmpl/err_showHtml.tmpl");	
	define(USER_TRIAL, 0);
	define(USER_OFFICIAL, 1);   
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
	$status = 0;//added by Dylan 20120307
   
   $str_query;
   $result;
   $row;

   $str_html;
   $report;   
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
      $ret =  -__LINE__;
      goto errExit;
   }

   //----- query report information -----
   $str_query = "
      select *
      from report
      where reportID = $reportID
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //report id is not matched
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         $ret =  -__LINE__;
         goto errExit;
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
      $ret =  -__LINE__;
      goto errExit;
   }
	//added by Dylan 20120307
	//查詢status
	/////////////////////////////////
   //----- query report information -----
   $str_query = "
      select *
      from customer
      where GUID = '$guid'
      ";
   if($result = mysqli_query($link, $str_query))
   {
      if(!mysqli_num_rows($result))    //report id is not matched
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         $ret =  -__LINE__;
         goto errExit;
      }
      $row = mysqli_fetch_assoc($result);   
      $status = $row["status"];		
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
      $ret =  -__LINE__;
      goto errExit;
   }
   if($link)
   {
      mysqli_close($link);
      $link = 0;
   } 
	//end of added by Dylan 20120307 
   //--------------------------------------------------
   //----- 3. Check seeion guid and report's guid -----
   //--------------------------------------------------
   
   if(strcmp($guid, $reportGuid))
   {
      $ret =  -__LINE__;
      goto errExit;
   }

   //-----------------------------------
   //----- 4. Open html and return -----
   //-----------------------------------
	//added by Dylan 20120307
	//if login account is client => file name = XXX_trial
	/////////////////////////////////////
   if($status == USER_TRIAL)
	{
		$fileName .= "_trial";
   }
	else if($status != USER_OFFICIAL)
	{
	   $fileName .= "_error";
	}
   //----- ie browser -----
   if(strpos($_SERVER["HTTP_USER_AGENT"], "MSIE"))
   {
      $report = REPORT_PATH . "/$reportGuid/$fileFolder/$fileName.mht";
      if(file_exists($report))   //report exists
      {
         if(!@($str_html = file_get_contents($report)))   
         {
            //read file error
            $ret =  -__LINE__;
            goto errExit;
         } 
         header("Content-Type: message/rfc822");
      }  
      else
      {
         $ret =  -__LINE__;
         goto errExit;
      }   
   }
   //----- other browsers -----
   else
   {
      $report = REPORT_PATH . "/$reportGuid/$fileFolder/$fileName.html";
      if(file_exists($report))   //report exists
      {
         if(!@($str_html = file_get_contents($report)))   
         {
            //read file error
            $ret =  -__LINE__;
            goto errExit;
         }        
      }  
      else
      {
         $ret =  -__LINE__;
         goto errExit;
      }   
   }
   echo $str_html;

   return;

   errExit:
   //------ Read error page -----
   if(file_exists(ERRORPAGE_HTML))
   {
      if(!@($str_html = file_get_contents(ERRORPAGE_HTML))) 
      {
            //read file error
            $ret =  -__LINE__;
      }
   }   
   else
   {
      $ret =  -__LINE__;
   } 
   sleep(DELAY_SEC);
   $str_html = str_replace('$$err_code$$', $ret, $str_html);
   echo $str_html;
   
   return;
?>
