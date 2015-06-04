<?php
/////////////////////////////////
//2012.02.15 Dylan
//繪出各種統計圖
//傳回統計圖資料字串回genReportChart
/////////////////////////////////   
//----- Read information from DB.conf -----
if(file_exists("/usr/local/www/apache22/DB.conf"))
{
   include_once("/usr/local/www/apache22/DB.conf");
}
else
{
   sleep(DELAY_SEC);
   echo FILE_ERROR;

   return;
}
if(file_exists("$path_prefix/$open_flash_chart_path/php-ofc-library/open-flash-chart.php"))
{
   include("$path_prefix/$open_flash_chart_path/php-ofc-library/open-flash-chart.php");
}
else
{
   sleep(DELAY_SEC);
   echo FILE_ERROR;
   return;
}
define(OPEN_FLASH_CHART_PATH, $open_flash_chart_path);   //from DB.conf
define(OPEN_FLASH_CHART_URL_PREFIX,$open_flash_chart_url_prefix);  // from DB.conf
define(FILE_NAME, $report_file_name);
/////////////////////////////////
//定義各種顏色
/////////////////////////////////  
define(COLOR_WHITE, "#FFFFFF");
define(COLOR_BLACK, "#000000");
define(COLOR_GREY, "#B4B4B4");
define(COLOR_BLUE, "#309ED1");
define(COLOR_YELLOW, "#F29700");
define(COLOR_BROWN, "#910D3B");
define(COLOR_ORANGE, "#F27000");
define(COLOR_RED, "#FF4939");
define(COLOR_GREEN, "#53AA13");
define(COLOR_PURPLE, "#4D004D");

define(COLOR_BACKGROUND, COLOR_WHITE);
define(COLOR_BAR_CHART_TOTAL, COLOR_BROWN);
define(COLOR_BAR_CHART_KINDS, COLOR_BLUE);

define(COLOR_BAR_DEPARTMENT_EXTREME_DANGER, COLOR_PURPLE);
define(COLOR_BAR_DEPARTMENT_HIGH_DANGER, COLOR_RED);
define(COLOR_BAR_DEPARTMENT_MEDIUM_DANGER, COLOR_GREEN);
define(COLOR_BAR_DEPARTMENT_LOW_DANGER, COLOR_BLUE);

define(COLOR_EXTREME_DANGER, COLOR_PURPLE);
define(COLOR_HIGH_DANGER, COLOR_RED);
define(COLOR_MEDIUM_DANGER, COLOR_GREEN);
define(COLOR_LOW_DANGER, COLOR_BLUE);

define(COLOR_BAR_TOPN_EXTREME_DANGER, COLOR_PURPLE);
define(COLOR_BAR_TOPN_HIGH_DANGER, COLOR_RED);
define(COLOR_BAR_TOPN_MEDIUM_DANGER, COLOR_GREEN);
define(COLOR_BAR_TOPN_LOW_DANGER, COLOR_BLUE);

define(COLOR_BAR_EXTREME_DANGER, COLOR_PURPLE);
define(COLOR_BAR_HIGHLY_DANGER, COLOR_RED);

/////////////////////////////////
//定義各種title
/////////////////////////////////   
define(TITLE_PIE_TOTAL, "");
define(TITLE_BAR_TOTAL_DANGER, "");
define(TITLE_PIE_KINDS_DANGER, "");
define(TITLE_BAR_KINDS_DANGER, "");
define(TITLE_BAR_DEPARTMENTS_DANGER, "");
define(TITLE_BAR_TOPN_DANGER, "");
define(TITLE_BAR_TOPN_EXTREME_DANGER, "");
define(TITLE_BAR_TOPN_HIGH_DANGER, "");

define(ALL_CLEAN, "100% clean");
/////////////////////////////////
//定義格數
//字體大小(餅狀圖的)
//多少格顯示出數值
/////////////////////////////////  
define(GRID_NUMBER, 20);
define(FONT_SIZE, 14);
define(BAR_FONT_SIZE, 14);
define(VISIBLE_STEP, 5);
/////////////////////////////////
//調整圖形對齊label所用參數
/////////////////////////////////  
define(NEW_LINE_LABEL_MAX, 24);
define(PIC_EXTEND_UNIT, 7);
/////////////////////////////////
//圖片預設大小
/////////////////////////////////  
define(PICTURE_WIDTH, 500);
define(PICTURE_HEIGHT, 500);
 
