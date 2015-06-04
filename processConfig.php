<?php

//////////////////////////////////
// 20130624, created by Odie
//    Process the value from setConfig.php and return corrsponding value for ajax
//
//////////////////////////////////

define(DB_CONF, "/usr/local/www/apache22/DB.conf");
define(CONFIGFUNCTION_PHP, "/usr/local/www/apache22/data/configFunction.php");
define(DELAY_SEC, 3);
define(FILE_ERROR, -2);
define(CONF_LENGTH, 100);
   
if(file_exists(DB_CONF) && file_exists(CONFIGFUNCTION_PHP))
{
   include_once(DB_CONF);
   include_once(CONFIGFUNCTION_PHP);
}
else
{
   //sleep(DELAY_SEC);
   echo FILE_ERROR;
   return;
}

define(DB_HOST, $db_host);
define(ADMIN_ACCOUNT, $admin_account);
define(ADMIN_PASSWORD, $admin_password);
define(CONNECT_DB, $connect_db);
define(TIME_ZONE, "Asia/Taipei");
define(SUCCESS, 0);
define(DB_ERROR, -1);
define(GUID_ERROR, -2);
define(SYMBOL_ERROR, -3);
define(UPDATE_ERROR, -4);

// check the command, must be show or modify
if($_POST["cmd"] == "show" || $_POST["cmd"] == "modify"){
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   if(!$link){  //connect to server failure    
      //sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   // check if GUID valid
   $guid = $_POST["guid"];
   if(!get_magic_quotes_gpc())
      $guid = mysql_real_escape_string($guid);
   $name = check_guid($link, $guid);
   if(!$name){
      mysqli_close($link);
      //sleepy(DELAY_SEC);
      echo GUID_ERROR;
      return;
   }
}
else{
   mysqli_close($link);
   //sleepy(DELAY_SEC);
   echo SYMBOL_ERROR;
   return;
}

// if command is modify, then update DB
if($_POST["cmd"] == "modify"){
   $modify_arr = $_POST["conf"];
   if(set_all_config_by_name($link, $guid, $modify_arr) == -1){
      mysqli_close($link);
      echo UPDATE_ERROR;
      return;
   }
}

// query DB and get config name and value
$ret = "公司名稱: ". $name. "<br /><form name=\"conf\"><table><tr><td>config name</td><td>config value</td><td>new value</td></tr>";
$config_arr = get_all_config_name_and_value($link, $guid);
if($config_arr){
   foreach($config_arr as $key => $value){
      $ret = $ret. "<tr><td>". $key. "</td><td>". $value. "</td><td><input type=\"text\" name=\"". $key ."\" /></td></tr>";
   }
}
//$ret = $ret. "</table><input type=\"text\" name=\"hidden\" hidden /></form>";
$ret = $ret. "</table></form>";
mysqli_close($link);
$ret = $ret. "<input type=\"button\" value=\"修改\" name=\"modifybutton\" onClick=\"modify();\">";
$ret = $ret. "<input type=\"button\" value=\"完成\" name=\"completebutton\" onClick=\"window.location.reload()\">";
echo $ret;
