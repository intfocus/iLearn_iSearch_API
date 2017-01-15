<?
// if( empty($_GET['FileName'])|| empty($_GET['FileDir'])|| empty($_GET['FileId'])){
    // echo'<script> alert("非法连接 !"); location.replace ("index.php") </script>'; exit();
// }
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["fid"])){
         $fileid = $_GET["fid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"分类夹下文件不存在！")); //-2没有传部门ID
         return; 
      }
   }
   else{
      echo json_encode(array("status"=>-1, "result"=>"分类夹下文件不存在！")); //-1没有传任何参数
      return;
   }
   
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

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $file_dir;
   $file_type;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $file_size;
   $str_user = "select FilePath, ZipSize, FileType from files where FileId = " . $fileid;
   if($result = mysqli_query($link, $str_user)){
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         $filerow = mysqli_fetch_assoc($result);
         $file_dir = $filerow["FilePath"] . "/";
		 $file_size = $filerow["ZipSize"];
		 $file_type = $filerow["FileType"];
      }
      else {
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         // echo -__LINE__;
         echo json_encode(array("status"=>0, "result"=>"文件不存在！")); 
         return;
      }
   }
   if($file_type == 3)
   {
	   $file_name=$fileid . ".zip";
   }
   else
   {
	   $file_name=$fileid . ".zip";
   }
   mysqli_close($link);
   if   (!file_exists($file_dir.$file_name))   {   //检查文件是否存在 
     echo   "文件找不到"; 
     exit;   
   }
   else{
      // 输入文件标签
	  
      Header("Content-type: application/octet-stream");
      Header("Accept-Ranges: bytes");
      Header("Accept-Length: " . $file_size);
      Header("Content-Disposition: attachment; filename=" . $file_name);
	  
      // 输出文件内容
      //echo fread($file,filesize($file_dir . $file_name));
	  $filepath = $file_dir . $file_name;
	  $file = fopen($filepath,"r"); // 打开文件
	  //while(!feof($file)) {
      //   echo fgets($file, 1024);
      //}
	  while(1) {
        $str = fread($file,1024);
        echo $str;
        if(strlen($str) < 1024)
           break;
      }
      fclose($file);
      exit();
   }
?>