$color = array(COLOR_PURPLE, COLOR_GREEN, COLOR_RED, COLOR_ORANGE, COLOR_BROWN, COLOR_YELLOW, COLOR_BLUE, COLOR_GREY);
/////////////////////////////////
//產生各種風險等級統計的餅狀圖
/////////////////////////////////       
function PieChartTotal($inputTotal)
{
   $data = array();
   $emptySlice = array();
   $patternName = array();
   $i = 0; 
   //餅狀圖資料陣列
   $slice = array();
   $dataSum = 0;      
   $numberOfDangerous = count($inputTotal);   
   $title = new title(TITLE_PIE_TOTAL);   
   $pie = new pie();   
   $percentage = 0;   
   $chart = new open_flash_chart();      
   global $color;	 
   $emptySlice = array();      
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////          
   if(is_null($inputTotal))
   {
      $inputTotal = array();
   }
   //取得危險種類pattern 和 data
   reset($inputTotal); 
   while (list($key, $val) = each($inputTotal))
   {
      $data[$i] = intval($val);
      $patternName[$i] = $key;
      $i++;
   }
   $dataSum = array_sum($data);          
   //指定餅狀圖樣式
   $pie->set_animate(false);
   $pie->colours(array(COLOR_EXTREME_DANGER, COLOR_HIGH_DANGER, COLOR_MEDIUM_DANGER, COLOR_LOW_DANGER));

   //當沒有任何危險時,呈現100%clean    
   if($dataSum == 0)
   {  
      $tmp = new pie_value(100, "");
      $tmpLabel = ALL_CLEAN;
      $tmp->set_label($tmpLabel, "", FONT_SIZE);
      $emptySlice[] = $tmp;
      //滑鼠移過去時的提示
      //$pie->tooltip('#percent# of 100%');
      $pie->set_values($emptySlice);
   }
   //有任何危險就繪出餅狀圖
   else
   {	
      for($i = 0 ; $i < $numberOfDangerous ; $i++)
      {
         $tmp = new pie_value($data[$i], "");
         $percentage = ($data[$i]/$dataSum) * 100;
         $percentage = number_format($percentage, 1, '.', '');
         $tmpLabel = $patternName[$i] . "\n" . number_format($data[$i]) . " : " . $percentage."%";
         if($data[$i] == 0)
         {
            $tmpLabel = "";
         }
         $tmp->set_label($tmpLabel, "", FONT_SIZE);
         $slice[] = $tmp;
      }      
      $pie->set_alpha(0.5);
      $pie->set_values($slice);
   }
   //設定整張圖樣式
   $chart = new open_flash_chart();
   $chart->set_title($title);
   $chart->add_element($pie);
   $chart->set_bg_colour(COLOR_BACKGROUND);

   return $chart->toPrettyString();   
}
/////////////////////////////////
//產生各種風險等級統計的條狀圖
/////////////////////////////////   
function BarChartTotal($inputTotal)
{
   $data = array();
   $patternName = array();
   $i = 0;   
   $numberOfDangerous = count($inputTotal);
   $title = new title(TITLE_BAR_TOTAL_DANGER);
   $maxValue = 0;
   $barTotal = new hbar(COLOR_BAR_CHART_TOTAL);  

   $chart = new open_flash_chart();   
   $xAxis = new x_axis();      
   $labels = new x_axis_labels();      
   $y_labels = new y_axis_labels();  
   $yAxis = new y_axis();   
	$color = array(COLOR_EXTREME_DANGER, COLOR_HIGH_DANGER, COLOR_MEDIUM_DANGER, COLOR_LOW_DANGER);   
   //$tooltip = new tooltip();     
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////          
   if(is_null($inputTotal))
   {
      $inputTotal = array();
      //echo "null";
   }
   //取得危險種類pattern 和 data
   reset($inputTotal);
   while (list($key, $val) = each($inputTotal))
   {
      $data[$i] = intval($val);
      $patternName[$i] = $key;
      $i++;
   }
   //$barTotal->set_tooltip('#val#');    
   //設定bar的數值,並將y軸label加上數值,且算出最大值,用來作X軸數值顯示   
   for($i = 0 ; $i < $numberOfDangerous ; $i++)
   {
		//////////////////////////////////////////////
		//to do next
		//////////////////////////////////////////////
		$temp = new hbar_value(0, $data[$i]);
		$temp->set_colour($color[$i] );
		$barTotal->append_value( $temp );

      //$barTotal->append_value(new hbar_value(0, $data[$i]));
      if($data[$i] > $maxValue)
      {
         $maxValue = $data[$i] ;
      }
      $patternName[$i] .= "\n". number_format($data[$i]);
   }
   //因為Y軸方向與X軸相反,所以必須先反向一次
   $patternName = array_reverse($patternName);	
   //指定整張圖樣式      
   $chart->set_title($title);
   $chart->add_element($barTotal);
   $chart->set_bg_colour(COLOR_BACKGROUND);
   //設定X軸格數與顯示數字    
   $xAxis->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->visible_steps(VISIBLE_STEP);
   $xAxis->set_labels($labels);
   $chart->set_x_axis($xAxis);
   //設定Y軸顯示樣式
   $y_labels = new y_axis_labels();
   $y_labels->set_size(BAR_FONT_SIZE);
   $y_labels->set_labels($patternName);
   $yAxis->set_range(0, $numberOfDangerous - 1, 1);   
   $yAxis->set_offset(true);      
   $yAxis->set_labels($y_labels);
   $chart->add_y_axis($yAxis);
   //設定滑鼠游標經過浮出提示
/*       $tooltip->set_hover();
   $tooltip->set_stroke(1);
   $tooltip->set_colour(COLOR_BLACK);
   $tooltip->set_background_colour(COLOR_WHITE); 
   $tooltip->set_body_style("{font-size: 15px; font-weight: bold; color: COLOR_BLACK;}");													
   $chart->set_tooltip($tooltip);    */ 
   
   return $chart->toPrettyString();  
}   
/////////////////////////////////
//產生各種風險類型統計的餅狀圖
/////////////////////////////////   
function PieChartKinds($inputKinds)
{  
   $data = array();
   $patternName = array();
   $emptySlice = array();
   $i = 0;
   $numberOfKinds = count($inputKinds);      
   global $color;     
   $title = new title(TITLE_PIE_KINDS_DANGER);    
   //餅狀圖資料陣列
   $slice = array();
   $dataSum = 0;
   $emptySlice = array();  
   $chart = new open_flash_chart();
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////          
   if(is_null($inputKinds))
   {
      $inputKinds = array();
      //echo "null";
   }
   //取得個資類型pattern與data
   reset($inputKinds);
   while (list($key, $val) = each($inputKinds))
   {
      $data[$i] = intval($val);
      $patternName[$i] = $key;
      $i++;
   }
   $dataSum = array_sum($data);      
   //指定餅狀圖樣式      
   $pie = new pie();
   $pie->set_animate(false);
   $pie->colours($color);
   //當沒有任何危險時,呈現100%clean          
   if($dataSum == 0)
   {  
      $tmp = new pie_value(100, "");
      $tmpLabel = ALL_CLEAN;
      $tmp->set_label($tmpLabel, "", FONT_SIZE);
      $emptySlice[] = $tmp;
      //滑鼠移過去時的提示         
      $pie->set_values($emptySlice);
   }
   //有任何危險就繪出餅狀圖      
   else
   {	
      for($i = 0 ; $i < $numberOfKinds ; $i++)
      {
         $tmp = new pie_value($data[$i], "");
         $percentage = ($data[$i]/$dataSum) * 100;
         $percentage = number_format($percentage, 1, '.', '');
         $tmpLabel = $patternName[$i] . "\n" . $data[$i] . " : " . $percentage."%";
         if($data[$i] == 0)
         {
            $tmpLabel = "";
         }		 
         $tmp->set_label($tmpLabel, "", FONT_SIZE);
         $slice[] = $tmp;
      }      
      $pie->set_values($slice);
      $pie->set_alpha(2);
   }
   //設定整張圖樣式      
   $chart->set_title($title);
   $chart->add_element($pie);
   $chart->set_bg_colour(COLOR_BACKGROUND);

   return $chart->toPrettyString();   
}
/////////////////////////////////
//產生各種風險類型統計的條狀圖
/////////////////////////////////     
function BarChartKinds($inputKinds)
{
   $data = array();
   $patternName = array("","","","","","","","");
   $i = 0;   
   $numberOfKinds = count($inputKinds);   
   $title = new title(TITLE_BAR_KINDS_DANGER);
   $maxValue = 0;
   $barKinds = new hbar(COLOR_BAR_CHART_KINDS);
   $chart = new open_flash_chart();      
   $xAxis = new x_axis();
   $labels = new x_axis_labels();      
   $yAxis = new y_axis();   
   //$tooltip = new tooltip();
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////          
   if(is_null($inputKinds))
   {
      $inputKinds = array();
      //echo "null";
   }
   //取得個資類型pattern與data
   reset($inputKinds);
   while (list($key, $val) = each($inputKinds))
   {
      if($val != 0)
      {
         $data[$i] = intval($val);
         $patternName[$i] = $key;
         $i++;
      }
   }
   //$barKinds->set_tooltip('#val#');      
   //設定bar的數值,並將y軸label加上數值,且算出最大值,用來作X軸數值顯示   
   for($i = 0 ; $i < $numberOfKinds ; $i++)
   {
      $barKinds->append_value(new hbar_value(0, $data[$i]));
      if($data[$i] > $maxValue)
      {
         $maxValue = $data[$i] ;
      }
      $patternName[$i] .= "\n". $data[$i];
   }
   //因為Y軸方向與X軸相反,所以必須先反向一次      
   $patternName = array_reverse($patternName);
   //指定整張圖樣式      
   $chart->set_title($title);
   $chart->add_element($barKinds); 
   $chart->set_bg_colour(COLOR_BACKGROUND);
   //設定X軸格數與顯示數字      
   $xAxis->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->visible_steps(VISIBLE_STEP);
   $xAxis->set_labels($labels);
   $chart->set_x_axis($xAxis);
   //設定Y軸顯示樣式      
   $yAxis->set_offset(true);
   $yAxis->set_labels($patternName);
   $chart->add_y_axis($yAxis);
   //設定滑鼠游標經過浮出提示      
/*       $tooltip->set_hover();
   $tooltip->set_stroke(1);
   $tooltip->set_colour(COLOR_BLACK);
   $tooltip->set_background_colour(COLOR_WHITE); 
   $tooltip->set_body_style("{font-size: 15px; font-weight: bold; color: COLOR_BLACK;}");													
   $chart->set_tooltip($tooltip); */
   
   return $chart->toPrettyString();
}   
/////////////////////////////////
//產生各種部門風險等級統計的條狀圖
/////////////////////////////////
////////////////////////////////
// 2013.01.14 modified by Odie
// 1.移除圖示中，中低風險的部分
// 2.按風險檔案數量排序(降幂)
// 3.Fix bug, 將departmentXXX array放到dataXXX array時錯置的問題
////////////////////////////////
function BarChartDepartments($departmentExtremeDanger, $departmentHighDanger, $departmentMediumDanger, $departmentLowDanger)
{
   //$patternName = array();
   $sortedPatternName = array();
   //$dataExtremeDanger = array();      
   //$dataHighDanger = array();      
   //$dataMediumDanger = array();     
   //$dataLowDanger = array();      
   $numberOfDepartment = 0;     
   $title = new title(TITLE_BAR_DEPARTMENTS_DANGER);
   $maxValue = 0;
   $barExtremeDanger = new hbar(COLOR_BAR_DEPARTMENT_EXTREME_DANGER);
   $barHighDanger = new hbar(COLOR_BAR_DEPARTMENT_HIGH_DANGER);   
   //$barMediumDanger = new hbar(COLOR_BAR_DEPARTMENT_MEDIUM_DANGER);      
   //$barLowDanger = new hbar(COLOR_BAR_DEPARTMENT_LOW_DANGER);
   $chart = new open_flash_chart();
   $xAxis = new x_axis();
   $labels = new x_axis_labels();      
   $yAxis = new y_axis();
   $isEmptyExtremeDanger = FALSE;

   //$tooltip = new tooltip();    
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////          
   if(is_null($departmentExtremeDanger))
   {
      $departmentExtremeDanger = array();
      //echo "null";
   }
   if(is_null($departmentHighDanger))
   {
      $departmentHighDanger = array();
      //echo "null";
   }
   
   if(is_null($departmentMediumDanger))
   {
      $departmentMediumDanger = array();
      //echo "null";
   }
   if(is_null($departmentLowDanger))
   {
      $departmentLowDanger = array();
      //echo "null";
   } 
   
   /* 
   //測試用，自己填傳進來的參數內容
   $departmentExtremeDanger = array();
   $departmentHighDanger = array();
   $departmentMediumDanger = array();
   $departmentLowDanger = array();

   $departmentExtremeDanger["A"] = 10;
   $departmentExtremeDanger["B"] = 54;
   $departmentExtremeDanger["C"] = 8;
   $departmentExtremeDanger["D"] = 6;

   $departmentHighDanger["B"] = 76;
   $departmentHighDanger["C"] = 6;

   $departmentMediumDanger["A"] = 50;
   $departmentMediumDanger["E"] = 80;

   $departmentLowDanger["F"] = 20;
    */

   //檢查departmentExtremeDanger原本是否為空
   if(count($departmentExtremeDanger) == 0)
      $isEmptyExtremeDanger = TRUE;

   //取得所有的部門名稱，包含沒有極高、高度風險檔案的電腦
   //檢查departmentExtremeDanger, departmentHighDanger, departmentMediumDanger, departmentLowDanger中的key
   //是不是有在departmentExtremeDanger和departmentHighDanger中出現
   //若沒有出現，則新增該筆資料，value為0
   foreach($departmentExtremeDanger as $key => $val)
   {
      if(!array_key_exists($key, $departmentHighDanger))
         $departmentHighDanger[$key] = 0;
   }
   foreach($departmentHighDanger as $key => $val)
   {
      if(!array_key_exists($key, $departmentExtremeDanger))
         $departmentExtremeDanger[$key] = 0;
   }
   foreach($departmentMediumDanger as $key => $val)
   {
      if(!array_key_exists($key, $departmentExtremeDanger))
         $departmentExtremeDanger[$key] = 0;
      if(!array_key_exists($key, $departmentHighDanger))
         $departmentHighDanger[$key] = 0;
   }
   foreach($departmentLowDanger as $key => $val)
   {
      if(!array_key_exists($key, $departmentExtremeDanger))
         $departmentExtremeDanger[$key] = 0;
      if(!array_key_exists($key, $departmentHighDanger))
         $departmentHighDanger[$key] = 0;
   }
   $numberOfDepartment = count($departmentExtremeDanger);

   //若departmentExtremeDanger原本有資料則按其排序
   //若原本無資料則按departmentHighDanger排序
   if($isEmptyExtremeDanger == FALSE)
   {
      $i = 0;
      //排序departmentExtremeDanger和departmentHighDanger(依value降冪)
      arsort($departmentExtremeDanger);
      //以排序依次取得departmentExtremeDanger的value
      //並依其key將departmentHighDanger中的value取出
      foreach($departmentExtremeDanger as $key => $val)
      {
         $barExtremeDanger->append_value(new hbar_value(0, $val));
         $barHighDanger->append_value(new hbar_value($val, $val + $departmentHighDanger[$key]));
         if($val + $departmentHighDanger[$key] > $maxValue)
            $maxValue = $val + $departmentHighDanger[$key];
         //排序y-axis-label
         $sortedPatternName[$i] = $key;
         $i++;
      }
   }
   else
   {
      $i = 0;
      //排序departmentHighDanger(依value降冪)
      arsort($departmentHighDanger);
      //以排序依次取得departmentHighDanger的value
      foreach($departmentHighDanger as $key => $val)
      {
         $barExtremeDanger->append_value(new hbar_value(0, 0));
         $barHighDanger->append_value(new hbar_value(0, $val));
         if($val > $maxValue)
            $maxValue = $val;
         //排序y-axis-label
         $sortedPatternName[$i] = $key;
         $i++;
      }
   }

   /*
   //取得各種危險資料與部門pattern      
   if($departmentExtremeDanger != null)
   {
      reset($departmentExtremeDanger);
      $i = 0;
      while (list($key, $val) = each($departmentExtremeDanger))
      {
         $dataExtremeDanger[$i] = intval($val);
         $patternName[$i] = $key;
         $i++;
      }
      $numberOfDepartment = count($dataExtremeDanger);     
   }
   if($departmentHighDanger != null)
   {
      reset($departmentHighDanger);
      $i = 0;
      while (list($key, $val) = each($departmentHighDanger))
      {
         $dataHighDanger[$i] = intval($val);
         $patternName[$i] = $key;         
         $i++;
      }
      $numberOfDepartment = count($departmentHighDanger);         
   }  
   if($departmentMediumDanger != null)
   {
      reset($departmentMediumDanger);
      $i = 0;
      while (list($key, $val) = each($departmentMediumDanger))
      {
         $dataMediumDanger[$i] = intval($val);
         $patternName[$i] = $key;         
         $i++;
      }
      $numberOfDepartment = count($departmentMediumDanger);         
   }
   if($departmentLowDanger != null)
   {
      reset($departmentLowDanger);
      $i = 0;
      while (list($key, $val) = each($departmentLowDanger))
      {
         $dataLowDanger[$i] = intval($val);
         $patternName[$i] = $key;         
         $i++;
      }      
      $numberOfDepartment = count($departmentLowDanger);
   }
   //設定numberOfDepartment至最大值
   $numberOfDepartment = max(count($departmentHighDanger), count($dataExtremeDanger));
   
   //$barExtremeDanger->set_tooltip('ExtremeDanger<br>Counts: #val#');
   //設定barExtremeDanger資料與區間      
   for($i = 0 ; $i < $numberOfDepartment ; $i++)
   {
      $barExtremeDanger->append_value(new hbar_value(0, $dataExtremeDanger[$i]));
   }
   //$barHighDanger->set_tooltip("HighDanger<br>Counts: #val#");
   //設定barHighDanger 資料與區間   
   for($i = 0 ; $i < $numberOfDepartment ; $i++)
   {
      $barHighDanger->append_value(new hbar_value($dataExtremeDanger[$i], $dataExtremeDanger[$i] + $dataHighDanger[$i]));
   }
   //$barMediumDanger->set_tooltip('MediumDanger<br>Counts: #val#');
   //設定barMediumdanger資料與區間   
   for($i = 0 ; $i < $numberOfDepartment ; $i++)
   {
      $barMediumDanger->append_value(new hbar_value($dataExtremeDanger[$i] + $dataHighDanger[$i], $dataExtremeDanger[$i] + $dataHighDanger[$i] + $dataMediumDanger[$i]));
   }
   //$barLowDanger->set_tooltip('NormalDanger<br>Counts: #val#');
   //設定barLowDanger資料與區間
   for($i = 0 ; $i < $numberOfDepartment ; $i++)
   {
      $barLowDanger->append_value(new hbar_value($dataExtremeDanger[$i] + $dataHighDanger[$i] + $dataMediumDanger[$i], $dataExtremeDanger[$i] + $dataHighDanger[$i] + $dataMediumDanger[$i] + $dataLowDanger[$i]));
      if($dataExtremeDanger[$i]+$dataHighDanger[$i]+ $dataMediumDanger[$i] + $dataLowDanger[$i] > $maxValue)
      {
         $maxValue = $dataExtremeDanger[$i] + $dataHighDanger[$i] + $dataMediumDanger[$i] + $dataLowDanger[$i];
      }
   }
    */

   //因為Y軸方向與X軸相反,所以必須先反向一次         
   $sortedPatternName = array_reverse($sortedPatternName);     
   //塞入足夠的換行,讓yLabel對齊bar
   //在500*500下
   //八個bar需要4個\n,一個bar需要32個\n
   
   for($i = 0 ; $i < $numberOfDepartment ; $i++)
   {
      for($j = 0 ; $j < ceil(NEW_LINE_LABEL_MAX/$numberOfDepartment) ; $j++)
      {
         $sortedPatternName[$i] .= "\n";
      }
   } 
   
   //指定整張圖樣式           
   $chart->set_title($title);
   $chart->add_element($barExtremeDanger);
   $chart->add_element($barHighDanger);
   //$chart->add_element($barMediumDanger);
   //$chart->add_element($barLowDanger);
   $chart->set_bg_colour(COLOR_BACKGROUND);
   //設定X軸格數與顯示數字        
   $xAxis->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->visible_steps(VISIBLE_STEP);
   $xAxis->set_labels($labels);
   $chart->set_x_axis($xAxis);
   //設定Y軸顯示樣式            
   $y_labels = new y_axis_labels();
   $y_labels->set_size(BAR_FONT_SIZE);
   $y_labels->set_labels($sortedPatternName);
   $yAxis->set_range(0, $numberOfDepartment - 1, 1);   
   $yAxis->set_offset(true);
   $yAxis->set_labels($y_labels);
   $chart->add_y_axis($yAxis);
   //設定滑鼠游標經過浮出提示        
/*       $tooltip->set_hover();
   $tooltip->set_stroke(1);
   $tooltip->set_colour(COLOR_BLACK);
   $tooltip->set_background_colour(COLOR_WHITE); 
   $tooltip->set_body_style("{font-size: 15px; font-weight: bold; color: COLOR_BLACK;}");	   
   $chart->set_tooltip($tooltip); */
   
   return $chart->toPrettyString();      
} 
///////////////////////////////
// End of modified, 2013.01.14 by Odie
///////////////////////////////

