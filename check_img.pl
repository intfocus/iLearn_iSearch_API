#!/usr/bin/perl -w

use POSIX qw(mkfifo);
use Fcntl;

$CHART_COUNT = 7;
$TIMEOUT = 30;
$FIFO_FILE = 'fifo';
$FIFO_MSG = "save img ok\n";
$SUCC_MSG = "success\n";

$TIMEOUT_MSG = "timeout\n";
$ERR_MSG_PREFIX = "ERROR:";
$ERR_MSG_MKDIR = "mkdir failed\n";
$ERR_MSG_MKFIFO = "mkfifo failed\n";
$ERR_MSG_OPEN_FIFO = "open fifo file failed\n";
$ERR_MSG_READ_FIFO = "read fifo file failed\n";

$FOLDER_PREFIX = '/usr/local/www/apache22/data/report';

print "Content-Type: text/html\r\n\r\n";

$query_string = $ENV{QUERY_STRING};

@params = split('&',$query_string);
for(@params){
   ($name,$value) = split('=',$_);
   $param{$name} = $value;
}

$folder = "$FOLDER_PREFIX/$param{fileFolder}";

if(system("mkdir -p $folder")){
   &die_msg($ERR_MSG_MKDIR);
}

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

   $i = 0;
   $buf = '';
CHECK_LABEL:
   while($i < $CHART_COUNT){

      $ret = sysopen(FIFO,$fifo_file,O_RDONLY);
      if(!defined($ret)){
         &die_msg($ERR_MSG_OPEN_FIFO);
      }

      $read_len = length($FIFO_MSG);
      while($i < $CHART_COUNT){
         
         $ret = sysread(FIFO,$buf,$read_len);
         if(!defined($ret)){
            &die_msg($ERR_MSG_READ_FIFO);
         }
         if($ret != $read_len){
            if($ret == 0){
               close(FIFO);
               next CHECK_LABEL;
            }
            &die_msg($ERR_MSG_READ_FIFO);
         }
         ++$i;
      }
   }
   
   alarm 0;

};

if($@){
   unlink $fifo_file;
   print $@;
}
else{
   unlink $fifo_file;
   print $SUCC_MSG;
}


sub die_msg{
   
  $err_msg = shift;
  die $ERR_MSG_PREFIX. $err_msg;

}

   
