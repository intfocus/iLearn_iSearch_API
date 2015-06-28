#!/usr/bin/perl -w

##################################
# # 001 2013/07/02, modified by Odie
#       Add encrypted file list for searching result
# # 002 2013/09/10, modified by Odie
#       1. Transfer the encoding of the script from big5 to utf-8
#       2. Add the 8th element in @TYPE_NAME_MAP. The correct name will be replaced in searchIdentityFile.php
##################################

use Fcntl;
use File::Basename;
use Encode;

$META_FOLDER_PREFIX = '/usr/local/www/apache22/data/upload_old';
$COUNT_FIELD = 8;   # count0 ~ count7 , 目前 count7 為預留欄位
$COUNT_FIELD_SIZE = $COUNT_FIELD * 4;   # 每個 count 用 4bytes unsigned int , 不要亂改這個數字

# $EXTREME_RISK_NAME = encode('utf8',decode('big5','極高風險'));
# $HIGH_RISK_NAME = encode('utf8',decode('big5','高度風險'));
# $MEDIUM_RISK_NAME = encode('utf8',decode('big5','中度風險'));
# $UNKNOWN_RISK_NAME = encode('utf8',decode('big5','未知'));  # #001

$EXTREME_RISK_NAME = '極高風險';
$HIGH_RISK_NAME = '高度風險';
$MEDIUM_RISK_NAME = '中度風險';
$UNKNOWN_RISK_NAME = '未知';  # #001

###################
## count0 -> 身分證
## count1 -> 手機號碼
## count2 -> 地址
## count3 -> 電子郵件地址
## count4 -> 信用卡號碼
## count5 -> 姓名
## count6 -> 市話號碼
## count7 -> 生日(只是某個預設值)
####################
# #002

@TYPE_NAME_MAP = ('身分證', '手機號碼', '地址', '電子郵件地址', '信用卡號碼', '姓名', '市話號碼', '生日');

$iFound_file = $ARGV[0];

$result_file = $iFound_file;
$result_file =~ s/iFound/result/;

if(!open(IFOUND,$iFound_file)){
   &err(__LINE__);
}


chomp($guid = <IFOUND>);
chomp($risk = <IFOUND>);
chomp($identity_type = <IFOUND>);
chomp($encrypt = <IFOUND>);
chomp($lmtime = <IFOUND>);

# #002 begin
($g_risk_check,$g_risk_low,$g_risk_high,$g_risk_extreme,$g_risk_extreme_type_num,$risk_extreme_type,$type8_enable,$type8_name) = split("\t",$risk);
if($type8_enable == 1){
   $TYPE_NAME_MAP[7] = $type8_name;
}
# #002 end

@g_risk_extreme_type = split(",",$risk_extreme_type);
@g_identity_type = split(",",$identity_type);
($g_lmtime_begin,$g_lmtime_end) = split("\t",$lmtime);

###################################################
# 判斷是否要開啟 last modify time meta data 的 flag
###################################################
if($g_lmtime_begin == 0 and $g_lmtime_end == 0){
   $g_lmtime = 0;
}
else{
   $g_lmtime = 1;
}

##########
# 符合筆數
##########
$g_match_count = 0;

$seq = 0;

