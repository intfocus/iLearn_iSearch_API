<?php
///////////////////////////////
// systemAdminMgt.php
//
// 系統管理者 可以新增, 刪除, 修改系統管理者 (systemLogin)
//
// #000 created by Phantom 2014/09/16
// #001 modified by Odie   2014/11/25
//      1. 修改 return 的頁面中關於管理者帳號格式的說明（可用_及.)
//      2. 將 user input 加上 escape
///////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);

   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(FILE_ERROR, -2);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(MAPPING_ERROR, -5);
   
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

   session_start();
   if ($_SESSION["GUID_ADM"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit(); 
   }
   $login_name = $_SESSION["loginName"];
   session_write_close();
   
   //define
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(TIME_ZONE, "Asia/Taipei");
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(DEFAULT_GUID, "000000000000000000000000000000000000");
   define(STR_LENGTH, 50);
   define(EXTREME_TYPE_NUMBER, '7');                      //個資類型
   define(SEARCH_RISK_LIMIT, 3);                          //極高+高
   define(SEARCH_SIZE, 1000);                             //上限1000筆
   define(PAGE_SIZE, 1000);
   define(SEARCH_EXTREME, 2);
   define(SEARCH_HIGH, 1);
   define(TRIAL, 0);


   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "new") && strcmp($check_str, "delete") && strcmp($check_str, "modify"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   function check_illegal($check_str)
   {
      if (strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   
   //data
   if (($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if (($account = check_illegal($_GET["account"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   $password = $_GET["password"];
   if ($cmd != "delete")
   {
      if (strlen($password) < 8)
      {
         sleep(DELAY_SEC);
         echo SYMBOL_ERROR;
         return;
      }
      $password = hash('md5', $password);
   }
   else if ($account == $login_name)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }
   
   $account = mysqli_real_escape_string($link, $account);   // #001

   if ($cmd == "new")
   {
      $sql = "insert systemLogin (login_name,password,status) VALUES('$account','$password',1)";
   }
   else if ($cmd == "delete")
   {
      $sql = "delete from systemLogin where login_name='$account'";
   }
   else if ($cmd == "modify")
   {
      $sql = "update systemLogin set password='$password' where login_name='$account'";
   }
   if ($result = mysqli_query($link, $sql)) {
      // Successfully insert / delete / update
   }
   else {
      // Failed to insert / delete / update
      echo DB_ERROR;
      return;
   }
?>
        <table class="report" border="0" cellspacing="0" cellpadding="0">
            <colgroup>
               <col class="cIndex" />
               <col class="cName" />
               <col class="cPassword" />
               <col class="cActionAdm" />
            </colgroup>
            <tr>
               <th>序號</th>
               <th>管理者帳號</th>
               <th>管理者密碼</th>
               <th>功能</th>
            </tr>
<?php
   $total_count = 0;
   $str_query = "select * from systemLogin where status=1";
   if ($result = mysqli_query($link, $str_query)) {
      while ($row = mysqli_fetch_assoc($result)) {
         $total_count ++;
         echo "<tr>";
         echo "<td><span class='cIndex'>$total_count</span></td>";
         echo "<td><span class='cName'>" . $row["login_name"] . "</span></td>";
         echo "<td><span class='cPassword'><input type=password name=modifyAdminPassword size=50>(至少長度 8)</span></td>";
         echo "<td><span class='cActionAdm'>";
         if ($login_name != $row["login_name"])
            echo "<A onclick=\"modifyAdminDelete('" . $row["login_name"] . "');\">刪除</a>&nbsp;&nbsp;&nbsp;";
         echo "<A onclick=\"modifyAdminPasswd('" . $row["login_name"] . "',$total_count);\">修改密碼</a>";
         echo "</span></td>";
         echo "</tr>";
      }
   }
   else {
      if ($link) {
         mysqli_close($link);
         $link = 0;
      }
      echo DB_ERROR;
   }
?>
            <tr>
               <th>&nbsp;</th>
               <th>新增管理者 - 帳號</th>
               <th>新增管理者 - 密碼</th>
               <th>功能</th>
            </tr>
            <tr>
               <td><span class='cIndex'>NEW</span></td>
               <td><span class='cName'><Input type=text name=newAdminAccount size=50>(限定英數字、底線 (_) 及點 (.) , 至少長度 3)</span></td>
               <td><span class='cPassword'><Input type=password name=newAdminPassword size=50>(至少長度 8)</span></td>
               <td class="uSubmitW"><span class='cActionAdm'>
                  <A onclick="newSystemAdmin();">新增</a>
               </span></td>
            </tr>
         </table>
<?php
   if ($link)
      mysqli_close($link);
?>
