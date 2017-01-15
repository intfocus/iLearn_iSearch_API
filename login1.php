<?php
//////////////////////////////////////////////
// #001  Phantom,Odie   2013-04-26  Add loginType parameter, 
//                                  if loginType=1 ==> Admin, will check customer table
//                                  if loginType=2 ==> User, will check userLogin table
//
// #002  Odie           2014-11-26  Add SQL escape to $login_name
   
   //----- Define -----
   // include_once("http.php");
   // include_once("wstrust.php");
   define("FILE_NAME", "./DB.conf"); //account file name
   define("DELAY_SEC", 3);                                       //delay reply
   define("FILE_ERROR", -3);
   //----- Read account and password from DB.conf -----
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
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("URL_PREFIX", $webui_link);
   define("ILLEGAL_CHAR", "'-;<>");                          //illegal char
   define("TIME_ZONE", "Asia/Taipei");
   define("VCODE_LENGTH", 29);             
   //return value
   define("DB_ERROR", -1);       
   define("EMPTY_REMAIN", -2);   
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_GUID", -4);
   define("SYMBOL_ERROR_HOSTNAME", -5);
   
   //////////////////////
   // Input validation
   //////////////////////
   function check($check_str)
   {
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
   $password = "";
   $username = "E99658600";
   $login_name = "E99658600";
   //SSO 
   //$password = '2Federate';

   //$url = "https://login.salesforce.com/services/Soap/u/24.0";
   // $url = "https://webssodev.secureaccess.takeda.com/idp/attrsvc.ssaml2";
//    
   // $body = "
      // <samlp:AuthnRequest xmlns:samlp='urn:oasis:names:tc:SAML:2.0:protocol'
                          // ID='s248735275d5542177e9a4fd021410177660b9c8be'
                          // Version='2.0'
                          // IssueInstant='2015-05-26T08:36:03Z'
                          // Destination='https://websso.secureaccess.takeda.com/idp/SSO.saml2'
                          // ForceAuthn='false'
                          // IsPassive='false'
                          // ProtocolBinding='urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
                          // AssertionConsumerServiceURL='http://tsa-china.takeda.com.cn'
                          // >
          // <saml:Issuer xmlns:saml='urn:oasis:names:tc:SAML:2.0:assertion'>IntFocus_tsa-china</saml:Issuer>
          // <samlp:NameIDPolicy xmlns:samlp='urn:oasis:names:tc:SAML:2.0:protocol'
                              // Format='urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'
                              // AllowCreate='true'
                              // />
          // <samlp:RequestedAuthnContext xmlns:samlp='urn:oasis:names:tc:SAML:2.0:protocol'
                                       // Comparison='minimum'
                                       // >
              // <saml:AuthnContextClassRef xmlns:saml='urn:oasis:names:tc:SAML:2.0:assertion'>urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified</saml:AuthnContextClassRef>
          // </samlp:RequestedAuthnContext>
      // </samlp:AuthnRequest>";
      // // $body = "
      // // <sfdc:login xmlns:sfdc='urn:partner.soap.sforce.com'>
         // // <sfdc:username>$username</sfdc:username>
         // // <sfdc:password>$password</sfdc:password>
      // // </sfdc:login>";
    // $result = HTTP::doSoap($url, '', $body, NULL, NULL, 'http://schemas.xmlsoap.org/soap/envelope/', 'text/xml');
    // print_r($result);
    // return;
   //$loginType = $_POST["loginType"]; //直接猜測 system admin or user, 不再用 loginType
   /* 
   if(($password = check($_POST["password"])) == SYMBOL_ERROR)
   {
     sleep(DELAY_SEC);
     header("Location:main.php?cmd=err");
     exit();
   }
   */
   /////////////////////////////
   //Dylan 20120307
   //encrypt password by md5
   ////////////////////////////
   $password = hash('md5', $password);
   //////////////////////
   // check login and password
   //////////////////////

   //----- Read account and password from DB.conf -----
   if(file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   //----- Connect to MySQL -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link)
   {  //connect to server failure
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   //----- Query entryID by GUID, hostname, domainname -----
   
   $login_name = mysqli_real_escape_string($link, $login_name);   #002
   
   $str_query1 = "select UserId, status, UserName from users where EmployeeId = '$login_name' and status=1";

   if($result = mysqli_query($link, $str_query1))
   {   //query success
      $row = mysqli_fetch_assoc($result);
      $rownum = mysqli_num_rows($result);
      
      if ($rownum > 0)
      {
         $uid = $row["UserId"];
         $status = $row["status"];
         $username = $row["UserName"];
         $loginType = 2;
         $timestr = date('Y/m/d H:i:s', time());
         $str_query2 = "Update Users set LastModifyTime='$timestr' where EmployeeId='$login_name';";
         mysqli_query($link, $str_query2); // no check, 失敗就算了, 只是修改 userLogin 裡面的 last_modify_time
         session_start();
         $_SESSION["GUID"] = $uid;
         $_SESSION["loginName"] = $login_name; //#001 Add
         $_SESSION["username"] = $username;
         session_write_close(); 
         header("Location:index.php");
         exit();
      }
      else 
      {
         if($link)
         {
            mysqli_close($link);
            $link = 0;   
         }
      }
   }
   else
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;   
      }
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
    
   //////////////////////
   // If failed, set session=empty, redirect to main.php
   //////////////////////
   session_start();
   $_SESSION["GUID"] = "";
   $_SESSION["GUID_ADM"] = "";
   $_SESSION["loginLevel"] = ""; //#001 Add
   $_SESSION["loginName"] = ""; //#001 Add
   session_write_close();
   sleep(DELAY_SEC);
   header("Location:main.php?cmd=err");
   exit();
?>
