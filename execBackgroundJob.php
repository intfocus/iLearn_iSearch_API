<?php

/////////////////////////////////////////////////////////
// #001 modified by Odie 2013/01/24
//      加上另一個Excel template (excel_20130124.tmpl)
// #002 modified by Odie 2013/03/02 ~ 2013/03/08
//      1. 增加呼叫genExcel.pl的參數
//         1.1. iFoundLast (diff用)
//         1.2. iAction (帶入處置結果用)
//      2. 修改寫入sheet1.tsv.utf8第一行的資料，在customer_name後寫入清查電腦台數(從iFound行數計算)
//      3. 重新上傳兩個版本的excel template
// #003 modified by Odie, 2013/09/17
//      1. 將此script編碼由Big5改為UTF-8
/////////////////////////////////////////////////////////

if(!defined(TMPL_EXCEL)){
   define(TMPL_EXCEL,'/usr/local/www/apache22/data/tmpl/excel_20141006_consultant.tmpl');
}

// #002 start
if(!defined(TMPL_EXCEL_1)){
   define(TMPL_EXCEL_1,'/usr/local/www/apache22/data/tmpl/excel_20141216_user.tmpl');
}
// #002 end

$working_path = dirname(__FILE__);

function execBackgroundJob($params){

   global $working_path;

   if(array_key_exists('report_path',$params)){
      $report_path = $params['report_path'];
   }
   else{
      return -__LINE__;
   }

   if(array_key_exists('report_name',$params)){
      $report_name = $params['report_name'];
   }
   else{
      return -__LINE__;
   }

   if(array_key_exists('report_id',$params)){
      $report_id = $params['report_id'];
   }
   else{
      return -__LINE__;
   }

   if(array_key_exists('working_link',$params)){
      $working_link = $params['working_link'];
   }
   else{
      return -__LINE__;
   }

   if(file_exists("$working_path/change.php") != TRUE){  //include changeHtml function php 
      return -__LINE__;
   }
   include_once("$working_path/change.php");

   $pid = pcntl_fork();
   if($pid < 0){ // fork fail
      return -__LINE__;
   }
   else if($pid){ // parent
      return 0; // parent return success
   }
   else{ // child

      // execute background job
      // posix_kill(getmypid(),9) instead of calling exit() prevent to if child dies then close the output stream that shared with parent

      if(gen_excel($params) < 0){ // gen excel
         posix_kill(getmypid(),9);
      }

      if(chdir($report_path) != TRUE){  //change directory
         posix_kill(getmypid(),9);
      }
      
      $report_folder = 'P-Marker Report';
      system("mkdir '$report_folder';mv *.doc *.xlsm *.tsv '$report_folder'");
      $cmd = "/usr/local/bin/zip -q -r $report_name.zip '$report_folder'"; 
      if(system("$cmd") != 0){ // zip all
         posix_kill(getmypid(),9);
      }
      
      changeHtml($report_id, $report_path, $report_name,$working_link);
      posix_kill(getmypid(),9);
   }

}

function gen_excel($params){

   global $working_path;

   // check params

   if(array_key_exists('report_path',$params)){
      $report_path = $params['report_path'];
   }
   else{
      return -__LINE__;
   }

   if(array_key_exists('report_name',$params)){
      $report_name = $params['report_name'];
   }
   else{
      return -__LINE__;
   }

   if(array_key_exists('customer_name',$params)){
      // 沒有限制??
      $customer_name = $params['customer_name'];
   }
   else{
      return -__LINE__;
   }
   
   // end check params

   // write customer name to sheet1

   if(($f = fopen("$report_path/sheet1.tsv.utf8","wb")) == FALSE){
      return -__LINE__;
   }

   fwrite($f,"$customer_name\t");
   
   fclose($f);

   // call genExcel.pl to gen sheet1 and sheet2 for excel
   //system("$working_path/genExcel.pl $report_path/iFound"); 
   // #002 add
   system("$working_path/genExcel.pl $report_path/iFound $report_path/iFoundLast $report_path/iAction"); 

   // copy excel template to report folder
   
   // #003, the excel name must be converted to BIG-5
   $customer_name = iconv('UTF-8','BIG-5',$customer_name);
   $consultant_excel_name = iconv('UTF-8','BIG-5',"個資盤點總表_顧問版本.xlsm");
   if(copy(TMPL_EXCEL,"$report_path/$customer_name$consultant_excel_name") == FALSE){
      return -__LINE__;
   }

   // #001 start
   $user_excel_name = iconv('UTF-8','BIG-5',"個資盤點總表_一般版本.xlsm");       
   if(copy(TMPL_EXCEL_1,"$report_path/$customer_name$user_excel_name") == FALSE){
      return -__LINE__;
   }
   // #001 end
   
   // success
   return 0;

}

?>
