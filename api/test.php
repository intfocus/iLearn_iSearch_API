<?php
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
   $cpcount = 0;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $strlink = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
   $str_c = "select CoursewareId, CoursewareName, CoursewareDesc, CoursewareFile from Coursewares where CoursewareId>1 and CoursewareId<11";
   if($rsc = mysqli_query($strlink, $str_c)){
      while($row = mysqli_fetch_assoc($rsc)){      
	     $sccCoursewareId = $row['CoursewareId'];
         $sccCoursewareName = $row['CoursewareName'];
         $sccCoursewareDesc = $row['CoursewareDesc'];
         $sccCoursewareFile = $row['CoursewareFile'];
         $extensions = explode('.',$row['CoursewareFile']);
         $escount = count($extensions)-1;
         $sccExtension = $extensions[$escount];
		 echo $sccCoursewareId . "---" . $sccExtension . "<br />";
		 $str_query2 = "update coursewares set FileSize = '" . filesize("D:/Data/file/coursepacket/$sccCoursewareId." . $sccExtension) . "' where CoursewareId = $sccCoursewareId";
		 mysqli_query($link,$str_query2);
		 echo $str_query2 . "<br />";
      }
    }
    else
    {
       if($strlink){
          mysqli_close($strlink);
       }
       sleep(DELAY_SEC);
       return;
    }
   
   mysqli_close($link);    
   return;
?>
