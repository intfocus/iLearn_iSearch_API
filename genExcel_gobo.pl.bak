#!/usr/bin/perl -w
#########################
# Modification History
# #001 Phantom, 2012/09/27           Add riskCategorySelect
#                                    riskCategorySelect=2 代表只有極高+高 印到報表
#                                    riskCategorySelect=3 代表極高+高+中 印到報表
# #002 Odie, 2013/01/21              新增sheet3.tsv，由*.action產生，表示檔案的處置方式
# #003 Odie, 2013/02/20              若*.action不存在，去找之前最近的*.action，
#                                    再和這次的*.path file合併(將前次"排除"者帶到這次)
# #004 Odie, 2013/03/05              重寫#003, 再加上diff的部分
#                                    1. diff: 從*.iFoundLast找到上次掃描的結果並和這次比較
#                                    2. 將前次排除者帶到這次: 從*.iAction找
# #005 Odie, 2013/03/27              1. 工業局需求，產生報表要加上file last modified time, 開*.mtime來讀
#                                    2. 解勾選中度風險檔案列表時，excel會錯掉的bug
# #006 Odie, 2013/06/28              新增sheet4.tsv, 會寫入已加密檔案的清單
# #007 Odie, 2013/07/22              寫sheet1.tsv會在最後寫入email
# #008 Odie, 2013/09/11              iFound file第二行會加上第八種type的名字，以便在產生sheet2.tsv時可以替換
# #009 Odie, 2013/09/17              sheet1.tsv會在每次掃瞄的最後寫入scan_express_timeout和scan_express_count(從iFound file來)
# #010 Odie, 2013/10/17              國寶服務，sheet1.tsv最後寫入使用者帳號(com_login_name)
#########################

use Fcntl;
use File::Basename;
use Encode;

$META_FOLDER_PREFIX = '/usr/local/www/apache22/data/upload_old';
$COUNT_FIELD = 8;   # count0 ~ count7 , 目前 count7 為預留欄位
$COUNT_FIELD_SIZE = $COUNT_FIELD * 4;   # 每個 count 用 4bytes unsigned int , 不要亂改這個數字
$FILE_HANDLE_DEFAULT = 0;               # 預設風檢檔案的處置方式

# 加 -c 參數是為了怕有 utf8 不能轉 big5 的字元可以直接忽略
$ICONV_CMD = '/usr/local/bin/iconv -c -f utf-8 -t big5';

# get pattern list prog
$GET_PATTERN_LIST_PROG = '/usr/local/bin/php /usr/local/www/apache22/data/getPatternList.php';

###################
# count0 -> 身分證
# count1 -> 手機號碼
# count2 -> 地址
# count3 -> 電子郵件地址
# count4 -> 信用卡號碼
# count5 -> 姓名
# count6 -> 市話號碼
# count7 -> 生日(預設)
###################
@TYPE_NAME_MAP = ('身分證', '手機號碼', '地址', '電子郵件地址', '信用卡號碼', '姓名', '市話號碼', '生日');
#for(@TYPE_NAME_MAP){
#   $_ = encode('utf8',decode('big5',$_));
#}

@RISK_LEVEL = ('極高','高','中');
#for(@RISK_LEVEL){
#   $_ = encode('utf8',decode('big5',$_));
#}

###############
# count0 -> 3
# count1 -> 1
# count2 -> 1
# count3 -> 1
# count4 -> 3
# count5 -> 1
# count6 -> 1
###############

@TYPE_GRADE_MAP = (3, 1, 1, 1, 3, 1, 1);

$iFound_file = $ARGV[0];
$report_dir = dirname($iFound_file);

# #004 add the two following files
$iFoundLast_file = $ARGV[1];
$iAction_file = $ARGV[2];

$sheet1_utf8 = "$report_dir/sheet1.tsv.utf8";
$sheet1_big5 = "$report_dir/sheet1.tsv";
$sheet2_utf8 = "$report_dir/sheet2.tsv.utf8";
$sheet2_big5 = "$report_dir/sheet2.tsv";

# #002 add the two following files
$sheet3_utf8 = "$report_dir/sheet3.tsv.utf8";
$sheet3_big5 = "$report_dir/sheet3.tsv";

$pattern_list_utf8 = "$report_dir/pattern_list.tsv.utf8";
$pattern_list_big5 = "$report_dir/pattern_list.tsv";

# #006 add
$sheet4_utf8 = "$report_dir/sheet4.tsv.utf8";
$sheet4_big5 = "$report_dir/sheet4.tsv";

