<?php

////////////////////////////////////
// 20130624, created by Odie
//    Some functions used to get and set config values.
//    The config value is stored in "customer" table with column named "conf".
//    it contains 100 digit, each digit reprensents some config value.
//    The meaning of each bit is stored in "conf" table.
//    "conf_name" stands for the name, 
//    and "conf_index" stands for the index of the config in the 100 digit string.
////////////////////////////////////

// check if the guid valid, return true if valid, false if not valid
function check_guid($link, $guid){
   if(gettype($link) != "object" || gettype($guid) != "string")
      return false;
   $query_cmd = "select * from customer where GUID='$guid'";
   $result = mysqli_query($link, $query_cmd);
   if($result && mysqli_num_rows($result) > 0){
      $row = mysqli_fetch_assoc($result);
      return $row["name"];
   }
   else
      return false;
}

// used for other functions only, do not call directly in other programs
// return an array with format {"config_name" => "index"(conf_index) } if success
// return false if fail
function get_config_name_and_index($link){
   // check parameter
   if(gettype($link) != "object")
      return false;
   
   $query_cmd = "select * from conf";
   $result = mysqli_query($link, $query_cmd);
   if($result){
      while($row = mysqli_fetch_assoc($result)){
         $conf_name = $row["conf_name"];
         $index = $row["conf_index"];
         $config[$conf_name] = $index;
      }
      return $config;
   }
   else
      return false;      
}

// used for other functions only, do not call directly in other programs
// return a string with conf value (100 digits) if success
// return false if fail
function get_config_string($link, $guid){
   // check parameter
   if(gettype($link) != "object" || gettype($guid) != "string")
      return false;

   $query_cmd = "select conf from customer where GUID='$guid'";
   $result = mysqli_query($link, $query_cmd);
   if($result){
      $row = mysqli_fetch_assoc($result);
      $conf_value = $row["conf"];
      return $conf_value;
   }
   else
      return false;
}

// return an array with format {"config_name" => "value"} if success
// return false if fail
function get_all_config_name_and_value($link, $guid){
   if(gettype($link) != "object" || gettype($guid) != "string")
      return false;

   $conf_string = get_config_string($link, $guid);
   $conf_array = get_config_name_and_index($link, $guid);
   $conf_return = array();
   if($conf_string && $conf_array){
      foreach($conf_array as $name => $value){
         $conf_return[$name] = $conf_string[$value];
      }
      return $conf_return;
   }
   return false;
}
   
// get the config value by config_name
// return 0 if success
// return -1 if fail
function get_config_by_name($link, $guid, $config_name){
   if(gettype($link) != "object" || gettype($guid) != "string")
      return -1;
   $config = get_config_name_and_index($link);
   if($config && array_key_exists($config_name, $config)){
      $index = $config[$config_name];
      $conf_value = get_config_string($link, $guid);
      if($conf_value){
         return $conf_value[$index];
      }
   }
   return -1;
}

// set the config value by config_name, value and update DB
// return 0 if success
// return -1 if fail
function set_config_by_name($link, $guid, $config_name, $value){
   if(gettype($link) != "object" || gettype($guid) != "string")
      return -1;
   $value_str = (string)$value;
   if(strlen($value_str) != 1)
      return -1;
   $config = get_config_name_and_index($link);
   if($config && array_key_exists($config_name, $config)){
      $index = $config[$config_name];
      $conf_value = get_config_string($link, $guid);
      if($conf_value){
         $conf_value[$index] = $value_str;
         $update_cmd = "update customer set conf='$conf_value' where GUID='$guid'";
         if(mysqli_query($link, $update_cmd)){
            return 0;
         }
      }
   }
   return -1;
}

// set the config value by array and update DB
// return 0 if success
// return -1 if fail
function set_all_config_by_name($link, $guid, $config_pair){
   if(gettype($link) != "object" || gettype($guid) != "string" || count($config_pair) == 0)
      return -1;
   $config = get_config_name_and_index($link);
   $conf_value = get_config_string($link, $guid);
   
   if($config && $conf_value){
      foreach($config_pair as $key => $value){
         if(strlen($value) != 1 || !array_key_exists($key, $config)){
            return -1;
         }
         $index = $config[$key];
         $conf_value[$index] = (string)$value;
      }
      $update_cmd = "update customer set conf='$conf_value' where GUID='$guid'";
      if(mysqli_query($link, $update_cmd)){
         return 0;
      }
   }
   return -1;
}

?>
