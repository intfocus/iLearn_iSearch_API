<?
// if( empty($_GET['FileName'])|| empty($_GET['FileDir'])|| empty($_GET['FileId'])){
    // echo'<script> alert("非法连接 !"); location.replace ("index.php") </script>'; exit();
// }
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["cid"])){
         $coursewaresid = $_GET["cid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"课件文件不存在！")); //-2没有传课件ID
         return; 
      }
   }
   else{
      echo json_encode(array("status"=>-1, "result"=>"课件文件不存在！")); //-1没有传任何参数
      return;
   }
   $file_name=$coursewaresid . ".pdf";
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
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
   define("FILE_PATH", $file_path);

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   
   $file_dir = FILE_PATH . "/coursepacket/";
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   if   (!file_exists($file_dir.$file_name))   {   //检查文件是否存在 
     echo   "文件找不到"; 
     exit;   
   }
   else{
      $filepath = $file_dir . $file_name;
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