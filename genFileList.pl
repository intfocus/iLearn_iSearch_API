#!/usr/bin/perl -w

use Fcntl;
use File::Basename;
use Encode;

$META_FOLDER_PREFIX = '/usr/local/www/apache22/data/upload_old';
$COUNT_FIELD = 8;   # count0 ~ count7 , �ثe count7 ���w�d���
$COUNT_FIELD_SIZE = $COUNT_FIELD * 4;   # �C�� count �� 4bytes unsigned int , ���n�ç�o�ӼƦr

# �[ -c �ѼƬO���F�Ȧ� utf8 ������ big5 ���r���i�H��������
$ICONV_CMD = '/usr/local/bin/iconv -c -f utf-8 -t big5';

###################
# count0 -> ������
# count1 -> ������X
# count2 -> �a�}
# count3 -> �q�l�l��a�}
# count4 -> �H�Υd���X
# count5 -> �m�W
# count6 -> ���ܸ��X
###################
@TYPE_NAME_MAP = ('������', '������X', '�a�}', '�q�l�l��a�}', '�H�Υd���X', '�m�W', '���ܸ��X');
for(@TYPE_NAME_MAP){
   $_ = encode('utf8',decode('big5',$_));
}

@RISK_LEVEL = ('����','��','��');
for(@RISK_LEVEL){
   $_ = encode('utf8',decode('big5',$_));
}

$HANDLE = encode('utf8',decode('big5','�d��'));

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

      # ��Ӹ�ƶq

      $data = 0;

      for my $identity_type (@g_identity_type){
         $data += $count[$count_index + $identity_type];
      }

      # �ⷥ��
      
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
      
      if($data >= $g_risk_high){   # �Ⱚ
         $risk = 1;
         &print_out;
      }
      elsif($data > $g_risk_low and $data < $g_risk_high){   # �⤤
         $risk = 2;
         &print_out;
      }

   }

   close(PATH);

}

close(OUT);

if($no > 0){

   # �N out_utf8 �ন big5 �~���� excel Ū

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

   # �h���̫᪺�r��
   chop($type_name);

   # �h�� path �̫᪺����r��
   chomp($path);

   print OUT "$risk\t$type_name\t$data\t$HANDLE\t$path\n";

}

sub err{

   $line = shift;

   print "error occur at $line line.\n";

   exit -1;

}