//產生TOP10極端危險人員的條狀圖
/////////////////////////////////     
function BarExtremeDanger($inputExtremeDanger)
{
   $data = array();
   $patternName = array("","","","","","","","","","");
   $i = 0;   
   $numberOfTopN = count($inputExtremeDanger);
   $title = new title(TITLE_BAR_TOPN_EXTREME_DANGER);
   $maxValue = 0;
   $barTonNExtremeDanger = new hbar(COLOR_BAR_EXTREME_DANGER);   
   $chart = new open_flash_chart();
   $xAxis = new x_axis();
   $labels = new x_axis_labels();      
   $yAxis = new y_axis();      
   //$tooltip = new tooltip();
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////         
   if(is_null($inputExtremeDanger))
   {
      $inputExtremeDanger = array();
      //echo "null";
   }      
   //取得特別危險的人員pattern與資料
   reset($inputExtremeDanger);
   while (list($key, $val) = each($inputExtremeDanger))
   {
      $data[$i] = intval($val);
      $patternName[$i] = $key;
      $i++;
   }
   //$barTonNExtremeDanger->set_tooltip('#val#'); 
   //設定barExtremeDanger資料
   for($i = 0 ; $i < $numberOfTopN ; $i++)
   {
      $barTonNExtremeDanger->append_value(new hbar_value(0, $data[$i]));
      if($data[$i] > $maxValue)
      {
         $maxValue = $data[$i] ;
      }
      $patternName[$i] = str_replace("/","\n/", $patternName[$i]);             
      $patternName[$i] .= "\n". $data[$i];
   }
   //因為Y軸方向與X軸相反,所以必須先反向一次
   $patternName = array_reverse($patternName);
   //產生左邊邊界  
   $yLegend = new y_legend('                         ');
   $yLegend->set_style("{font-size:20px;}");
   $chart->set_y_legend($yLegend);
   //指定整張圖樣式
   $chart->set_title($title);
   $chart->add_element($barTonNExtremeDanger);
   $chart->set_bg_colour(COLOR_BACKGROUND);
   //設定X軸格數與顯示數字          
   $xAxis->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->visible_steps(VISIBLE_STEP);
   $xAxis->set_labels($labels);
   $chart->set_x_axis($xAxis);
   //設定Y軸顯示樣式         
   $yAxis->set_offset(true);
   $yAxis->set_labels($patternName);
   $chart->add_y_axis($yAxis);
   //設定滑鼠游標經過浮出提示           
/*       $tooltip->set_hover();
   $tooltip->set_stroke(1);
   $tooltip->set_colour(COLOR_BLACK);
   $tooltip->set_background_colour(COLOR_WHITE); 
   $tooltip->set_body_style("{font-size: 15px; font-weight: bold; color: COLOR_BLACK;}");													
   $chart->set_tooltip($tooltip); */
   
   return $chart->toPrettyString();   
} 
/////////////////////////////////
//產生TOP10高度危險人員的條狀圖
/////////////////////////////////      
function BarhighDanger($inputhighDanger)
{
   $data = array();
   $patternName = array("","","","","","","","","","");
   $i = 0;
   $numberOfTopN = count($inputhighDanger);
   $title = new title(TITLE_BAR_TOPN_HIGH_DANGER);
   $maxValue = 0;
   $barTopNHigh = new hbar(COLOR_BAR_HIGHLY_DANGER);    
   $chart = new open_flash_chart();    
   $xAxis = new x_axis();       
   $labels = new x_axis_labels();    
   $yAxis = new y_axis();      
   //$tooltip = new tooltip();
   /////////////////////////////////
   //如果null預設為空
   /////////////////////////////////       
   if(is_null($inputhighDanger))
   {
      $inputhighDanger = array();
      //echo "null";
   }    
   //取得高度危險的人員pattern與資料   
   reset($inputhighDanger);
   while (list($key, $val) = each($inputhighDanger))
   {
      $data[$i] = intval($val);
      $patternName[$i] = $key;
      $i++;
   }
   //$barTopNHigh->set_tooltip('#val#'); 
   //設定barHDanger資料      
   for($i = 0 ; $i < $numberOfTopN ; $i++)
   {
      $barTopNHigh->append_value(new hbar_value(0, $data[$i]));
      if($data[$i] > $maxValue)
      {
         $maxValue = $data[$i] ;
      }
      $patternName[$i] = str_replace("/","\n/", $patternName[$i]);    
      $patternName[$i] .= "\n". $data[$i];
   }
   //因為Y軸方向與X軸相反,所以必須先反向一次      
   $patternName = array_reverse($patternName);
   //產生左邊邊界   
   $yLegend = new y_legend('                         ');
   $yLegend->set_style("{font-size:20px;}");
   $chart->set_y_legend($yLegend);
   //指定整張圖樣式
   $chart->set_title($title);
   $chart->add_element($barTopNHigh); 
   $chart->set_bg_colour(COLOR_BACKGROUND);
   //設定X軸格數與顯示數字       
   $xAxis = new x_axis();      
   $xAxis->set_steps(ceil($maxValue/GRID_NUMBER));      
   $labels = new x_axis_labels();
   $labels->set_steps(ceil($maxValue/GRID_NUMBER));
   $labels->visible_steps(VISIBLE_STEP);
   $xAxis->set_labels($labels);
   $chart->set_x_axis($xAxis);
   //設定Y軸顯示樣式        
   $yAxis = new y_axis();
   $yAxis->set_offset(true);
   $yAxis->set_labels($patternName);
   $chart->add_y_axis($yAxis);
   //設定滑鼠游標經過浮出提示        
/*     $tooltip = new tooltip();
   $tooltip->set_hover();
   $tooltip->set_stroke(1);
   $tooltip->set_colour(COLOR_BLACK);
   $tooltip->set_background_colour(COLOR_WHITE); 
   $tooltip->set_body_style("{font-size: 15px; font-weight: bold; color: COLOR_BLACK;}");													
   $chart->set_tooltip($tooltip);     */ 
   
   return $chart->toPrettyString();   
}        