while(<IFOUND>){

   ##### 第幾筆個資
   ++$seq;

   chomp;

   ($xmlid,$create_time,$hostname,$domain_name,$login_name,$department) = split("\t",$_);
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

   @count = unpack('L*',$buf);

   ###########################################
   # open last modify time meta file 當 filter
   ###########################################

   $lmtime_file = "$meta.mtime";

   $lmtime_size = -s "$lmtime_file";
   if(!defined($lmtime_size)){
      &err(__LINE__);
   }

   $ret = sysopen(LMTIME,$lmtime_file,O_RDONLY);
   if(!defined($ret)){
      &err(__LINE__);
   }

   $ret = sysread(LMTIME,$buf,$lmtime_size);
   if(!defined($ret)){
      &err(__LINE__);
   }
   elsif($ret != $lmtime_size){
      &err(__LINE__);
   }

   close(LMTIME);

   @lmtime = unpack('L*',$buf);

   @extreme_data = ();
   @high_data = ();
   @medium_data = ();
   @extreme_index = ();
   @high_index = ();
   @medium_index = ();
   @extreme_type = ();
   @high_type = ();
   @medium_type = ();
   @extreme_lmtime = ();
   @high_lmtime = ();
   @medium_lmtime = ();

   $num = $count_size / $COUNT_FIELD_SIZE;   #iFound中有幾筆資料

   for($i = 0; $i < $num; ++$i){

      #########################
      # last modify time filter
      #########################
      next if($g_lmtime and ($lmtime[$i] < $g_lmtime_begin or $lmtime[$i] > $g_lmtime_end));

      $count_index = $i * $COUNT_FIELD;

      # 算個資數量

      $data = 0;
      $type = 0;

      for my $identity_type (@g_identity_type){
         if($count[$count_index + $identity_type]){
            $data += $count[$count_index + $identity_type];
            $type |= (1 << $identity_type);
         }
      }

      # 算極高
      
      $risk_extreme_type_num = 0;

      for my $extreme_type (@g_risk_extreme_type){ 
         if($count[$count_index + $extreme_type] >= $g_risk_extreme){
            ++$risk_extreme_type_num; 
         }
      }

      if($risk_extreme_type_num >= $g_risk_extreme_type_num){
         # 極高風險可能是 001, 011, 101, 111 (1,3,5,7), 所以遇到 0,2,4,6 就跳過(continue)
         # IsExtreme, but extreme is not checked => continue to find next data
         next if($g_risk_check == 0 || $g_risk_check == 2 || $g_risk_check == 4 || $g_risk_check == 6);
         push(@extreme_data,$data);
         push(@extreme_index,$i);
         push(@extreme_type,$type);
         push(@extreme_lmtime,$lmtime[$i]);
         next;
      }
      
      if($data >= $g_risk_high){   # 算高(個資檔案數多於g_risk_high)
         # 高風險可能是 010, 011, 110, 111 (2,3,6,7), 所以遇到 0,1,4,5 就跳過(continue)
         # IsHigh, but high is not checked => continue to find next data
         next if($g_risk_check == 0 || $g_risk_check == 1 || $g_risk_check == 4 || $g_risk_check == 5);
         push(@high_data,$data);
         push(@high_index,$i);
         push(@high_type,$type);
         push(@high_lmtime,$lmtime[$i]);
         next;
      }

      if($data > $g_risk_low){   # 算中風險
         # 中風險可能是 100, 101, 110, 111 (4,5,6,7), 所以遇到 0,1,2,3 就跳過(continue)
         # IsMedium, but medium is not checked => continue to find next data
         next if($g_risk_check == 0 || $g_risk_check == 1 || $g_risk_check == 2 || $g_risk_check == 3);
         push(@medium_data,$data);
         push(@medium_index,$i);
         push(@medium_type,$type);
         push(@medium_lmtime,$lmtime[$i]);
         next;
      }
   }

   $g_match_count += $#extreme_data + $#high_data + $#medium_data + 3;

   # sort local top 1000 extreme/high data count
 
   if($#extreme_data >= 0){

      # local top1000 extreme

      $top_index  = $#extreme_data >= 1000 ? 999 : $#extreme_data;
      @top1000_extreme_index = (sort { $extreme_data[$b] <=> $extreme_data[$a] } (0 .. $#extreme_index))[0 .. $top_index];

      @top1000_info = ();
      for my $index (0 .. $#top1000_extreme_index){
         $m = $top1000_extreme_index[$index];
         $info = {
            'index' => $extreme_index[$m],
            'data'  => $extreme_data[$m],
            'type'  => $extreme_type[$m],
            'mtime' => $extreme_lmtime[$m],
            'xmlid' => $xmlid,
            'seq'   => $seq
         };
         push(@top1000_info,$info);
      }

      # merge to final top1000 extreme

      $m = 0;   # local top1000 info index
      $n = 0;   # final top1000 info index
      $top = 0;

      @work_info = ();
      while($m <= $#top1000_info and $n <= $#final_top1000_extreme_info and $top < 1000){
         if($top1000_info[$m]->{'data'} >= $final_top1000_extreme_info[$n]->{'data'}){
            push(@work_info,$top1000_info[$m]);
            ++$m;
         }
         else{
            push(@work_info,$final_top1000_extreme_info[$n]);
            ++$n;
         }
         ++$top;
      }
      
      while($m <= $#top1000_info and $top < 1000){
         push(@work_info,$top1000_info[$m]);
         ++$m;
         ++$top;
      }

      while($n <= $#final_top1000_extreme_info and $top < 1000){
         push(@work_info,$final_top1000_extreme_info[$n]);
         ++$n;
         ++$top;
      }

      @final_top1000_extreme_info = @work_info;

   }

   #######################################################################################################
   # 計算 high data 要取 top 幾, 因為如果前面有 top n extreme data 的話, 後面的 high data 就不用取那麼多了
   #######################################################################################################
   $topn_high = 999 - $#final_top1000_extreme_info;

   if($topn_high > 0 and $#high_data >= 0){

      # local topn high

      $top_index  = $#high_data >= $topn_high ? ($topn_high - 1) : $#high_data;
      @topn_high_index = (sort { $high_data[$b] <=> $high_data[$a] } (0 .. $#high_index))[0 .. $top_index];

      @topn_info = ();
      for my $index (0 .. $#topn_high_index){
         $m = $topn_high_index[$index];
         $info = {
            'index' => $high_index[$m],
            'data'  => $high_data[$m],
            'type'  => $high_type[$m],
            'mtime' => $high_lmtime[$m],
            'xmlid' => $xmlid,
            'seq'   => $seq
         };
         push(@topn_info,$info);
      }

      # merge to final topn high

      $m = 0;   # local topn info index
      $n = 0;   # final topn info index
      $top = 0;

      @work_info = ();
      while($m <= $#topn_info and $n <= $#final_topn_high_info and $top < $topn_high){
         if($topn_info[$m]->{'data'} >= $final_topn_high_info[$n]->{'data'}){
            push(@work_info,$topn_info[$m]);
            ++$m;
         }
         else{
            push(@work_info,$final_topn_high_info[$n]);
            ++$n;
         }
         ++$top;
      }
      
      while($m <= $#topn_info and $top < $topn_high){
         push(@work_info,$topn_info[$m]);
         ++$m;
         ++$top;
      }

      while($n <= $#final_topn_high_info and $top < $topn_high){
         push(@work_info,$final_topn_high_info[$n]);
         ++$n;
         ++$top;
      }

      @final_topn_high_info = @work_info;

   }

   #######################################################################################################
   # 計算 medium data 要取 top 幾, 因為如果前面有 top n extreme+high 的話, 後面的 medium data 就不用取那麼多了
   #######################################################################################################
   $topn_medium = 999 - $#final_top1000_extreme_info - $#final_topn_high_info;

   if($topn_medium > 0 and $#medium_data >= 0){

      # local topn medium 

      $top_index  = $#medium_data >= $topn_medium ? ($topn_medium - 1) : $#medium_data;
      @topn_medium_index = (sort { $medium_data[$b] <=> $medium_data[$a] } (0 .. $#medium_index))[0 .. $top_index];

      @topn_info = ();
      for my $index (0 .. $#topn_medium_index){
         $m = $topn_medium_index[$index];
         $info = {
            'index' => $medium_index[$m],
            'data'  => $medium_data[$m],
            'type'  => $medium_type[$m],
            'mtime' => $medium_lmtime[$m],
            'xmlid' => $xmlid,
            'seq'   => $seq
         };
         push(@topn_info,$info);
      }

      # merge to final topn medium 

      $m = 0;   # local topn info index
      $n = 0;   # final topn info index
      $top = 0;

      @work_info = ();
      while($m <= $#topn_info and $n <= $#final_topn_medium_info and $top < $topn_medium){
         if($topn_info[$m]->{'data'} >= $final_topn_medium_info[$n]->{'data'}){
            push(@work_info,$topn_info[$m]);
            ++$m;
         }
         else{
            push(@work_info,$final_topn_medium_info[$n]);
            ++$n;
         }
         ++$top;
      }
      
      while($m <= $#topn_info and $top < $topn_medium){
         push(@work_info,$topn_info[$m]);
         ++$m;
         ++$top;
      }

      while($n <= $#final_topn_medium_info and $top < $topn_medium){
         push(@work_info,$final_topn_medium_info[$n]);
         ++$n;
         ++$top;
      }

      @final_topn_medium_info = @work_info;

   }
}

if(!open(RESULT,">$result_file")){
   &err(__LINE__);
}

# print RESULT "$g_match_count\n";

# gen top 1000 extreme/high/medium data info

# 根據檔案編號和所在行數將 final_top1000_extreme_info 和 final_topn_high_info 和 final_topn_medium_info 混合 sort

if($#final_top1000_extreme_info >= 999){
   $#final_topn_high_info = -1;
   $#final_topn_medium_info = -1;
}
elsif($#final_top1000_extreme_info + $#final_topn_high_info >= 999){
   $#final_topn_medium_info = -1;
}

@seq_info = sort { $a->{'seq'} <=> $b->{'seq'} or $a->{'index'} <=> $b->{'index'} } @final_top1000_extreme_info,@final_topn_high_info,@final_topn_medium_info;

# #001 begin
# 先確認是否需要讀入encrypted file path
# => 1.用戶有勾選要看盤點失敗檔案清單 2.風險檔案未滿1000筆

if($encrypt == 1){
   @encrypt_info = ();
   if($g_match_count < 1000){
      if(seek(IFOUND,0,0) != 1){
         &err(__LINE__);
      }
      # 略過前五行 guid, risk, encrypt, identity_type and lmtime

      $line = <IFOUND>;
      $line = <IFOUND>;
      $line = <IFOUND>;
      $line = <IFOUND>;
      $line = <IFOUND>;

      while(<IFOUND>){
         chomp;
         ($xmlid,$create_time,$hostname,$domain_name,$login_name,$department) = split("\t",$_);
         ($year,$mon) = $create_time =~ /(\d\d\d\d)-(\d\d)/;

         $meta =  "$META_FOLDER_PREFIX/$guid/$year$mon/$xmlid";
         $encrypt_file = "$meta.encrypt";
         unless(-e $encrypt_file){
            next;
         }
         if(!open(ENCRYPT,$encrypt_file)){
            &err(__LINE__);
         }
         $line = <ENCRYPT>; # 去掉第一行(encrypted file數)
         while($filepath = <ENCRYPT>){
            chomp($filepath);
            ($filetype) = $filepath =~ /\.([^.]+)$/;
            $encrypt_line = "$xmlid\t$domain_name/$hostname\t$login_name\t$department\t$UNKNOWN_RISK_NAME\t-\t$filetype\t-\t-\t$filepath\n";
            push(@encrypt_info, $encrypt_line);
            $g_match_count++;
            last if($g_match_count == 1000);
         }
         last if($g_match_count == 1000);
      }
   }
}
# #001 end

print RESULT "$g_match_count\n";

# 如果 seq_info 裡面有東西才需要開啟 filepath meta file 取出 filepath

if($#seq_info >= 0){

   # 回到 IFOUND 檔頭 

   if(seek(IFOUND,0,0) != 1){
      &err(__LINE__);
   }

   # 略過前五行 guid, risk, encrypt, identity_type and lmtime

   $line = <IFOUND>;
   $line = <IFOUND>;
   $line = <IFOUND>;
   $line = <IFOUND>;
   $line = <IFOUND>;

   # 取出 filepath

   $info = $seq_info[0];
   $seq_info_index = 0;
   $seq = 0;
   while(<IFOUND>){

      ++$seq;
      next if($seq != $info->{'seq'});

      chomp;

      ($xmlid,$create_time,$hostname,$domain_name,$login_name,$department) = split("\t",$_);
      ($year,$mon) = $create_time =~ /(\d\d\d\d)-(\d\d)/;

      $meta =  "$META_FOLDER_PREFIX/$guid/$year$mon/$xmlid";
      $path_file = "$meta.path";

      if(!open(PATH,$path_file)){
         &err(__LINE__);
      }

      # 將 filepath meta file 全部讀到 memory
      
      @path = <PATH>;

      # add computer,login_name,filepath to info structure

      $info->{'department'} = $department;
      $info->{'hostname'} = $hostname;
      $info->{'domain_name'} = $domain_name;
      $info->{'login_name'} = $login_name;
      chomp($info->{'path'} = $path[$info->{'index'}]);

      while($seq_info_index < $#seq_info){
         $info = $seq_info[++$seq_info_index];
         last if($seq != $info->{'seq'});
         $info->{'department'} = $department;
         $info->{'hostname'} = $hostname;
         $info->{'domain_name'} = $domain_name;
         $info->{'login_name'} = $login_name;
         chomp($info->{'path'} = $path[$info->{'index'}]);
      }

      close(PATH);

      # seq_info 全部處理完畢
      
      #last if($seq_info_index ==  $#seq_info);

   }
   

   # top 1000 extreme data

   if($#final_top1000_extreme_info >= 0){

      for(@final_top1000_extreme_info){

         ################
         # get basic info
         ################
         $xmlid = $_->{'xmlid'};
         $hostname = $_->{'hostname'};
         $domain_name = $_->{'domain_name'};
         $login_name = $_->{'login_name'};
         $department = $_->{'department'};

         ###########################
         # get file last modify time
         ###########################
         ($day,$mon,$year) = (localtime($_->{'mtime'}))[3,4,5];
         ++$mon;
         $year += 1900;
         $mdate = sprintf("%4d-%02d-%02d",$year,$mon,$day);

         #############################
         # get file path and file type
         #############################
         $filepath = $_->{'path'};
         ($filetype) = $filepath =~ /\.([^.]+)$/;

         ################
         # get data count
         ################
         $data = $_->{'data'};

         ###################
         # get identity type
         ###################
         $type = $_->{'type'};
         $datatype = '';
         for my $type_index (0 .. $#TYPE_NAME_MAP){
            if($type & (1 << $type_index)){
               $datatype .= "$TYPE_NAME_MAP[$type_index],";
            }
         }
         chop($datatype);  # 去掉結尾的逗號


         print RESULT "$xmlid\t$domain_name/$hostname\t$login_name\t$department\t$EXTREME_RISK_NAME\t$mdate\t$filetype\t$data\t$datatype\t$filepath\n";
      }

   }

   # top n high data

   if($#final_topn_high_info >= 0){

      for(@final_topn_high_info){

         ################
         # get basic info
         ################
         $xmlid = $_->{'xmlid'};
         $hostname = $_->{'hostname'};
         $domain_name = $_->{'domain_name'};
         $login_name = $_->{'login_name'};
         $department = $_->{'department'};

         ###########################
         # get file last modify time
         ###########################
         ($day,$mon,$year) = (localtime($_->{'mtime'}))[3,4,5];
         ++$mon;
         $year += 1900;
         $mdate = sprintf("%4d-%02d-%02d",$year,$mon,$day);

         #############################
         # get file path and file type
         #############################
         $filepath = $_->{'path'};
         ($filetype) = $filepath =~ /\.([^.]+)$/;

         ################
         # get data count
         ################
         $data = $_->{'data'};

         ###################
         # get identity type
         ###################
         $type = $_->{'type'};
         $datatype = '';
         for my $type_index (0 .. $#TYPE_NAME_MAP){
            if($type & (1 << $type_index)){
               $datatype .= "$TYPE_NAME_MAP[$type_index],";
            }
         }
         chop($datatype);  # 去掉結尾的逗號


         print RESULT "$xmlid\t$domain_name/$hostname\t$login_name\t$department\t$HIGH_RISK_NAME\t$mdate\t$filetype\t$data\t$datatype\t$filepath\n";
      }

   }

   # top n medium data

   if($#final_topn_medium_info >= 0){

      for(@final_topn_medium_info){

         ################
         # get basic info
         ################
         $xmlid = $_->{'xmlid'};
         $hostname = $_->{'hostname'};
         $domain_name = $_->{'domain_name'};
         $login_name = $_->{'login_name'};
         $department = $_->{'department'};

         ###########################
         # get file last modify time
         ###########################
         ($day,$mon,$year) = (localtime($_->{'mtime'}))[3,4,5];
         ++$mon;
         $year += 1900;
         $mdate = sprintf("%4d-%02d-%02d",$year,$mon,$day);

         #############################
         # get file path and file type
         #############################
         $filepath = $_->{'path'};
         ($filetype) = $filepath =~ /\.([^.]+)$/;

         ################
         # get data count
         ################
         $data = $_->{'data'};

         ###################
         # get identity type
         ###################
         $type = $_->{'type'};
         $datatype = '';
         for my $type_index (0 .. $#TYPE_NAME_MAP){
            if($type & (1 << $type_index)){
               $datatype .= "$TYPE_NAME_MAP[$type_index],";
            }
         }
         chop($datatype);  # 去掉結尾的逗號


         print RESULT "$xmlid\t$domain_name/$hostname\t$login_name\t$department\t$MEDIUM_RISK_NAME\t$mdate\t$filetype\t$data\t$datatype\t$filepath\n";
      }

   }

}

# 如果@encrypt_info有東西，把它寫到result
if(defined(@encrypt_info)){
   print RESULT @encrypt_info;
}

close(RESULT);

close(IFOUND);
unlink $iFound_file;

sub err{

   $line = shift;

   print "error occur at $line line.\n";

   exit -1;

}

