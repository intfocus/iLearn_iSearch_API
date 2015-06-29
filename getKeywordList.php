<?php
///////////////////////////////////////////////
// 2013/06/04 Created by Odie, copy from getPatternList.php
// ���X�t����r�ɮ׳���M�ΡA�|�C�Xmatch�쪺����r�M��g�Jkeyword_list.tsv
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

// �ˬdxml�ɮ׬O�_�s�b�B����size�O�_�j��limit
// �Y�s�b�Bsize�j��limit, �h�L�X�Ŧ�A���ɡA�h�X���{��
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

// note: php �� unpack index �q 1 �}�l 
$i = 1;

// note: xml ���Ĥ@���i��O dummy file
// �ҥH id �]�� -1, ����Ĥ@���O���Odummy file,
// �q 0 �}�l�H��N��u�����Ӹꪺ file

$id = -1;
foreach($xml->file as $file){
   // �ݬO���O�Ĥ@���A�p�G�O�A�ˬd�O���Odummy
   if($id == -1){
      $obj_str = $file->file_info->file_full_name;
      // �p�G�Odummy,continue (���U�@����ơAid++)
      if(strncmp($obj_str, "dummy.file", 10) == 0){
         $id++;
         continue;
      }
      // �p�G���Odummy, ��ثe�o�@��id�]��0 (�}�l���)
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
      // note: �n�j���૬�� int, �_�h���|���O string type, ��ɭԥh array �d�䪺�ɭԴN�|�䤣��F
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
