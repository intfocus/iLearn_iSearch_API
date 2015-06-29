<?php
///////////////////////////////
//getFileList.php
//
// Provide query from TrustView, read domain+hostname+auth, output tsv file
// 2012/07/20 created by Phantom and Yaoan
//
///////////////////////////////
   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
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
   
   //$GUID = "8f44a8ab_5c6c_6232_cd4f_642761007428";
   header('Content-Type:application/vnd.ms-excel');
   //header('Content-Type:text/html;charset=utf-8');
   
   //define
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(STR_LENGTH, 50);
   define(EXTREME_TYPE_NUMBER, '7');                      //個資類型

   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(INPUT_ERROR, -2);
   define(DATA_DUPLICATE_ERROR, -3);
   define(DATA_NOT_FOUND_ERROR, -4);
   define(OUTPUT_ERROR, -5);
   define(OUTPUT_GEN_ERROR, -6);
   define(NO_DATA_ERROR, -7);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   
   //get data from client
   $domain_name;
   $hostname;
   $auth;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   $total_count=0;
   
   //data
   $domain_name = $_GET["domain"];
   $hostname = $_GET["hostname"];
   $auth = $_GET["auth"];

   ////////////////
   // 1. check input and check auth 
   ////////////////
   if ($domain_name === "" || $hostname === "" || $auth === "")
   {
      sleep(DELAY_SEC);
      echo INPUT_ERROR;
      return;
   }
   $tmp_str = "$domain_name${hostname}P-Marker";
   $tmp_auth = hash('md5',$tmp_str);
   if ($tmp_auth != $auth) 
   {
      sleep(DELAY_SEC);
      echo INPUT_ERROR;
      return;
   }
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   ////////////////
   // get data in identityFound with domain_name and hostname
   ///////////////
   $sql = "select GUID
              from identityFound 
              where domain_name ='$domain_name' and
                    hostname = '$hostname'
              group by GUID
          ";

   if($result = mysqli_query($link, $sql)){
      $count = mysqli_num_rows($result);
      if ($count > 1)
      {
         sleep(DELAY_SEC);
         echo DATA_DUPLICATE_ERROR;
         return;
      }
      else if ($count == 0)
      {
         sleep(DELAY_SEC);
         echo DATA_NOT_FOUND_ERROR;
         return;
      }
      $row = mysqli_fetch_assoc($result);
      $guid = $row["GUID"];
      mysqli_free_result($result);
   }
   else{
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }

   //----- query risk category by guid -----
   $str_query = "
      select *
      from riskCategory
      where GUID = '$guid'";
   if($result = mysqli_query($link, $str_query))   //query riskCategory success
   {
      //----- riskCategory have this GUID -----
      if($row = mysqli_fetch_assoc($result))
      {
         $risk_low = $row["low"];
         $risk_high = $row["high"];
         $risk_extreme = $row["extreme"];
         $risk_extreme_type_num = $row["extreme_type_num"];
         $risk_extreme_type = $row["extreme_type"];
         mysqli_free_result($result);    //free useless result
         
         unset($row);    //clean array
      }
      //----- riskCategory doesn't have this GUID -----
      else
      {
         mysqli_free_result($result);    //free useless result
         $str_query = "
            select *
            from riskCategory
            where GUID = '" . DEFAULT_GUID . "'";
         if($result = mysqli_query($link, $str_query))   //query riskCategory by default success
         {
            $row = mysqli_fetch_assoc($result);
            $risk_low = $row["low"];
            $risk_high = $row["high"];
            $risk_extreme = $row["extreme"];
            $risk_extreme_type_num = $row["extreme_type_num"];
            $risk_extreme_type = $row["extreme_type"];
            mysqli_free_result($result);    //free useless result
         }
         else   //query riskCategory by default failure
         {
            if($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            sleep(DELAY_SEC);
            echo -__LINE__;

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
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }

   /////////////////
   // 4. prepare middle input file and then call perl
   /////////////////
   $arr_identity_type = explode(",", "0,1,2,3,4,5,6");
   $name_pid = getmypid();
   $now_time = date("Y-m-d H:i:s");
   $name_timestamp = date("YmdHis", strtotime($now_time));    
   $iFound_file = dirname(__FILE__) . "/${name_pid}_$name_timestamp";

   $f = fopen($iFound_file,"w");
   fwrite($f,"$guid\n");
   fwrite($f,"$risk_low\t$risk_high\t$risk_extreme\t$risk_extreme_type_num\t$risk_extreme_type\n");
   $type = implode("\t",$arr_identity_type);
   fwrite($f,"$type\n");

   $sql = "
      select XMLID,create_time,ip,hostname,domain_name,employee_name,department,count0,count1,count2,count3,count4,count5,count6,start_time,end_time,total_file 
      from identityFound as iFound
      where iFound.GUID = '$guid' and
         iFound.hostname = '$hostname' and
         iFound.domain_name = '$domain_name' and 
         status = 0 and 
      iFound.create_time = (
         select max(tmp.create_time)
         from identityFound as tmp
         where tmp.GUID = iFound.GUID and
         tmp.hostname = iFound.hostname and
         tmp.domain_name = iFound.domain_name and
         tmp.login_name = iFound.login_name and
         tmp.department = iFound.department and
         tmp.employee_name = iFound.employee_name and
         tmp.status = iFound.status
      )
   ";

   if($result = mysqli_query($link, $sql)){

      $computer_count = 0;
      $pdata_computer_count = 0;

      $nExtremeFile = 0;
      $nHighFile = 0;
      $nMediumFile = 0;
      $nLowFile = 0;

      $nExtremeData = 0;
      $nHighData = 0;
      $nMediumData = 0;
      $nLowData = 0;

      foreach ($arr_identity_type as $type){
         $arr_sum_risk_count[$type] = 0; 
      }

      while($row = mysqli_fetch_assoc($result)){

         $xmlid = $row["XMLID"];
         $create_time = $row["create_time"];
         $ip =  $row["ip"];
         $hostname = $row["hostname"];
         $domain_name = $row["domain_name"];
         $login_name = $row["employee_name"];
         $department = $row["department"];
         $start_time = $row["start_time"];
         $end_time = $row["end_time"];
         $total_file = $row["total_file"];

         $computer_count++;
         $pdata_flag = 0;

         foreach ($arr_identity_type as $type){
            if($row["count$type"] > 0){
               $pdata_flag = 1; 
               $arr_sum_risk_count[$type] += $row["count$type"];
            }
         }

         if($pdata_flag){
            $pdata_computer_count++;
            $spent_time = 0;
            fwrite($f,"$xmlid\t$create_time\t$ip\t$hostname\t$domain_name\t$login_name\t$department\t$start_time\t$end_time\t$spent_time\t$total_file\n");
         } 
      }

      fclose($f);
   }
   else   //query failure
   {
      if($link)
      {
         mysqli_close($link);
         $link = 0;
      } 
      sleep(DELAY_SEC);
      echo -__LINE__;

      return;
   }
   if ($link){
      mysqli_close($link);
   }

   $last_line = system("$working_path/genFileList.pl $iFound_file");
   $out = "${iFound_file}.out";
   unlink($iFound_file);

   if($last_line === ""){
      if(@($buf = file_get_contents($out)) == FALSE){
         sleep(DELAY_SEC);
         echo OUTPUT_ERROR;
         unlink($out);
         return;
      }
   }
   else if(strncasecmp($last_line,"error",5) == 0){
      sleep(DELAY_SEC);
      echo OUTPUT_GEN_ERROR;
      unlink($out);
      return;
   }
   else if(strncasecmp($last_line,"no data",7) == 0){
      sleep(DELAY_SEC);
      echo NO_DATA_ERROR;
      unlink($out);
      return;
   }

   unlink($out);
   sleep(1);
   echo $buf;

   return;

?>