function drawChart($nLowFile, $nMediumFile, $nHighFile, $nExtremeFile, $arr_sum_risk_count,
   $arr_nDepExtremeFile, $arr_nDepHighFile, $arr_nDepMediumFile, $arr_nDepLowFile, $arr_nCompHighFile, $arr_nCompExtremeFile, $name_timestamp)
{
   global $report_file_name;
   //------------------------
   /////////////////////////////////
   //從傳入參數複製需要的資料
   /////////////////////////////////          
   $inputTotal = array(" 極高風險檔案"=>$nExtremeFile, " 高度風險檔案"=>$nHighFile, " 中度風險檔案"=>$nMediumFile, " 低度風險檔案"=>$nLowFile);
   $inputKinds = array("姓名"=>$arr_sum_risk_count[0], "身分證"=>$arr_sum_risk_count[1], "家用電話"=>$arr_sum_risk_count[2], "手機"=>$arr_sum_risk_count[3], "電子信箱"=>$arr_sum_risk_count[4], "住址"=>$arr_sum_risk_count[5], "信用卡"=>$arr_sum_risk_count[6]);
   $departmentExtremeDanger = array();
   $departmentHighDanger = array();
   $departmentMediumDanger = array();
   $departmentLowDanger = array();
   $inputhighDanger = array();
   $inputExtremeDanger = array();
   $departmentExtremeDanger = $arr_nDepExtremeFile;
   $departmentHighDanger = $arr_nDepHighFile;
   $departmentMediumDanger = $arr_nDepMediumFile;
   $departmentLowDanger = $arr_nDepLowFile;
   $inputhighDanger = $arr_nCompHighFile; 
   $inputExtremeDanger = $arr_nCompExtremeFile;
   //-------------------------
   /////////////////////////////////
   //給予各圖ID
   /////////////////////////////////             
   $idPieTotal = $report_file_name . "_1";
   $idBarTotal = $report_file_name . "_2";
   $idPieKinds = $report_file_name . "_3";
   $idBarKinds = $report_file_name . "_4";
   $idBarDepartments = $report_file_name . "_5";
   $idExtremeDanger = $report_file_name . "_6"; 
   $idHighDanger = $report_file_name . "_7";   
   /////////////////////////////////
   //算出極高度風險圖片需要的寬度
   /////////////////////////////////          
   $maxPatternLength = 0;
   if($inputExtremeDanger)
   {
      while (list($key, $val) = each($inputExtremeDanger))
      {
         $substrArray = explode("/",$key);
         foreach($substrArray as $index => $value)
         {
            $length = mb_strwidth($value, 'UTF-8');
            if($length > $maxPatternLength)
            {
               $maxPatternLength = $length;			
            }
         }
      }
   }   
   $pictureExtremeDangerWidth = PICTURE_WIDTH + $maxPatternLength * PIC_EXTEND_UNIT;    
   /////////////////////////////////
   //算出TopN高度風險圖片需要的寬度
   /////////////////////////////////    
   $maxPatternLength = 0;   
   if($inputhighDanger)
   {   
      while (list($key, $val) = each($inputhighDanger))
      {
         $substrArray = explode("/", $key);
         foreach($substrArray as $index => $value)
         {
            $length = mb_strwidth($value, 'UTF-8');
            if($length > $maxPatternLength)
            {
               $maxPatternLength = $length;			
            }
         }
      } 
   }      
   $pictureHighDangerWidth = PICTURE_WIDTH + $maxPatternLength * PIC_EXTEND_UNIT;
   /////////////////////////////////
   //得到圖片需要的內容
   /////////////////////////////////             
   $data_1 = PieChartTotal($inputTotal);
   $data_2 = BarChartTotal($inputTotal);
   $data_3 = PieChartKinds($inputKinds);
   $data_4 = BarChartKinds($inputKinds);
   $data_5 = BarChartDepartments($departmentExtremeDanger, $departmentHighDanger, $departmentMediumDanger, $departmentLowDanger);
   $data_6 = BarExtremeDanger($inputExtremeDanger);
   $data_7 = BarhighDanger($inputhighDanger);   
   $swfStr = "	   
      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?1_$name_timestamp\", \"$idPieTotal\",
         \"" . PICTURE_WIDTH . "\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_1\",\"id\":\"$idPieTotal\"}
      );       

      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?2_$name_timestamp\", \"$idBarTotal\",
         \"" . PICTURE_WIDTH . "\", \"" . "300" . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_2\",\"id\":\"$idBarTotal\"}
      );      
      
      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?3_$name_timestamp\", \"$idPieKinds\",
         \"" . PICTURE_WIDTH . "\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_3\",\"id\":\"$idPieKinds\"}
      ); 

      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?4_$name_timestamp\", \"$idBarKinds\",
         \"" . PICTURE_WIDTH . "\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_4\",\"id\":\"$idBarKinds\"}
      );   
 
      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?5_$name_timestamp\", \"$idBarDepartments\",
         \"" . PICTURE_WIDTH . "\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_5\",\"id\":\"$idBarDepartments\"}
      );         
      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?6_$name_timestamp\", \"$idExtremeDanger\",
         \"$pictureExtremeDangerWidth\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_6\",\"id\":\"$idExtremeDanger\"}
      );   
      swfobject.embedSWF
      (
         \"" . OPEN_FLASH_CHART_URL_PREFIX . "/open-flash-chart.swf?7_$name_timestamp\", \"$idHighDanger\",
         \"$pictureHighDangerWidth\", \"" . PICTURE_HEIGHT . "\", \"9.0.0\", \"expressInstall.swf\",
         {\"get-data\":\"get_data_7\",\"id\":\"$idHighDanger\"}
      );               
   data_1 =" . $data_1 . ";          
   data_2 =" . $data_2 . ";      
   data_3 =" . $data_3 . ";           
   data_4 =" . $data_4 . ";       
   data_5 =" . $data_5 . ";           
   data_6 =" . $data_6 . ";
   data_7 =" . $data_7 . ";
   ";   
   /////////////////////////////////
   //回傳圖形資料回genReportChart
   /////////////////////////////////     
   return $swfStr;
}
?>
