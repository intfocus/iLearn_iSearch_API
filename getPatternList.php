<?php
///////////////////////////////////////////////
// #002 modified by Odie, 2013/01/23
//      原本預設XML第一筆一定是dummy file, 但有可能不是dummy file, 導致筆數漏算
//      修正這個BUG
// #003 modified by Odie, 2013/02/27
//      因xml檔案過大, memory不足的狀況下會導致simplexml_load_file()失敗
//      先檢查xml檔案大小，若超過限制，則在pattern_list.tsv.utf8印出相對應數目的空行
// #004 modified by Odie, 2013/09/11
//      1. 從big5轉utf-8
//      2. init_type_count 從七個增加到八個
///////////////////////////////////////////////

define(XML_SIZE_LIMIT, 300*1024*1024);
define(EMPTY_MSG, "\n");

$prog = $argv[0];
$report_dir = $argv[1];
$xml_file_path = $argv[2];
$identity_type = $argv[3];
$file_count = $argv[4];
$fileid_file = "$report_dir/fileid";
$pattern_list_utf8 = "$report_dir/pattern_list.tsv.utf8";
//$debug_file = "$report_dir/php_debug.txt";
//$fdebug = @fopen($debug_file, "a");
$f = @fopen($pattern_list_utf8,"a");
$i = 0;

if($f == FALSE){
   err(__LINE__);
}

if(!@($fileid_data = file_get_contents($fileid_file))){
   err(__LINE__);
}

unlink($fileid_file);

$fileid = unpack("L*",$fileid_data);
$count = count($fileid);

// #003 begin
// 檢查xml檔案是否存在且它的size是否大於limit
// 若存在且size大於limit, 則印出空行，關檔，退出此程式
if(file_exists($xml_file_path) && filesize($xml_file_path) > XML_SIZE_LIMIT){
   $file_count = (int)($file_count);
   for($i = 0; $i < $file_count; $i++){
      fwrite($f, EMPTY_MSG);
   }
   fclose($f);
   echo "SUCCESS";
   exit;
}
// #003 end

if(($xml = @simplexml_load_file($xml_file_path)) == FALSE){
   err(__LINE__);
}

foreach(explode(',',$identity_type) as $val){
   $type_map[$val] = 1;
}

$init_type_count = array(0,0,0,0,0,0,0,0);   // #004
$type_count = array_values($init_type_count);


// note: php 的 unpack index 從 1 開始 
$i = 1;

// note: xml 的第一筆可能是 dummy file
// 所以 id 設為 -1, 先比第一筆是不是dummy file,
// 從 0 開始以後代表真正有個資的 file

$id = -1;
foreach($xml->file as $file){
   
   ////////////////////////////
   // #002 start modified
   ////////////////////////////

   // 看是不是第一筆，如果是，檢查是不是dummy
   if($id == -1){
      $obj_str = $file->file_info->file_full_name;
      // 如果是dummy,continue (取下一筆資料，id++)
      if(strncmp($obj_str, "dummy.file", 10) == 0){
         $id++;
         continue;
      }
      // 如果不是dummy, 把目前這一筆id設成0 (開始比對)
      else{
         $id++;
      }
   }

   ////////////////////////////
   // #002 end modified
   ////////////////////////////

   if($id++ != $fileid[$i]){
      continue;
   }
   $data = '';
   $type_count = array_values($init_type_count);
   foreach($file->pattern_list->pattern as $pattern){
      // note: 要強制轉型為 int, 否則它會當做是 string type, 到時候去 array 查找的時候就會找不到了
      $type = (int)($pattern->attributes()->type);
      if(!$type_map[$type]){
         continue;
      }
      if($type_count[$type] >= 5){
         continue;
      }
      if($data){
         $data .= ",$pattern";
      }
      else{
         $data = $pattern;
      }
      ++$type_count[$type];

   }
   fwrite($f,"$data\n");
   if(++$i > $count){
      break;
   }
}

fclose($f);
echo "SUCCESS";

function err($err_line){

   global $prog;

   echo "program $prog: error occur at $err_line line.";

   exit;

}

?>
