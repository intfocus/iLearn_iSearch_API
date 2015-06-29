#!/usr/bin/perl -w

use Fcntl;
use File::Basename;
use Encode;

$META_FOLDER_PREFIX = '/usr/local/www/apache22/data/upload_old';
$COUNT_FIELD = 8;   # count0 ~ count7 , 目前 count7 為預留欄位
$COUNT_FIELD_SIZE = $COUNT_FIELD * 4;   # 每個 count 用 4bytes unsigned int , 不要亂改這個數字

# 加 -c 參數是為了怕有 utf8 不能轉 big5 的字元可以直接忽略
$ICONV_CMD = '/usr/local/bin/iconv -c -f utf-8 -t big5';

###################
# count0 -> 身分證
# count1 -> 手機號碼
# count2 -> 地址
# count3 -> 電子郵件地址
# count4 -> 信用卡號碼
# count5 -> 姓名
# count6 -> 市話號碼
###################
@TYPE_NAME_MAP = ('身分證', '手機號碼', '地址', '電子郵件地址', '信用卡號碼', '姓名', '市話號碼');
for(@TYPE_NAME_MAP){
   $_ = encode('utf8',decode('big5',$_));
}

@RISK_LEVEL = ('極高','高','中');
for(@RISK_LEVEL){
   $_ = encode('utf8',decode('big5',$_));
}

$HANDLE = encode('utf8',decode('big5','留用'));

###############
# count0 -> 3
# count1 -> 1
# count2 -> 1
# count3 -> 1
# count4 -> 3
# count5 -> 1
# count6 -> 1
###############

$iFound_file = $ARGV[0];
$out = "$iFound_file.out";
$out_utf8 = "$out.utf8";

if(!open(IFOUND,$iFound_file)){
   &err(__LINE__);
}

if(!open(OUT,">$out_utf8")){
   &err(__LINE__);
}

chomp($guid = <IFOUND>);
chomp($risk = <IFOUND>);
chomp($identity_type = <IFOUND>);

($g_risk_low,$g_risk_high,$g_risk_extreme,$g_risk_extreme_type_num,$risk_extreme_type) = split("\t",$risk);
@g_risk_extreme_type = split(",",$risk_extreme_type);
@g_identity_type = split("\t",$identity_type);

$no = 0;

while(<IFOUND>){

   chomp;

   ($xmlid,$create_time) = split("\t",$_);
   ($year,$mon) = $create_time =~ /(\d\d\d\d)-(\d\d)/;

   $meta =  "$META_FOLDER_PREFIX/$guid/$year$mon/$xmlid";
   $count_file = "$meta.count";

   $count_size = -s "$count_file";
   if(!defined($count_size)){
      &err(__LINE__);
   }
   
   $ret = sysopen(COUNT,$count_file,O_RDONLY);
   if(!defined($ret)){
      &err(__LINE__);
   }

   $ret = sysread(COUNT,$buf,$count_size);
   if(!defined($ret)){
      &err(__LINE__);
   }
   elsif($ret != $count_size){
      &err(__LINE__);
   }

   close(COUNT);

   $path_file = "$meta.path";

   if(!open(PATH,$path_file)){
      &err(__LINE__);
   }

   @count = unpack('L*',$buf);
   $num = $count_size / $COUNT_FIELD_SIZE;

   for($i = 0; $i < $num; ++$i){

      $count_index = $i * $COUNT_FIELD;

      $path = <PATH>;

      # 算個資數量

      $data = 0;

      for my $identity_type (@g_identity_type){
         $data += $count[$count_index + $identity_type];
      }

      # 算極高
      
      $risk_extreme_type_num = 0;

      for my $extreme_type (@g_risk_extreme_type){ 
         if($count[$count_index + $extreme_type] >= $g_risk_extreme){
            ++$risk_extreme_type_num; 
         }
      }

      if($risk_extreme_type_num >= $g_risk_extreme_type_num){
         $risk = 0;
         &print_out;
         next;
      }
      
      if($data >= $g_risk_high){   # 算高
         $risk = 1;
         &print_out;
      }
      elsif($data > $g_risk_low and $data < $g_risk_high){   # 算中
         $risk = 2;
         &print_out;
      }

   }

   close(PATH);

}

close(OUT);

if($no > 0){

   # 將 out_utf8 轉成 big5 才能讓 excel 讀

   system("$ICONV_CMD $out_utf8 > $out");
   unlink($out_utf8);

}
else{

   print "no data\n";

}

sub print_out{

   $no++;

   $type_name = '';

   for my $identity_type (@g_identity_type){

      if($count[$count_index + $identity_type]){
         $type_name .= "$TYPE_NAME_MAP[$identity_type],";
      }

   }

   # 去掉最後的逗號
   chop($type_name);

   # 去掉 path 最後的換行字元
   chomp($path);

   print OUT "$risk\t$type_name\t$data\t$HANDLE\t$path\n";

}

sub err{

   $line = shift;

   print "error occur at $line line.\n";

   exit -1;

}

