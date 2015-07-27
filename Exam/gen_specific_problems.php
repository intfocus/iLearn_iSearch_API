<?php
   require_once("../Problem/Problems_utility.php");
   require_once("Exams_utility.php");
   
   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }

   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                          //illegal char
   define("STR_LENGTH", 50);
   define("SEARCH_SIZE", 1000);                             //上限1000笔数
   define("PAGE_SIZE", 100);                                //设置列表显示笔数

   //return value
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);

   //get data from client
   $cmd;
   $ProbName;
   $Status;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   //1.get information from client
   
   function check_number($check_str)
   {
      if ($check_str == "")
      {
         $check_str = 0;
      }
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str < 0)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   function check_functions_id($check_str)
   {
      if (preg_match("/([0-9]+,)*([0-9])*/", $check_str))
      {
         return true;
      }
      else
      {
         return false;
      }
   }
   
   function get_functions_id($func_id_str)
   {
      return preg_split("/,/", $func_id_str);
   }

   if (!isset($_GET["is_obu_require"]))
   {
      $is_obu_require = false;
   }
   else
   {
      $is_obu_require = $_GET["is_obu_require"];
   }
   
   if (!isset($_GET["product_functions_id"]))
   {
      $product_functions_id = array();
   }
   else
   {
      $product_functions_id = $_GET["product_functions_id"];
   }
   if (!isset($_GET["adapation_functions_id"]))
   {
      $adapation_functions_id = array();
   }
   else
   {
      $adapation_functions_id = $_GET["adapation_functions_id"];
   }

   $require_function_id = 0;
   if (!isset($_GET["require_function_id"]))
   {
      echo SYMBOL_ERROR;
      return;
   }
   else
   {
      $require_function_id = $_GET["require_function_id"];
   }

   //link
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   

   // get obu require id
   $func_type = FUNCTION_PRODUCT;
   $str_query = "Select * from functions where FunctionType=$func_type AND FunctionName='OBL'";
   if($result = mysqli_query($link, $str_query))
   {
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         $row = mysqli_fetch_assoc($result);
         $obu_id = $row["FunctionId"];
      }
   }

   if (!isset($obu_id))
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   if ($is_obu_require == 1)
   {
      $str_query1 = "select * from problems where Status = 1 AND (ProblemCategory like ',%$obu_id%,')";
   }
   else
   {
      $str_query1 = "select * from problems where Status = 1 AND (ProblemCategory NOT like ',%$obu_id%,')";
   }

   //----- query -----
   //***Step16 页面搜索SQl语句 起始
   if (count($product_functions_id) == 0 && count($adapation_functions_id) == 0)
   {
      $str_query1 = $str_query1." AND (ProblemCategory like ',%$require_function_id%,')";
   }
   else
   {
      $str_query1 = $str_query1." AND (ProblemCategory like ',%$require_function_id%,') AND (" ;
      for ($i=0; $i<count($product_functions_id); $i++)   
      {
         if ($i == (count($product_functions_id) - 1))
         {
            $str_query1 = $str_query1."ProblemCategory like ',%$product_functions_id[$i]%,')";
         }
         else
         {
            $str_query1 = $str_query1."ProblemCategory like ',%$product_functions_id[$i]%,'"." OR ";
         }
      }

      if (count($adapation_functions_id) > 0)
      {
         $str_query1 = $str_query1." AND (";
         for ($i=0; $i<count($adapation_functions_id); $i++)   
         {
            if ($i == (count($adapation_functions_id) - 1))
            {
               $str_query1 = $str_query1."ProblemCategory like ',%$adapation_functions_id[$i]%,')";
            }
            else
            {
               $str_query1 = $str_query1."ProblemCategory like ',%$adapation_functions_id[$i]%,'"." OR ";
            }
         }
      }
   }

   $str_query1 = $str_query1." ORDER BY CreatedTime DESC";

   //***Step16 页面搜索SQl语句 结束
   /////////////////////
   // prepare the SQL command and query DB
   /////////////////////
   $SelectedProbs = array();
   if($result = mysqli_query($link, $str_query1)){
      $row_number = mysqli_num_rows($result);
      if ($row_number == 0)
      {
         echo ERR_NOT_ENOUGH_PROBLEM;
         return;
      }

      for ($i=0; $i<$row_number; $i++)
      {
         $row = mysqli_fetch_assoc($result);
         array_push($SelectedProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], timestamp_to_datetime_with_only_date(strtotime($row["CreatedTime"]))));
      }
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   $result = array("problems"=> $SelectedProbs);
   echo json_encode($result);
   return;   
?>
