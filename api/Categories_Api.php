<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["did"])){
         $deptid = $_GET["did"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"分类不存在！")); //-2没有传部门ID
         return; 
      }
      
      if(isset($_GET["pid"])){
         $parentid = $_GET["pid"];
      }
      else {
         echo json_encode(array("status"=>-3, "result"=>"分类不存在！")); //-3没有传分类父ID
         return; 
      }
   }
   else{
      echo json_encode(array("status"=>-1, "result"=>"分类不存在！")); //-1没有传任何参数
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
   
   header('Content-Type:application/json;charset=utf-8');
   
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
   $categorycount;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $datacategory = array();
   class Stucategory{
      public $categoryname;
      public $parentid;
      public $deptlist;
      public $filepath;
      public $palist;
      public $productlist;
      public $edittime;
   }
   
   //----- query -----
   $str_category = "select CategoryName, ParentId, DeptList, FilePath, PAList, ProductList, EditTime from categories where ParentId = $parentid and DeptList like '%," . $deptid . ",%';";
   if($rs = mysqli_query($link, $str_category)){
      $categorycount = mysqli_num_rows($rs);
      while($row = mysqli_fetch_assoc($rs)){      
         $sc = new Stucategory();
         $sc->categoryname = $row['CategoryName'];
         $sc->parentid = $row['ParentId'];
         $sc->deptlist = $row['DeptList'];
         $sc->filepath = $row['FilePath'];
         $sc->palist = $row['PAList'];
         $sc->productlist = $row['ProductList'];
         $sc->edittime = $row['EditTime'];
         array_push($datacategory,$sc);
      }
      // mysqli_close($link);
      
      //$data = doSql('SELECT nodeID id,fid,nodeName text FROM mytable');
      //$bta = new BuildTreeArray($data,'id','fid',0);
      //$data = $bta->getTreeArray();
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      // echo -__LINE__;
      echo json_encode(array("status"=> 0, "result"=>"分类获取失败！")); 
      return;
   }
   
   mysqli_close($link);
   echo json_encode(array("status"=> 1, "count"=>$categorycount, "data"=>$datacategory, "result"=>"分类获取成功！"));      
   return;
?>