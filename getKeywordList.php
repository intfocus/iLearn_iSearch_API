<?php
///////////////////////////////////////////////
// 2013/06/04 Created by Odie, copy from getPatternList.php
// 產出含關鍵字檔案報表專用，會列出match到的關鍵字然後寫入keyword_list.tsv
///////////////////////////////////////////////

define(XML_SIZE_LIMIT, 300*1024*1024);
define(EMPTY_MSG, "\n");
define(KEYWORD_TYPE, 7);

$prog = $argv[0];
$report_dir = $argv[1];
$xml_file_path = $argv[2];
$file_count = $argv[3];
$fileid_file = "$report_dir/fileid";
$keyword_list_utf8 = "$report_dir/keyword_list.tsv.utf8";
//$debug_file = "$report_dir/php_debug.txt";
//$fdebug = @fopen($debug_file, "a");
$f = @fopen($keyword_list_utf8,"a");
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

if(($xml = @simplexml_load_file($xml_file_path)) == FALSE){
   err(__LINE__);
}

// note: php 的 unpack index 從 1 開始 
$i = 1;

// note: xml 的第一筆可能是 dummy file
// 所以 id 設為 -1, 先比第一筆是不是dummy file,
// 從 0 開始以後代表真正有個資的 file

$id = -1;
foreach($xml->file as $file){
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

   if($id++ != $fileid[$i]){
      continue;
   }
   $data = '';
   $type_count = array_values($init_type_count);
   foreach($file->pattern_list->pattern as $pattern){
      // note: 要強制轉型為 int, 否則它會當做是 string type, 到時候去 array 查找的時候就會找不到了
      $type = (int)($pattern->attributes()->type);
      if($type != KEYWORD_TYPE){
         continue;
      }
      /*
      if($type_count[$type] >= 5){
         continue;
      }
      */
      if($data){
         $data .= ",$pattern";
      }
      else{
         $data = $pattern;
      }
      // ++$type_count[$type];
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
