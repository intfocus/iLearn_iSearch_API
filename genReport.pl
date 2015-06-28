#!/usr/bin/perl -w

use Fcntl;
use File::Basename;

$META_FOLDER_PREFIX = '/usr/local/www/apache22/data/upload_old';
$RESULT_TMP = 'result.tmp';
$COUNT_FIELD = 8;   # count0 ~ count7 , 目前 count7 為預留欄位
$COUNT_FIELD_SIZE = $COUNT_FIELD * 4;   # 每個 count 用 4bytes unsigned int , 不要亂改這個數字


$iFound_file = $ARGV[0];
$report_dir = dirname($iFound_file);

if(!open(IFOUND,$iFound_file)){
   &err(__LINE__);
}

if(!open(RESULT_TMP,">$report_dir/$RESULT_TMP")){
   &err(__LINE__);
}

chomp($guid = <IFOUND>);
chomp($risk = <IFOUND>);
chomp($identity_type = <IFOUND>);

($g_risk_low,$g_risk_high,$g_risk_extreme,$g_risk_extreme_type_num,$risk_extreme_type) = split("\t",$risk);
@g_risk_extreme_type = split(",",$risk_extreme_type);
@g_identity_type = split("\t",$identity_type);

$g_total_extreme_data = 0;
$g_total_high_data = 0;
$g_total_medium_data = 0;
$g_total_low_data = 0;

$seq = 0;

