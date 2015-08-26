<?
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["fid"])){
         $fileid = $_GET["fid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"文件不存在！")); //-2没有传文件ID
         return; 
      }
      if(isset($_GET["ftype"])){
         $filetype = $_GET["ftype"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"文件不存在！")); //-2没有传文件属性
         return; 
      }
      if(isset($_GET["fe"])){
         $fileExtension = $_GET["fe"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"文件不存在！")); //-2没有传文件属性
         return; 
      }
   }
   else{
      echo json_encode(array("status"=>-1, "result"=>"文件不存在！")); //-1没有传任何参数
      return;
   }
   $file_name=$fileid . "." . $fileExtension;
   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
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
   define("FILE_PATH", $file_path);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   $filepath = FILE_PATH . "/uploads/" . $filetype . "/" . $file_name;
   if   (!file_exists($filepath))   {   //检查文件是否存在 
     echo   "文件找不到"; 
     exit;   
   }
   else{
      // 输入文件标签
      Header("Content-type: application/octet-stream");
      Header("Accept-Ranges: bytes");
      Header("Accept-Length: " . filesize($filepath));
      Header("Content-Disposition: attachment; filename=" . $file_name);
      // 输出文件内容
      //echo fread($file,filesize($file_dir . $file_name));
      $file = fopen($filepath,"r"); // 打开文件
      while(1) {
         $str = fread($file,1024);
         echo $str;
         if (strlen($str) < 1024)
            break;
      }
      fclose($file);
      exit();
   }
?>