# #005 add
$desc_file = "$report_dir/desc";

$fileid_file = "$report_dir/fileid";
#$debug_file = "$report_dir/phantom.log";

if(!open(IFOUND,$iFound_file)){
   &err(__LINE__);
}

# #003 add
if(!open(IFOUNDLAST,$iFoundLast_file)){
   &err(__LINE__);
}
# #004 add
if(!open(IACTION,$iAction_file)){
   &err(__LINE__);
}

# 用 append 的 , 因為第一行是公司名稱

if(!open(SHEET1,">>$sheet1_utf8")){
   &err(__LINE__);
}

if(!open(SHEET2,">$sheet2_utf8")){
   &err(__LINE__);
}

# #002 Add
if(!open(SHEET3,">$sheet3_utf8")){
   &err(__LINE__);
}

# #006 Add
if(!open(SHEET4,">$sheet4_utf8")){
   &err(__LINE__);
}

#if(!open(DEBUGLOG,">$debug_file")){
#   &err(__LINE__);
#}

# #005 add, write computer number, risk category select to sheet1.tsv
if(!open(DESC, $desc_file)){
   &err(__LINE__);
}
chomp($desc_line = <DESC>);
@meta_info = split('\t', $desc_line);
$computer_count = $meta_info[0];
# $pdata_computer_count = $meta_info[1];
$risk_category_select = $meta_info[2];
print SHEET1 "$computer_count\t$risk_category_select\n";
close(DESC);

chomp($guid = <IFOUND>);
chomp($risk = <IFOUND>);
chomp($identity_type = <IFOUND>);

# #004 Add
for($i = 0; $i < 3; ++$i){
   <IFOUNDLAST>;
   <IACTION>;
}


#($g_risk_low,$g_risk_high,$g_risk_extreme,$g_risk_extreme_type_num,$risk_extreme_type) = split("\t",$risk); before #001 modified 
# #008 modified
($g_risk_low,$g_risk_high,$g_risk_extreme,$g_risk_extreme_type_num,$risk_extreme_type,$riskCategorySelect,$type8_enable,$type8_name) = split("\t",$risk);
if($type8_enable == 1){
   $TYPE_NAME_MAP[7] = $type8_name;
}
@g_risk_extreme_type = split(",",$risk_extreme_type);
@g_identity_type = split("\t",$identity_type);

$sheet1_no = 0;
$sheet2_no = 0;
$sheet3_no = 0; #002 Add
$sheet4_no = 0; #006 Add

