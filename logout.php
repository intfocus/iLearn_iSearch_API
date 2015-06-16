<?php
   session_start();

   $_SESSION["GUID"] = "";
   $_SESSION["loginName"] = "";
   $_SESSION["username"] = "";
   session_write_close();
   header("Location:main.php?cmd=logout");
   exit();
?>