while(<IFOUND>){

   ##### 第幾筆個資
   ++$seq;

   chomp;

   ($xmlid,$create_time,$ip,$hostname,$domain_name,$login_name,$department) = split("\t",$_);
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

   $total_extreme_file = 0;
   $total_high_file = 0;
   $total_medium_file = 0;
   $total_low_file = 0;

   @extreme_data = ();
   @high_data = ();
   @extreme_index = ();
   @high_index = ();

   @count = unpack('L*',$buf);
   $num = $count_size / $COUNT_FIELD_SIZE;

   for($i = 0; $i < $num; ++$i){

      $count_index = $i * $COUNT_FIELD;

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
         $extreme_data[$total_extreme_file] = $data;
         $extreme_index[$total_extreme_file] = $i;
         ++$total_extreme_file;
         $g_total_extreme_data += $data;
         next;
      }
      
      if($data >= $g_risk_high){   # 算高
         $high_data[$total_high_file] = $data;
         $high_index[$total_high_file] = $i;
         ++$total_high_file;
         $g_total_high_data += $data;
      }
      elsif($data > $g_risk_low and $data < $g_risk_high){   # 算中
         ++$total_medium_file;
         $g_total_medium_data += $data;
      }
      elsif($data > 0 and $data <= $g_risk_low){   # 算低
         ++$total_low_file;
         $g_total_low_data += $data;
      }

   }

   # sort local top 10 extreme/high data count
 
   if($total_extreme_file > 0){

      # local top10 extreme

      $top_index  = $total_extreme_file >= 10 ? 9 : ($total_extreme_file - 1);
      @top10_extreme_index = (sort { $extreme_data[$b] <=> $extreme_data[$a] } (0 .. $#extreme_index))[0 .. $top_index];

      @top10_info = ();
      for my $index (0 .. $#top10_extreme_index){
         $m = $top10_extreme_index[$index];
         $info = {
            'index' => $extreme_index[$m],
            'data'  => $extreme_data[$m],
            'seq'   => $seq
         };
         push(@top10_info,$info);
      }

      # merge to final top10 extreme

      $m = 0;   # local top10 info index
      $n = 0;   # final top10 info index
      $top = 0;

      @work_info = ();
      while($m <= $#top10_info and $n <= $#final_top10_extreme_info and $top < 10){
         if($top10_info[$m]->{'data'} >= $final_top10_extreme_info[$n]->{'data'}){
            push(@work_info,$top10_info[$m]);
            ++$m;
         }
         else{
            push(@work_info,$final_top10_extreme_info[$n]);
            ++$n;
         }
         ++$top;
      }
      
      while($m <= $#top10_info and $top < 10){
         push(@work_info,$top10_info[$m]);
         ++$m;
         ++$top;
      }

      while($n <= $#final_top10_extreme_info and $top < 10){
         push(@work_info,$final_top10_extreme_info[$n]);
         ++$n;
         ++$top;
      }

      @final_top10_extreme_info = @work_info;

   }

   if($total_high_file > 0){

      # local top10 high

      $top_index  = $total_high_file >= 10 ? 9 : ($total_high_file - 1);
      @top10_high_index = (sort { $high_data[$b] <=> $high_data[$a] } (0 .. $#high_index))[0 .. $top_index];

      @top10_info = ();
      for my $index (0 .. $#top10_high_index){
         $m = $top10_high_index[$index];
         $info = {
            'index' => $high_index[$m],
            'data'  => $high_data[$m],
            'seq'   => $seq
         };
         push(@top10_info,$info);
      }

      # merge to final top10 high

      $m = 0;   # local top10 info index
      $n = 0;   # final top10 info index
      $top = 0;

      @work_info = ();
      while($m <= $#top10_info and $n <= $#final_top10_high_info and $top < 10){
         if($top10_info[$m]->{'data'} >= $final_top10_high_info[$n]->{'data'}){
            push(@work_info,$top10_info[$m]);
            ++$m;
         }
         else{
            push(@work_info,$final_top10_high_info[$n]);
            ++$n;
         }
         ++$top;
      }
      
      while($m <= $#top10_info and $top < 10){
         push(@work_info,$top10_info[$m]);
         ++$m;
         ++$top;
      }

      while($n <= $#final_top10_high_info and $top < 10){
         push(@work_info,$final_top10_high_info[$n]);
         ++$n;
         ++$top;
      }

      @final_top10_high_info = @work_info;

   }

   # write tmp result info to file
   
   print RESULT_TMP "$department\t$domain_name/$hostname\t$login_name\t$ip\t$total_extreme_file\t$total_high_file\t$total_medium_file\t$total_low_file\n";

}

close(RESULT_TMP);

# write extreme/high/medium/low total data count to file

if(!open(DATA_TOTAL,">$report_dir/data.total")){
   &err(__LINE__);
}

print DATA_TOTAL "$g_total_extreme_data\t$g_total_high_data\t$g_total_medium_data\t$g_total_low_data\n";

close(DATA_TOTAL);

# gen top 10 extreme/high file info

# top 10 extreme file

$result = `sort -t"\t" -k 5 -nr $report_dir/$RESULT_TMP | head -n 10`;
@top10 = split("\n",$result);
$top1 = shift @top10;
($department,$computer,$login_name,$ip,$extreme_file,$high_file,$medium_file,$low_file) = split("\t",$top1);

# 如果第一筆的 extreme_file > 0 才表示真的有 top 10 extreme file 

if($extreme_file > 0){

   if(!open(TOP10_EXTREME_FILE,">$report_dir/top10_extreme.file")){
      &err(__LINE__);
   }

   ($domain_name,$hostname) = split("/",$computer);

   $total_file = $extreme_file + $high_file + $medium_file + $low_file;
   print TOP10_EXTREME_FILE "$department\t$hostname\t$domain_name\t$login_name\t$total_file\t$extreme_file\n";

   for my $topn (@top10){

      ($department,$computer,$login_name,$ip,$extreme_file,$high_file,$medium_file,$low_file) = split("\t",$topn);

      last if($extreme_file <= 0);

      ($domain_name,$hostname) = split("/",$computer);

      $total_file = $extreme_file + $high_file + $medium_file + $low_file;
      print TOP10_EXTREME_FILE "$department\t$hostname\t$domain_name\t$login_name\t$total_file\t$extreme_file\n";

   }

   close(TOP10_EXTREME_FILE);

}

# top 10 high file

$result = `sort -t"\t" -k 6 -nr $report_dir/$RESULT_TMP | head -n 10`; 

@top10 = split("\n",$result);
$top1 = shift @top10;
($department,$computer,$login_name,$ip,$extreme_file,$high_file,$medium_file,$low_file) = split("\t",$top1);

# 如果第一筆的 high_file > 0 才表示真的有 top 10 high file 

if($high_file > 0){

   if(!open(TOP10_HIGH_FILE,">$report_dir/top10_high.file")){
      &err(__LINE__);
   }

   ($domain_name,$hostname) = split("/",$computer);

   $total_file = $extreme_file + $high_file + $medium_file + $low_file;
   print TOP10_HIGH_FILE "$department\t$hostname\t$domain_name\t$login_name\t$total_file\t$high_file\n";

   for my $topn (@top10){

      ($department,$computer,$login_name,$ip,$extreme_file,$high_file,$medium_file,$low_file) = split("\t",$topn);

      last if($high_file <= 0);

      ($domain_name,$hostname) = split("/",$computer);

      $total_file = $extreme_file + $high_file + $medium_file + $low_file;
      print TOP10_HIGH_FILE "$department\t$hostname\t$domain_name\t$login_name\t$total_file\t$high_file\n";

   }

   close(TOP10_HIGH_FILE);

}

# gen top 10 extreme/high data info

# 根據檔案編號和所在行數將 final_top10_extreme_info 和 final_top10_high_info 混合 sort

@seq_info = sort { $a->{'seq'} <=> $b->{'seq'} or $a->{'index'} <=> $b->{'index'} } @final_top10_extreme_info,@final_top10_high_info;

# 如果 seq_info 裡面有東西才需要開啟 filepath meta file 取出 filepath

if($#seq_info >= 0){

   # 回到 IFOUND 檔頭 

   if(seek(IFOUND,0,0) != 1){
      &err(__LINE__);
   }

   # 略過前三行 guid,risk and identity_type

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

      ($xmlid,$create_time,$ip,$hostname,$domain_name,$login_name,$department) = split("\t",$_);
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

}

close(IFOUND);

# top 10 extreme data

if($#final_top10_extreme_info >= 0){

   if(!open(TOP10_EXTREME_DATA,">$report_dir/top10_extreme.data")){
      &err(__LINE__);
   }

   for(@final_top10_extreme_info){

      $department = $_->{'department'};
      $hostname = $_->{'hostname'};
      $domain_name = $_->{'domain_name'};
      $login_name = $_->{'login_name'};
      $filepath = $_->{'path'};
      ($filetype) = $filepath =~ /\.([^.]+)$/;
      $data = $_->{'data'};

      print TOP10_EXTREME_DATA "$department\t$hostname\t$domain_name\t$login_name\t$filepath\t$filetype\t$data\n";
   }

   close(TOP10_EXTREME_DATA);

}

# top 10 high data

if($#final_top10_high_info >= 0){

   if(!open(TOP10_HIGH_DATA,">$report_dir/top10_high.data")){
      &err(__LINE__);
   }

   for(@final_top10_high_info){

      $department = $_->{'department'};
      $hostname = $_->{'hostname'};
      $domain_name = $_->{'domain_name'};
      $login_name = $_->{'login_name'};
      $filepath = $_->{'path'};
      ($filetype) = $filepath =~ /\.([^.]+)$/;
      $data = $_->{'data'};

      print TOP10_HIGH_DATA "$department\t$hostname\t$domain_name\t$login_name\t$filepath\t$filetype\t$data\n";
   }

   close(TOP10_HIGH_DATA);

}

sub err{

   $line = shift;

   print "error occur at $line line.\n";

   exit -1;

}