while(<IFOUND>){

   ++$sheet1_no;

   chomp;
   
   # #004 Add
   chomp($iFoundLast_line = <IFOUNDLAST>);
   chomp($iAction_line = <IACTION>);

   ############################
   ## Read from iFound, split with TAB, store to some variables
   ############################
   
   # #009 modified, #010 modified
   ($xmlid,$create_time,$ip,$hostname,$domain_name,$login_name,$department,$start_time,$end_time,$spent_time,$total_file,$employee_email,$scan_express_timeout,$scan_express_count,$com_login_name) = split("\t",$_);
   ($year,$mon) = $create_time =~ /(\d\d\d\d)-(\d\d)/;

   ############################
   ## Read Meta count
   ############################
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

   ############################
   # #005 begin
   #  Read Meta mtime
   ############################
   $mtime_file = "$meta.mtime";

   $mtime_size = -s "$mtime_file";
   if(!defined($mtime_size)){
      &err(__LINE__);
   }
   
   $ret = sysopen(MTIME,$mtime_file,O_RDONLY);
   if(!defined($ret)){
      &err(__LINE__);
   }

   $ret = sysread(MTIME,$buf,$count_size);
   if(!defined($ret)){
      &err(__LINE__);
   }
   elsif($ret != $mtime_size){
      &err(__LINE__);
   }

   close(MTIME);
   
   @mtime = unpack('L*',$buf);

   ############################
   # #005 end
   #  Read Meta mtime
   ############################

   ############################
   ## Read Meta path
   ############################
   $path_file = "$meta.path";

   ############################
   # #004 begin
   #  parse iAction_line, 若action不為-1且xmlid和iFound的不同，
   #  則和這次的*.path合併(將先前"排除"的檔案帶到這次的*.action)
   ############################
   ($iAction_id,$iAction_time) = split("\t",$iAction_line);
   if($iAction_id != -1){
      $action_file = "$meta.action";
      ($iAction_year,$iAction_mon) = $iAction_time =~ /(\d\d\d\d)-(\d\d)/;

      if($iAction_id != $xmlid){
         $last_action_file = "$META_FOLDER_PREFIX/$guid/$iAction_year$iAction_mon/$iAction_id.action";
         # 呼叫merge_file, 合併上次的action和這次的path到這次的action file
         if(merge_file($path_file, $last_action_file, $action_file) == -1){
            &err(__LINE__);
         }
      }
      if(!open(ACTION, $action_file)){
         &err(__LINE__);
      }
   }
   ############################
   # #004 end
   ############################

   if(!open(PATH,$path_file)){
      &err(__LINE__);
   }

   $total_extreme_file = 0;
   $total_high_file = 0;
   $total_medium_file = 0;
   $total_low_file = 0;

   ############################
   # #004 begin
   #  宣告紀錄未處置檔案的相關變數
   ############################

   $total_extreme_file_unhandled = 0;
   $total_high_file_unhandled = 0;
   $file_handle_status = 0;
   $total_file_unhandled = 0;

   ############################
   # #004 end
   ############################
   
   $num = $count_size / $COUNT_FIELD_SIZE;

   @fileid = ();

   for($i = 0; $i < $num; ++$i){

      $count_index = $i * $COUNT_FIELD;

      $path = <PATH>;
      
      # #004 Add
      $action = <ACTION> if(tell(ACTION) != -1);
      
      # #005 Add, convert Unix epoch to local time
      my($sec, $min, $hour, $day, $month, $year) = (localtime($mtime[$i]))[0, 1, 2, 3, 4, 5];
      $year = $year + 1900;
      $month = $month + 1;
      $last_mtime = sprintf("%d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $min, $sec);
      
      # 算個資數量

      $data = 0;

      # 加總有勾選的個資 from count[]
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

      #################################
      ## 找到的 file 如果屬於極高, 在這邊寫入 sheet2.tsv
      #################################
      if($risk_extreme_type_num >= $g_risk_extreme_type_num){
         ++$total_extreme_file;
         $risk = $RISK_LEVEL[0];
         push(@fileid,$i);
         &print_sheet2;
         # #004 begin
         $file_handle_status = &print_sheet3;
         if($file_handle_status == 0){
            ++$total_extreme_file_unhandled;
         }
         # #004 end
         next;
      }
      
      #################################
      ## 找到的 file 如果屬於高, 在這邊寫入 sheet2.tsv
      #################################
      if($data >= $g_risk_high){   # 算高
         ++$total_high_file;
         $risk = $RISK_LEVEL[1];
         push(@fileid,$i);
         &print_sheet2;
         # #004 begin
         $file_handle_status = &print_sheet3;
         if($file_handle_status == 0){
            ++$total_high_file_unhandled;
         }
         # #004 end
      }
      #################################
      ## 找到的 file 如果屬於中, 在這邊寫入 sheet2.tsv
      #################################
      # elsif($data > $g_risk_low and $data < $g_risk_high){   # 算中 before #001 modified
      elsif($data > $g_risk_low and $data < $g_risk_high){   # 算中
         ++$total_medium_file;
         if ($riskCategorySelect == 3){
            $risk = $RISK_LEVEL[2];
            push(@fileid,$i);
            &print_sheet2;
            # 目前未處置檔案不考慮中風險，故直接寫入sheet3.tsv
            &print_sheet3; # #004 Add
         }
      }
      #################################
      ## 目前 低 不用出現在報表中, 所以不用 print_sheet2
      #################################
      elsif($data > 0 and $data <= $g_risk_low){   # 算低
         ++$total_low_file;
      }

   }

   close(PATH);
   close(ACTION) if(tell(ACTION) != -1);  # #004 Add

   ############################
   ## #004 begin
   ## Start to process the last scan record
   ############################

   ($iFoundLast_id,$iFoundLast_time) = split("\t",$iFoundLast_line);

   # if $xmlid_last != -1, start to process
   if($iFoundLast_id != -1){
      ($iFoundLast_year,$iFoundLast_mon) = $iFoundLast_time =~ /(\d\d\d\d)-(\d\d)/;
      $iFoundLast_meta =  "$META_FOLDER_PREFIX/$guid/$iFoundLast_year$iFoundLast_mon/$iFoundLast_id";
      $iFoundLast_count_file = "$iFoundLast_meta.count";
      
      $iFoundLast_count_size = -s "$iFoundLast_count_file";
      if(!defined($iFoundLast_count_size)){
         &err(__LINE__);
      }
   
      $ret = sysopen(IFOUNDLAST_COUNT,$iFoundLast_count_file,O_RDONLY);
      if(!defined($ret)){
         &err(__LINE__);
      }

      $ret = sysread(IFOUNDLAST_COUNT,$buf,$iFoundLast_count_size);
      if(!defined($ret)){
         &err(__LINE__);
      }
      elsif($ret != $iFoundLast_count_size){
         &err(__LINE__);
      }

      close(IFOUNDLAST_COUNT);

      $iFoundLast_total_extreme_file = 0;
      $iFoundLast_total_high_file = 0;
      $iFoundLast_total_medium_file = 0;
      $iFoundLast_total_low_file = 0;
      $iFoundLast_total_data_file = 0;

      @iFoundLast_count = unpack('L*',$buf);
      $iFoundLast_num = $iFoundLast_count_size / $COUNT_FIELD_SIZE;

      for($i = 0; $i < $iFoundLast_num; ++$i){
         
         $count_index = $i * $COUNT_FIELD;
         $iFoundLast_data = 0;

         # 加總有勾選的個資 from count[]
         for my $identity_type (@g_identity_type){
            $iFoundLast_data += $iFoundLast_count[$count_index + $identity_type];
         }

         # 算極高
      
         $risk_extreme_type_num = 0;
   
         for my $extreme_type (@g_risk_extreme_type){ 
            if($iFoundLast_count[$count_index + $extreme_type] >= $g_risk_extreme){
               ++$risk_extreme_type_num; 
            }
         }

         #################################
         ## 找到的 file 如果屬於極高
         #################################
         if($risk_extreme_type_num >= $g_risk_extreme_type_num){
            ++$iFoundLast_total_extreme_file;
            next;
         }  
      
         #################################
         ## 找到的 file 如果屬於高
         #################################
         if($iFoundLast_data >= $g_risk_high){   # 算高
            ++$iFoundLast_total_high_file;
         }
         #################################
         ## 找到的 file 如果屬於中
         #################################
         # elsif($data > $g_risk_low and $data < $g_risk_high){   # 算中 before #001 modified
         elsif($iFoundLast_data > $g_risk_low and $iFoundLast_data < $g_risk_high){   # 算中
            ++$iFoundLast_total_medium_file;
         }
         #################################
         ## 目前 低 不用出現在報表中, 所以不用 print_sheet2
         #################################
         elsif($iFoundLast_data > 0 and $iFoundLast_data <= $g_risk_low){   # 算低
            ++$iFoundLast_total_low_file;
         }
      }
      $iFoundLast_total_data_file = $iFoundLast_total_extreme_file + $iFoundLast_total_high_file + $iFoundLast_total_medium_file + $iFoundLast_total_low_file;

   }
   else{
      $iFoundLast_total_data_file = -1;
      $iFoundLast_total_extreme_file = -1;
      $iFoundLast_total_high_file = -1;
      $iFoundLast_total_medium_file = -1;
      $iFoundLast_total_low_file = -1;
   }
   ############################
   ## #004 end
   ############################

   # #004 Add
   $total_extreme_and_high_file = $total_extreme_file + $total_high_file;
   $total_file_unhandled = $total_extreme_file_unhandled + $total_high_file_unhandled;

   $total_data_file = $total_extreme_file + $total_high_file + $total_medium_file + $total_low_file;

   #################################
   # get pattern list
   # 呼叫 getPatternList.php 去讀取 xml 內容找到 masked strings
   #################################
   if($#fileid >= 0){
      if(!open(FILEID,">$fileid_file")){
         &err(__LINE__);
      }
      print FILEID pack('L*',@fileid);
      $cmd = "$GET_PATTERN_LIST_PROG $report_dir $META_FOLDER_PREFIX/$guid/$year$mon/$xmlid-$guid.xml " . join(',',@g_identity_type) . " $total_extreme_and_high_file";
      $ret = `$cmd`;
      if($ret !~ /SUCCESS/){
         &err(__LINE__);
      }
      close(FILEID);
   }

   ############################
   ## #006 begin
   ############################

   $encrypt_file = "$meta.encrypt";
   $total_encrypt = 0;
   if(-e $encrypt_file){
      if(!open(ENCRYPT_FILE,"<$encrypt_file")){
         &err(__LINE__);
      }
      chomp($encrypt_line = <ENCRYPT_FILE>);
      $total_encrypt = $encrypt_line;
      while($encrypt_path = <ENCRYPT_FILE>){
         chomp($encrypt_path);
         &print_sheet4;
      }
      close(ENCRYPT_FILE);
   }
   # write sheet1 result
  
   #print SHEET1 "$sheet1_no\t$department\t$login_name\t$domain_name/$hostname\t$ip\t$total_file\t$total_data_file\t$total_extreme_file\t$total_high_file\t$total_medium_file\t$total_low_file\t$start_time\t$end_time\t$spent_time\n";
   # #004 Add
   # 因應新版的EXCEL報表，增加sheet1.tsv寫入的資料欄位
   #print SHEET1 "$sheet1_no\t$department\t$login_name\t$domain_name/$hostname\t$ip\t$total_file\t$total_data_file\t$total_file_unhandled\t$total_extreme_file\t$total_high_file\t$total_medium_file\t$total_low_file\t$iFoundLast_total_data_file\t$iFoundLast_total_extreme_file\t$iFoundLast_total_high_file\t$iFoundLast_total_medium_file\t$iFoundLast_total_low_file\t$start_time\t$end_time\t$spent_time\n";
   
   # #006 Add
   # 因應新版的EXCEL報表，增加sheet1.tsv寫入的資料欄位
   # #007, add employee_email in the last field
   # #009, add scan_express_timeout and scan_express_count in the last field
   # #010, add scan_express_timeout and scan_express_count in the last field
   print SHEET1 "$sheet1_no\t$department\t$login_name\t$domain_name/$hostname\t$ip\t$total_file\t$total_data_file\t$total_file_unhandled\t$total_extreme_file\t$total_high_file\t$total_medium_file\t$total_low_file\t$iFoundLast_total_data_file\t$iFoundLast_total_extreme_file\t$iFoundLast_total_high_file\t$iFoundLast_total_medium_file\t$iFoundLast_total_low_file\t$start_time\t$end_time\t$spent_time\t$total_encrypt\t$employee_email\t$scan_express_timeout\t$scan_express_count\t$com_login_name\n";

}

close(SHEET1);
close(SHEET2);
close(SHEET3);
close(SHEET4);

# 將 sheet1 and sheet2 and sheet3 and sheet4 轉成 big5 才能讓 excel 讀

system("$ICONV_CMD $sheet1_utf8 > $sheet1_big5");
system("$ICONV_CMD $sheet2_utf8 > $sheet2_big5");
system("$ICONV_CMD $sheet3_utf8 > $sheet3_big5");
system("$ICONV_CMD $sheet4_utf8 > $sheet4_big5");
system("$ICONV_CMD $pattern_list_utf8 > $pattern_list_big5");

unlink($sheet1_utf8,$sheet2_utf8,$sheet3_utf8,$sheet4_utf8,$pattern_list_utf8);

sub print_sheet2{

   ++$sheet2_no;

   $type_name = '';
   $grade = 0;

   for my $identity_type (@g_identity_type){

      if($count[$count_index + $identity_type]){
         $type_name .= "$TYPE_NAME_MAP[$identity_type]($count[$count_index + $identity_type]),";
         $grade += $TYPE_GRADE_MAP[$identity_type];
      }

   }

   # 去掉最後的逗號
   chop($type_name);

   # 去掉 path 最後的換行字元
   chomp($path);

   ($file_name) = $path =~ /([^\\]+)$/;
   ($filetype) = $path =~ /\.([^.]+)$/;
   # #010
   print SHEET2 "$sheet2_no\t$department\t$login_name\t$domain_name/$hostname\t$com_login_name\t$file_name\t$path\t$filetype\t$risk\t$type_name\t$data\t$grade\t$last_mtime\n";

}
############################
## #004 begin
############################
sub print_sheet3{
   my ($action_filename, $file_handle, $move_path);
   ++$sheet3_no;
   
   # 如果*.action file存在，直接印出
   if(tell(ACTION) != -1){
      # 因為action file是由windows產生，要考慮換行是\r\n的情形，否則會多印空白行
      $action =~ s/\r?\n$//;
      ($action_filename, $file_handle, $move_path) = split("\t", $action);
      print SHEET3 "$sheet3_no\t$action_filename\t$file_handle\t$move_path\n";
      return $file_handle;
   }
   else{
      # 如果*.action file不存在，則將$path中的path取出，再加上檔案預設的處置方式
      if($path =~ /\n$/){
         chomp($path);
      }
      print SHEET3 "$sheet3_no\t$path\t$FILE_HANDLE_DEFAULT\n";
      return $FILE_HANDLE_DEFAULT;
   }
}
############################
## #004 end
############################

############################
## #006 begin
############################
sub print_sheet4{

   ++$sheet4_no;

   # 去掉 path 最後的換行字元
   # chomp($path);

   ($file_name) = $encrypt_path =~ /([^\\]+)$/;
   # ($filetype) = $encrypt_path =~ /\.([^.]+)$/;

   print SHEET4 "$sheet4_no\t$department\t$login_name\t$domain_name/$hostname\t$com_login_name\t$file_name\t$encrypt_path\n";

}
############################
## #006 end
############################

###########################
# #003
# 合併之前的action file和這次的path file, 成為新的action file
# parameter: (0)目前的path file (1)之前的action file (3)要寫進去的新action file
###########################
sub merge_file{
   my($path, $last_action, $action) = ($_[0], $_[1], $_[2]);
   my $ret = 1;
   my($path_filename, $action_line, $action_filename, $file_handle, $path_eq_action);
   ####################
   # 開檔並檢查
   ####################
   if(!open(PATH, "<$path")){
      $ret = -1;
      goto MERGE_FILE_EXIT;
   }
   if(!open(LAST_ACTION, "<$last_action")){
      $ret = -1;
      goto MERGE_FILE_EXIT;
   }
   if(-e $action){
      goto MERGE_FILE_EXIT;
   }
   elsif(!open(ACTION, ">$action")){
      $ret = -1;
      goto MERGE_FILE_EXIT;
   }
   $path_eq_action = 1;
   ####################
   # 1. 如果path file還有內容，就讀取一行
   ####################
   while($path_filename = <PATH>){
      chomp($path_filename);
      ####################
      # 1.1 如果action file還有內容，就讀一行，否則進入下一回
      ####################
      if($path_eq_action){
         if($action_line = <LAST_ACTION>){
            chomp($action_line);
            ($action_filename, $file_handle) = split("\t", $action_line);
         }
         else{
            print ACTION "$path_filename\t0\n";
            goto PATH_LOOP;
         }
      }
      ####################
      # 1.2 比對path和action
      #     1.2.1 若path比較小，就持續讀path直到path大於等於action，若path讀完直接跳出大迴圈(path每讀一行都要寫出去)
      #     1.2.2 當path大於等於action時，比較path是否等於action，若等於則看處置狀況(file_handle)是否大於等於4(包含排除)
      #           若path等於action，大迴圈進入下一回(path和action各再讀一行)
      #     1.2.3 若action比較小，就持續讀action直到action大於等於path，或action讀完為止
      #     1.2.4 當action大於等於path時，比較path是否等於action，若等於則看處置狀況(file_handle)是否大於等於4(包含排除)
      #           若path等於action，大迴圈進入下一回(path和action各再讀一行)
      ####################
      while($path_filename lt $action_filename){
         $path_eq_action = 0;
         print ACTION "$path_filename\t0\n";
         if($path_filename = <PATH>){
            chomp($path_filename);
         }
         else{
            goto MERGE_FILE_EXIT;
         }
      }
      print ACTION "$path_filename\t";
      if($path_filename eq $action_filename){
         $path_eq_action = 1;
         # 只要file_handle大於等於4就表示"排除"這個bit是on的
         if($file_handle =~ m/[4-7]/){
            print ACTION "4\n";
         }
         else{
            print ACTION "0\n";
         }
         next;
      }
      while($action_filename lt $path_filename){
         $path_eq_action = 0;
         if($action_line = <LAST_ACTION>){
            chomp($action_line);
            ($action_filename, $file_handle) = split("\t", $action_line);
         }
         else{
            print ACTION "0\n";
            goto PATH_LOOP;
         }
      }
      if($path_filename eq $action_filename){
         $path_eq_action = 1;
         if($file_handle =~ m/[4-7]/){
            print ACTION "4\n";
         }
         else{
            print ACTION "0\n";
         }
      }
      else{
         $path_eq_action = 0;
         print ACTION "0\n";
      }
   }
PATH_LOOP:
   while($path_filename = <PATH>){
      chomp($path_filename);
      print ACTION "$path_filename\t0\n";
   }

MERGE_FILE_EXIT:
   if(tell(PATH) != -1){
      close(PATH);
   }
   if(tell(LAST_ACTION) != -1){
      close(LAST_ACTION);
   }
   if(tell(ACTION) != -1){
      close(ACTION);
   }
   $ret;
}
###########################
# End #003
###########################

sub err{

   $line = shift;

   print "error occur at $line line.\n";

   exit -1;

}

