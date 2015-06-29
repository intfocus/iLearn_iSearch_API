#!/usr/bin/perl -w

use POSIX qw(mkfifo);
use Fcntl;

$TIMEOUT = 10;
$FIFO_FILE = 'fifo';
$FIFO_MSG = "save img ok\n";
$SUCC_MSG = "success\n";

$TIMEOUT_MSG = "timeout\n";

$ERR_MSG_PREFIX = "ERROR:";
$ERR_MSG_MKDIR = "mkdir  failed\n";
$ERR_MSG_OPEN_IMG = "open img file failed\n";
$ERR_MSG_READ_IMG = "read img failed\n";
$ERR_MSG_WRITE_IMG = "write img failed\n";
$ERR_MSG_MKFIFO = "mkfifo failed\n";
$ERR_MSG_OPEN_FIFO = "open fifo file failed\n";
$ERR_MSG_WRITE_FIFO = "write fifo file failed\n";

$FOLDER_PREFIX = '/usr/local/www/apache22/data/report';

print "Content-Type: text/html\r\n\r\n";

$query_string = $ENV{QUERY_STRING};

@params = split('&',$query_string);
for(@params){
   ($name,$value) = split('=',$_);
   $param{$name} = $value;
}

$folder = "$FOLDER_PREFIX/$param{fileFolder}";
$chart_name = $param{name};

if(system("mkdir -p $folder")){
   &die_msg($ERR_MSG_MKDIR);
}

$ret = sysopen(IMG,"$folder/$chart_name",O_CREAT|O_WRONLY);
if(!defined($ret)){
   &die_msg($ERR_MSG_OPEN_IMG);
}

$img_length = $ENV{CONTENT_LENGTH};

$read_img_len = 0;
$out = '';
while(1){
   $ret = sysread(STDIN,$buf,$img_length - $read_img_len);
   if(!defined($ret)){
      &die_msg($ERR_MSG_READ_IMG);
   }
   $read_img_len += $ret;
   $out .= $buf;
   if($read_img_len == $img_length){
      last;
   }
   if($read_img_len > $img_length){
      &die_msg($ERR_MSG_READ_IMG);
   }
}

$ret = syswrite(IMG,$out,$img_length);
if(!defined($ret) or $ret != $img_length){
   &die_msg($ERR_MSG_WRITE_IMG);
}

#$write_img_len = 0;
#while(1){
#   $ret = syswrite(IMG,$out,$img_length - $write_img_len);
#   if(!defined($ret)){
#      &die_msg($ERR_MSG_WRITE_IMG);
#   }
#   $write_img_len += $ret;
#   if($write_img_len == $img_length){
#      last;
#   }
#   if($write_img_len > $img_length){
#      &die_msg($ERR_MSG_WRITE_IMG);
#   }
#}

close(IMG);

eval{

   local $SIG{ALRM} = sub {
      &die_msg($TIMEOUT_MSG);
   };
   alarm $TIMEOUT;
   
   $fifo_file = "$folder/$FIFO_FILE";

   $ret = mkfifo($fifo_file,0666);
   if(!defined($ret)){
      if(!-p $fifo_file){
         &die_msg($ERR_MSG_MKFIFO);
      }
   }

   $ret = sysopen(FIFO,$fifo_file,O_WRONLY);

   if(!defined($ret)){
      &die_msg($ERR_MSG_OPEN_FIFO);
   }

   $write_len = length($FIFO_MSG);
   $ret = syswrite(FIFO,$FIFO_MSG,$write_len);
   if(!defined($ret) or $ret != $write_len){
      &die_msg($ERR_MSG_WRITE_FIFO);
   }

   alarm 0;

};


if($@){
   print $@;
}
else{
   print $SUCC_MSG;
}

sub die_msg{

   $err_msg = shift;
   die $ERR_MSG_PREFIX. $err_msg;

}

