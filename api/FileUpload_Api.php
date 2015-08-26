<?php
print_r($_GET["ftype"]);
echo "<pre>";
print_r($_POST);
echo "<pre>";
print_r($_FILES);
echo "</pre>";
// 
// if(move_uploaded_file($_FILES['upimg']['tmp_name'],$_FILES['upimg']['name'])){
 // echo 'ok';
// }
   define("FILE_NAME", "../DB.conf");
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   
   //define
   define("File_PATH", $file_path);
   $savefile = File_PATH . "/uploads/" . $_GET["ftype"];
   if (!file_exists($savefile))
   {
      mkdir ($savefile);
      //echo '创建文件夹test成功';
   } 
   // else 
   // {
      // echo '需创建的文件夹test已经存在';
   // }
   // print_r($_POST);
   // echo "<br />";
   // echo "===file upload info:";
   // print_r($_FILES);
   if(move_uploaded_file($_FILES['file']['tmp_name'],$savefile . "/" . $_FILES['file']['name'])){
      echo 'ok';
   }
   else {
       echo 'error';
   }
?>