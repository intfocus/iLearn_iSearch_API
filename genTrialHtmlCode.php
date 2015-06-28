<?php
//////////////////////////////////////////
//Dylan
//產生trial的html code
//////////////////////////////////////////
define(FILE_ERROR, -3);
define(N_OF_TOP, 10);
define(N_OF_TOP_PERSON, 10);
define(N_OF_KINDS, 7);

define(TEMPLATE_TRIAL_HTML, "reportDetailTrial.html");
define(HIDE_TEXT, "<font color = silver>隱藏</font>");

function genTrialHtmlCode($report_name, $create_time, $customer_name, $range_begin, $range_end, $computer_numbers, $arr_identity_type,
      $privacy_computer_numbers, $file_numbers, $data_numbers, $arr_sum_risk_count, $arr_extreme_type, $risk_extreme_num, $risk_extreme, $risk_high, $risk_low,
      $person_extreme_detail, $person_high_detail,$arr_extreme_detail, $arr_high_detail, $url_purchase)
{
   $i;
   $tempLength = 0;
   $reportName = $report_name;
   $createTime = $create_time;
   $companyName = $customer_name;   
   $timeBegin = $range_begin;
   $timeEnd = $range_end;      
   $computerCheckCount = $computer_numbers;
   $riskTypeNames = "";
   $dataString = "";
   $dataCellsString = "";
   $numOfHitComputer = $privacy_computer_numbers;
   $numOfTotalData = $file_numbers;
   $numOfPersonalData = $data_numbers;   
   $pattern = array("身分證" , "手機", "住址", "電子信箱", "信用卡", "姓名", "家用電話");
   $dataRiskCount = array(0=>$arr_sum_risk_count[0],1=>$arr_sum_risk_count[1],2=>$arr_sum_risk_count[2], 3=>$arr_sum_risk_count[3], 4=>$arr_sum_risk_count[4], 5=>$arr_sum_risk_count[5],6=>$arr_sum_risk_count[6]);   
   $dataKinds = array();
   $riskExtremeTypeNum = $risk_extreme_num ;
   $riskExtremeNum = $risk_extreme;
   $riskExtremeTypeNames = "";
   $riskHigh = $risk_high;
   $underRiskHigh = $riskHigh - 1;
   $riskLow = $risk_low;   
   $dataTop10Extreme = array();
   $dataTop10High = array();   
   $personTop10Extreme = array();
   $personTop10High = array();      
   $urlPurchase = "$url_purchase/";
   
   for($i = 0 ; $i < N_OF_KINDS ; $i++)
   {
      $dataKinds[$i] = array("pattern"=>"", "value"=>"");
   }
   /////////////////////////////////
   //個資類型資料字串設定
   ////////////////////////////////
   $i = 0;
   $tempLength = sizeof($arr_sum_risk_count);
   while (list($key, $val) = each($dataRiskCount))
   {
      //echo "$pattern[$key] $val,";
      if($val)
      {
         $dataCellsString .= "<span class=\"otherResult\"><span class=\"field\">" . 
            $pattern[$key] . "&nbsp; </span><span class=\"result\">" .
            number_format(intval($val)) . "筆&nbsp;</span></span>";
         $dataString .=  $pattern[$key] . "<span class=\"otherResult\">
            <span class=\"result\">" . number_format(intval($val)) . "筆</span></span>";
       if($i < $tempLength - 1)
      {
         $dataString .= "、"; 
      }
      }
      $i++;
   }  
   /////////////////////////////////
   //極高風險類型字串設定
   ////////////////////////////////   
   $i = 0;
   $tempLength = sizeof($arr_extreme_type);
   while (list($key, $val) = each($arr_extreme_type))
   {
      $riskExtremeTypeNames .= $pattern[$val];
      if($i < $tempLength - 1)
      {
         $riskExtremeTypeNames .= "、"; 
      }
      $i++;
   }
   /////////////////////////////////
   //客戶選擇風險類型字串
   ////////////////////////////////    
   $i = 0;   
   $tempLength = sizeof($arr_identity_type);
   while (list($key, $val) = each($arr_identity_type))
   {
      $riskTypeNames .= $pattern[$val];
      if($i < $tempLength - 1)
      {
         $riskTypeNames .= "、"; 
      }
      $i++;
   }   
   /////////////////////////////////
   //設定TOPN資料
   ////////////////////////////////   
   for($i = 0 ; $i < N_OF_TOP_PERSON ; $i++)
   {
      $personTop10Extreme[$i] = array("department"=>" ", "hostname"=>" ", "domain_name"=>" ", "login_name"=>" ", "totalFile"=>" ", "nFile"=>" ");
      $personTop10High[$i] = array("department"=>" ", "hostname"=>" ", "domain_name"=>" ", "login_name"=>" ", "totalFile"=>" ", "nFile"=>" ");
   } 
   for($i = 0 ; $i < N_OF_TOP ; $i++)
   {
      $dataTop10Extreme[$i] = array("department"=>" ", "hostname"=>" ", "domain_name"=>" ", "login_name"=>" ", "filePath"=>" ", "fileType"=>" ", "nFound"=>" ");
      $dataTop10High[$i] = array("department"=>" ", "hostname"=>" ", "domain_name"=>" ", "login_name"=>" ", "filePath"=>" ", "fileType"=>" ", "nFound"=>" ");
   }
   for($i = 0 ; $i < N_OF_TOP_PERSON ; $i++)
   {
      if($person_extreme_detail[$i])
      {
         while (list($key, $val) = each($person_extreme_detail[$i]))
         {
            $personTop10Extreme[$i][$key] = $person_extreme_detail[$i][$key];
         }         
         $personTop10Extreme[$i]["domain_name"] .= "/" . $person_extreme_detail[$i]["hostname"];         
      }
   }   
   for($i = 0 ; $i < N_OF_TOP_PERSON ; $i++)
   {
      if($person_high_detail[$i])
      {   
         while (list($key, $val) = each($person_high_detail[$i]))
         {
            $personTop10High[$i][$key] = $person_high_detail[$i][$key];
         }
         $personTop10High[$i]["domain_name"] .= "/" . $personTop10High[$i]["hostname"];
      }
   }      
   for($i = 0 ; $i < N_OF_TOP ; $i++)
   {
      if($arr_extreme_detail[$i])
      {
         while (list($key, $val) = each($arr_extreme_detail[$i]))
         {
            $dataTop10Extreme[$i][$key] = $arr_extreme_detail[$i][$key];
            if($key == "filePath")
            {
               $dataTop10Extreme[$i][$key] = HIDE_TEXT;;
            }          
         }         
         $dataTop10Extreme[$i]["domain_name"] .= "/" . $dataTop10Extreme[$i]["hostname"];         
      }
   }
   
   for($i = 0 ; $i < N_OF_TOP ; $i++)
   {
      if($arr_high_detail[$i])
      {   
         while (list($key, $val) = each($arr_high_detail[$i]))
         {
            $dataTop10High[$i][$key] = $arr_high_detail[$i][$key];
            if($key == "filePath")
            {
               $dataTop10High[$i][$key] = HIDE_TEXT;
            }
         }
         $dataTop10High[$i]["domain_name"] .= "/" . $dataTop10High[$i]["hostname"];
      }
   }   
   /////////////////////////////////
   //開啟樣本檔案
   //////////////////////////////// 
   if(!@($file = file_get_contents(TEMPLATE_TRIAL_HTML)))
   {
      return FILE_ERROR;
   }
   /////////////////////////////////
   //網頁字串替換
   ////////////////////////////////    
   $file = str_replace("\$reportName", $reportName, $file);   
   $file = str_replace("\$createTime", $createTime, $file);
   $file = str_replace("\$timeBegin", $timeBegin, $file);
   $file = str_replace("\$timeEnd", $timeEnd, $file);
   $file = str_replace("\$companyName", $companyName, $file);
   $file = str_replace("\$computerCheckCount", number_format(intval($computerCheckCount)), $file);
   $file = str_replace("\$timeBegin", $timeBegin, $file);
   $file = str_replace("\$timeEnd", $timeEnd, $file);
   $file = str_replace("\$companyName", $companyName, $file);
   $file = str_replace("\$riskTypeNames", $riskTypeNames, $file);     
   $file = str_replace("\$numOfHitComputer", number_format(intval($numOfHitComputer)), $file);     
   $file = str_replace("\$numOfTotalData", number_format(intval($numOfTotalData)), $file);     
   $file = str_replace("\$numOfPersonalData", number_format(intval($numOfPersonalData)), $file);
   $file = str_replace("\$dataString", $dataString, $file);
   $file = str_replace("\$dataCellsString", $dataCellsString, $file);
   $file = str_replace("\$riskExtremeTypeNum", number_format(intval($riskExtremeTypeNum)), $file);
   $file = str_replace("\$riskExtremeTypeNames", $riskExtremeTypeNames, $file);
   $file = str_replace("\$riskExtremeNum", number_format(intval($riskExtremeNum)), $file);
   $file = str_replace("\$riskHigh", number_format(intval($riskHigh)), $file);
   $file = str_replace("\$underRiskHigh", number_format(intval($underRiskHigh)), $file);   
   $file = str_replace("\$riskLow", number_format(intval($riskLow)), $file);  
   $file = str_replace("\$overRiskLow", number_format(intval($riskLow + 1)), $file);   
   $file = str_replace("\$urlPurchase", $urlPurchase, $file);   
   for($i = 0 ; $i < N_OF_KINDS ; $i++)
   {
      $file = str_replace("\$dataKinds[$i][\"pattern\"]", $dataKinds[$i]["pattern"], $file);
      $file = str_replace("\$dataKinds[$i][\"value\"]", $dataKinds[$i]["value"], $file);
   }
   for($i = 0 ; $i < N_OF_TOP_PERSON ; $i++)
   {
      while (list($key, $val) = each($personTop10Extreme[$i]))
      {
         if($key != "nFile" && $key != "totalFile")
         {
            $file = str_replace("\$personTop10Extreme[$i][$key]", $val, $file);
         }
         else
         {
            $file = str_replace("\$personTop10Extreme[$i][$key]", number_format(intval($val)), $file);
         }
      }
      while (list($key, $val) = each($personTop10High[$i]))
      {
         if($key != "nFile" && $key != "totalFile" )
         {      
            $file = str_replace("\$personTop10High[$i][$key]", $val, $file);
         }
         else
         {
            $file = str_replace("\$personTop10High[$i][$key]", number_format(intval($val)), $file);
         }
      }   
   }//end of for($i = 0 ; $i < N_OF_TOP_PERSON ; $i++)      
   for($i = 0 ; $i < N_OF_TOP ; $i++)
   {
      while (list($key, $val) = each($dataTop10Extreme[$i]))
      {
         if($key != "nFound")
         {
            $file = str_replace("\$dataTop10Extreme[$i][$key]", $val, $file);
         }
         else
         {
            $file = str_replace("\$dataTop10Extreme[$i][$key]", number_format(intval($val)), $file);
         }
      }
      while (list($key, $val) = each($dataTop10High[$i]))
      {
         if($key != "nFound")
         {      
            $file = str_replace("\$dataTop10High[$i][$key]", $val, $file);
         }
         else
         {
            $file = str_replace("\$dataTop10High[$i][$key]", number_format(intval($val)), $file);
         }
      }   
   }//end of for($i = 0 ; $i < N_OF_TOP ; $i++)     
   /////////////////////////////////
   //回傳網頁字串
   //////////////////////////////// 
   return $file;
   }
?>
