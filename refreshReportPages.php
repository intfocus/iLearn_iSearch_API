<?php
////////////////////////////////////
//refreshReportPage.php
//
//1.return string of the report page
// 
// #001 modified by Odie 2013/04/29
//      To support new feature: mutli-level admin
//      1. Add parameters for refreshReportPages(): $login_level, $login_name
//         admin => 1
//         user  => 2
//      2. If user, restrict the reports he can see
//
// #002 modified by Odie 2013/09/11
//      Add the name of the 8th type when displaying reports
////////////////////////////////////

define(DELAY_SEC, 3);
define(GUID_LENGTH, 36);
define(PARAMETER_ERROR, -2);
 
function refreshReportPages($link, $GUID, $login_level, $login_name)
{
   if(!$link || strlen($GUID) != GUID_LENGTH)
      return PARAMETER_ERROR;

   //define
   define(TIME_ZONE, "Asia/Taipei");                      //time zone
   define(PAGE_SIZE, 100);                                //page size
   define(EXTREME_TYPE_NUMBER, '8');                      //個資類型
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(SYMBOL_ERROR_REPORT_ID, -5);
   
   //status
   define(AVAILABLE, 0);
   define(DELETED, -1);   
  
   //msg
   //define(MSG_REPORT_1, "目前沒有任何報表，請點選&quot;<a>產生新的報表</a>&quot;");
      
   //query
   $link;
   $db_host; 
   $str_query;
   $connect_db;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   
   //page
   $page_default_no;        //預設頁數
   $page_size;              //每頁報表數
   $page_num;
   
   //report
   $rID;                    //報表編號
   $rNameW;                 //報表名稱
   $temp_time;
   $rTimeW;                 //產生日期
   $vHighW_file;            //極高風險-檔案
   $HighW_file;             //高風險-檔案
   $MediumW_file;           //中風險-檔案
   $LowW_file;              //低風險-檔案
   $totalW_file;            //檔案總數
   $vHighW_data;            //極高風險-個資
   $HighW_data;             //高風險-個資
   $MediumW_data;           //中風險-個資
   $LowW_data;              //低風險-個資
   $totalW_data;            //個資總數
   $rItemW;                 //掃描項目
   $rItemW_temp1;
   $rItemW_temp2;
   $rItemW_map = array("身分證", "手機號碼", "地址", "電子郵件地址", "信用卡號碼", "姓名", "市話號碼", "生日");  //掃描項目對應  #002, add the default name of type 8th: 生日
   $rItemW_default = array(0, 0, 0, 0, 0, 0, 0, 0);  //預設掃描項目
   $rItemW_default_temp;
   $temp_begin;
   $tRangeW_begin;          //產生區間-開始
   $temp_end;
   $tRangeW_end;            //產生區間-結束
   $cTotalW;                //電腦
   $i;
   $flag;

   //return page
   $return_string;

   //set time
   date_default_timezone_set(TIME_ZONE);  //set timezone
   $date_time = date("Y-m-d H:i:s");

   //link 
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   // #002 begin
   $str_query = "select type8_enable, type8_name from riskCategory where GUID = '$GUID'";
   if ($result = mysqli_query($link, $str_query))
   {
      if ($row = mysqli_fetch_assoc($result))
      {
         if ($row["type8_enable"] == 1)
            $rItemW_map[7] = $row["type8_name"];
      }
      mysqli_free_result($result);
   }
   else
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   // #002 end

   //----- query -----   
   // #001 if login_level == 2 (user), he can only view the reports he created
   if ($login_level == 1){
      $str_query = "
         select * 
         from report
         where GUID = '" . $GUID . "' and status =" . AVAILABLE .
         " order by create_time desc"; 
   }
   else if($login_level == 2){
      $str_query = "
         select * 
         from report
         where GUID = '" . $GUID . "' and status =" . AVAILABLE . " and owner_name ='". $login_name. 
         "' order by create_time desc"; 
   }
   if ($result = mysqli_query($link, $str_query))
   {
      $row_number = mysqli_num_rows($result);
      while ($row = mysqli_fetch_assoc($result))  //將資料存成array
      {
         $rID[] = $row["reportID"];
         $rNameW[] = $row["report_name"];
         //$fileFolder[] = $row["fileFolder"];
         //$fileName[] = $row["fileName"];
         $temp_time = $row["create_time"];
         $temp_time = strtotime($temp_time);
         $rTimeW[] = date("Y-m-d", $temp_time);
         $vHighW_file[] = $row["nExtremeFile"];
         $HighW_file[] = $row["nHighFile"];
         $MediumW_file[] = $row["nMediumFile"];
         $LowW_file[] = $row["nLowFile"];
         $vHighW_data[] = $row["nExtremeData"];
         $HighW_data[] = $row["nHighData"];
         $MediumW_data[] = $row["nMediumData"];
         $LowW_data[] = $row["nLowData"];                  
         $rItemW_temp1 = $row["identity_type"];  //掃描項目
         $rItemW_temp1 = explode(",", $rItemW_temp1);
         $rItemW_temp2 = "";
         foreach ($rItemW_temp1 as $index => $value)
         {
            if ($value >= 0 && $value <= EXTREME_TYPE_NUMBER)
            {
               if ($index == 0)
                  $rItemW_temp2 = $rItemW_temp2 . $rItemW_map[(int)$value];
               else
                  $rItemW_temp2 = $rItemW_temp2 . "," .$rItemW_map[(int)$value];
            }
         }                  
         $rItemW[] = $rItemW_temp2;
         $temp_begin = $row["range_begin"];
         $temp_begin = strtotime($temp_begin);
         $tRangeW_begin[] = date("Y-m-d", $temp_begin);
         $temp_end = $row["range_end"];
         $temp_end = strtotime($temp_end);
         $tRangeW_end[] = date("Y-m-d", $temp_end);
         $cTotalW[] = $row["computer_numbers"];                 
      }               
      mysqli_free_result($result); 
   }
   else
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;
      return;
   }  
   
   //----- Print Report Pages -----
   $return_string = "";

   $page_default_no = 1;
   $page_size = PAGE_SIZE;
   $page_num = (int)(($row_number - 1) / $page_size + 1);
   
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">"
                                   . "<input type=\"hidden\" id=report_no value=$row_number>"
                                   . "<input type=\"hidden\" name=page_no value=1>"
                                   . "<input type=\"hidden\" name=page_size value=" . $page_size . ">";
   if ($page_num > 1)
   {
     	for ($i = 0; $i < $page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"page";
         if ($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=page_begin_no_" . ($i + 1) . " OnClick=clickPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }      
   $return_string = $return_string . "</span>"
                                   . "<span class=\"btn new\" OnClick=\"newReportFunc();\">產生新的報表</span>"
                                   . "<span class=\"btn expandR\" OnClick=\"expandContentFunc();\">顯示過長內文</span>"
                                   . "</div>";                   
                                    
   //----- Print Report Tables -----
   if ($row_number == 0)
   {
      $return_string = $return_string . "<table id=\"report_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                      . "<colgroup>"
                                      . "<col class=\"rNameW\"/>"
                                      . "<col class=\"vHighW\"/>"
                                      . "<col class=\"highW\"/>"
                                      . "<col class=\"totalW\"/>"
                                      . "<col class=\"tRangeW\"/>"
                                      . "<col class=\"rItemW\"/>"
                                      . "<col class=\"cTotalW\"/>"
                                      . "<col class=\"rTimeW\"/>"
                                      . "<col class=\"actW\"/>"
                                      . "</colgroup>"
                                      . "<tr>"
                                      . "<th>報表名稱</th>"
                                      . "<th>極高風險</th>"
                                      . "<th>高風險</th>"
                                      . "<th>個資筆數 / 檔案總數</th>"
                                      . "<th>時間區間</th>"
                                      . "<th>掃描項目</th>"
                                      . "<th>電腦</th>"
                                      . "<th><span>產生日期</span></th>"
                                      . "<th>動作</th>"
                                      . "</tr>"
                                      . "<tr>"
                                      . "<td colspan=\"9\" class=\"empty\">目前沒有任何報表，請點選&quot;<a>產生新的報表</a>&quot;</td>"
                                      . "</tr>"
                                      . "</table>";
   }
   else
   {
      $i = 0;
      $page_no = 1;
      $page_count = 0;
      while ($i < $row_number)
      {
         //----- If No Data -----
         if ($page_count == 0)
         {
            $return_string = $return_string . "<div id=\"page" . $page_no . "\" ";
            if ($page_no == 1)
               $return_string = $return_string . "style=\"display:block;\"";
            else
               $return_string = $return_string . "style=\"display:none;\"";
            $return_string = $return_string . ">"
                                            . "<table id=\"report_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                            . "<colgroup>"
                                            . "<col class=\"rNameW\"/>"
                                            . "<col class=\"vHighW\"/>"
                                            . "<col class=\"highW\"/>"
                                            . "<col class=\"totalW\"/>"
                                            . "<col class=\"tRangeW\"/>"
                                            . "<col class=\"rItemW\"/>"
                                            . "<col class=\"cTotalW\"/>"
                                            . "<col class=\"rTimeW\"/>"
                                            . "<col class=\"actW\"/>"
                                            . "</colgroup>"
                                            . "<tr>"
                                            . "<th>報表名稱</th>"
                                            . "<th>極高風險</th>"
                                            . "<th>高風險</th>"
                                            . "<th>個資筆數 / 檔案總數</th>"
                                            . "<th>時間區間</th>"
                                            . "<th>掃描項目</th>"
                                            . "<th>電腦</th>"
                                            . "<th><span>產生日期</span></th>"
                                            . "<th>動作</th>"
                                            . "</tr>";              	  
         }
         if ($page_count < $page_size)
         {
            $return_string = $return_string . "<tr>"
                                            . "<td class=\"rNameW\"><span class=\"rName fixWidth\" OnClick=openReport($rID[$i]);><a>" . $rNameW[$i] . "</a></span></td>"
                                            . "<td class=\"vHighW\">" . number_format($vHighW_file[$i]) . "</td>"
                                            . "<td class=\"highW\">" . number_format($HighW_file[$i]) . "</td>"
                                            . "<td class=\"totalW\">" . number_format($vHighW_data[$i] + $HighW_data[$i] + $MediumW_data[$i] + $LowW_data[$i]) . " / " . number_format($vHighW_file[$i] + $HighW_file[$i] + $MediumW_file[$i] + $LowW_file[$i]) . "</td>"
                                            . "<td class=\"tRangeW\">" . $tRangeW_begin[$i] . " ~ " . $tRangeW_end[$i] . "</td>"
                                            . "<td class=\"rItemW\"><span class=\"rItem fixWidth\">" . $rItemW[$i] . "</span></td>"
                                            . "<td class=\"cTotalW\">" . number_format($cTotalW[$i]) . "</td>"
                                            . "<td class=\"rTimeW\">" . $rTimeW[$i] . "</td>"
                                            . "<td class=\"actW\"><a id=\"" . $rID[$i] . "_reportID\" class=\"del\" OnClick=\"deleteReport(this);\">刪除</a></td>"
                                            . "</tr>";                    
            $i++;
            $page_count++;
            if ($page_count == $page_size)
            {
               $return_string = $return_string . "</table>"
                                               . "</div>\n";
               $page_no++;
               $page_count = 0;
            }                    
         }
      }
      if ($page_count > 0)
      {
         $return_string = $return_string . "</table>"
                                         . "</div>\n";
      }               
   }
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
      
   //----- Print Report Pages -----
   if ($page_num > 1)
   {
     	for ($i = 0; $i < $page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"page";
         if ($i + 1 == $page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=page_end_no_" . ($i + 1) . " OnClick=clickPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span class=\"btn new\" OnClick=\"newReportFunc();\">產生新的報表</span>"
                                   . "</div>";
   echo $return_string;
   return;
}
?>
