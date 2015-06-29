<?php
///////////////////////////////
// customerMgt.php
//
// 系統管理者 可以新增, 刪除, 修改 Customer (name, loginName, password, validcode, email)
//
// #000 created by Phantom 2014/09/16
//
// #001 modified by Odie   2014/10/09
//      1. 若安泰conf存在，新增之前先找出System Admin remain還有多少，如果夠才新增，而且會從Admin remain扣除相對應的次數。
//         若安泰conf不存在，就直接新增。
//
// #002 modified by Odie   2014/11/13
//      1. 各個分行維護自己的 remain 不太可行，改成統一由 System Admin 的 remain 來扣
//
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

   //////////////////////////////
   // #001 Check 安泰銀行 conf 檔案是否存在 
   //////////////////////////////

   define(ANTIE_FILE_NAME, "/usr/local/www/apache22/entie.conf");
   if (file_exists(ANTIE_FILE_NAME))
      $entieFlag = 1;
   else
      $entieFlag = 0;

   session_start();
   if ($_SESSION["GUID_ADM"] == "" || $_SESSION["loginName"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main_adm.php");
      exit(); 
   }
   $GUID_ADM = $_SESSION["GUID_ADM"];
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
      if(strcmp($check_str, "new") && strcmp($check_str, "delete") && 
         strcmp($check_str, "modify_password") && strcmp($check_str, "modify_validcode") && strcmp($check_str, "modify_email"))
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
   $GUID = $_GET["GUID"];
   $name = $_GET["name"];
   $loginName = $_GET["loginName"];
   $password = $_GET["password"];
   $validcode = $_GET["validcode"];
   $email = $_GET["email"];


   if (check_illegal($GUID) == SYMBOL_ERROR)
   {
      // GUID could be empty, but not contain any illegal character
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   $len = strlen($loginName);
   if ($cmd == "new" && ($len < 3 || len > 31))
   {
      // loginName must between 3~31
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   if ($cmd == "new" && ereg("^[A-Za-z0-9_\.]+$", $loginName) == false)
   {
      // loginName can only be A-Za-z
      sleep(DELAY_SEC);
      echo __LINE__ . "<br>";
      echo SYMBOL_ERROR;
      return;
   }

   if ($cmd == "new" && strlen($name) <= 0)
   {
      // name cannot be empty 
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   $len = strlen($password);
   if (($cmd == "new" || $cmd == "modify_password") &&
       ($len < 8 || $len > 30))
   {
      // password must between 8~30
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   $password = hash('md5', $password);

   $len = strlen($validcode);
   if (($cmd == "new" || $cmd == "modify_validcode") &&
       ($len < 6 || $len > 12))
   {
      // validcode must between 6~12
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   if ($cmd == "new" || $cmd == "modify_email")
   {
      // email must contain "@" at least
      if (strchr($email,"@") == false)
      {
         sleep(DELAY_SEC);
         echo SYMBOL_ERROR;
         return;
      }
   }

   //----- Connect to MySql -----
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure   
   {   
      sleep(DELAY_SEC);
      echo DB_ERROR;                
      return;
   }

   $name = mysqli_real_escape_string($link, $name);
   $loginName = mysqli_real_escape_string($link, $loginName);
   $password = mysqli_real_escape_string($link, $password);
   $validcode = mysqli_real_escape_string($link, $validcode);
   $email = mysqli_real_escape_string($link, $email);

   if ($cmd == "new")
   {
      // Generate GUID
      require_once('/usr/local/www/apache22/web/guid.php');
      $guidclass = new Guid($loginName,"127.0.0.1");
      $GUID = $guidclass->toString();

      $sql = "insert customer (GUID,name,login_name,password,validcode,contact_email,status) 
              VALUES('$GUID','$name','$loginName','$password','$validcode','$email',1)";
   }
   else if ($cmd == "delete")
   {
      $sql = "Update customer set status=0 where GUID='$GUID'";
   }
   else if ($cmd == "modify_password")
   {
      $sql = "update customer set password='$password' where GUID='$GUID'";
   }
   else if ($cmd == "modify_validcode")
   {
      $sql = "update customer set validcode='$validcode' where GUID='$GUID'";
   }
   else if ($cmd == "modify_email")
   {
      $sql = "update customer set contact_email='$email' where GUID='$GUID'";
   }

   //echo $sql;
   //return;
   if (mysqli_query($link, $sql)) {
      // Successfully insert / delete / update

      if ($cmd == "new") { 
         ///////////////////////////////
         // If successfully insert
         // 1. copy riskCategory from $GUID_ADM (Current setting of System Admin)
         // 2. copy uploadMask, netDisk, removableDisk, conf, expressEnable, expressTimeout from $GUID_ADM 
         // 3. copy systemScanDir.txt from $GUID_ADM 
         // 4. copy whitelist.txt from $GUID_ADM 
         ///////////////////////////////

         // #001
         if ($entieFlag == 1)
         {
            $sql = "update customer set remain = remain - $remain where GUID = '$GUID_ADM'";
            mysqli_query($link,$sql);
         }


         $sql = "Insert into riskCategory (GUID,low,high,extreme,extreme_type_num,extreme_type) VALUES('$GUID',5,20,20,2,'0,1,2,3,4,5,6')";
         mysqli_query($link,$sql);

         $sql = "Update riskCategory t1, riskCategory t2
                  set t1.low=t2.low, t1.high=t2.high, t1.extreme=t2.extreme, t1.extreme_type_num=t2.extreme_type_num, t1.extreme_type=t2.extreme_type
                  where t1.GUID='$GUID' AND t2.GUID='$GUID_ADM'";
         mysqli_query($link,$sql);
         
         $sql = "Update customer t1, customer t2 
                  set t1.uploadMask=t2.uploadMask, t1.netDisk=t2.netDisk, t1.removableDisk=t2.removableDisk, 
                     t1.conf=t2.conf, t1.expressEnable=t2.expressEnable, t1.expressTimeout=t2.expressTimeout
                  where t1.GUID='$GUID' AND t2.GUID='$GUID_ADM'";
         mysqli_query($link,$sql);

         // #001
         if ($entieFlag == 1)
         {
            $sql = "update customer t1, customer t2 set t1.expire_time = t2.expire_time where t1.GUID = '$GUID' and t2.GUID = '$GUID_ADM'";
            mysqli_query($link,$sql);
         }

         $guid_dir_path = '/usr/local/www/apache22/data/upload_old' . "/$GUID";
         if (!file_exists($guid_dir_path)) {
            system("mkdir -p -m 0774 $guid_dir_path");
         }

         // copy systemScanDir.txt
         $tmpCmd = "cp /usr/local/www/apache22/data/upload_old/$GUID_ADM/systemScanDir.txt" . 
                     " /usr/local/www/apache22/data/upload_old/$GUID/systemScanDir.txt";
         system($tmpCmd);

         // copy whitelist.txt
         $tmpCmd = "cp /usr/local/www/apache22/data/upload_old/$GUID_ADM/whitelist.txt" . 
                      " /usr/local/www/apache22/data/upload_old/$GUID/whitelist.txt";
         system($tmpCmd);
      }
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
               <col class="cLoginName" />
               <col class="cPassword" />
               <col class="cValidcode" />
               <col class="cEmail" />
               <col class="cActionAdm" />
            </colgroup>
            <tr>
               <th>序號</th>
               <th>分行名稱</th>
               <th>分行管理者帳號</th>
               <th>分行管理者密碼</th>
               <th>用戶端盤點密碼</th>
               <th>分行管理者 Email</th>
               <th>功能</th>
            </tr>
<?php
   $total_count = 0;
   $str_query = "select * from customer where status=1 and GUID <> '00000000_0000_0000_0000_000000000000' order by name";
   if ($result = mysqli_query($link, $str_query)) {
      while ($row = mysqli_fetch_assoc($result)) {
         $total_count ++;
         echo "<tr>";
         echo "<td><span class='cIndex'>$total_count</span></td>";
         echo "<td><span class='cName'>" . $row["name"] . "</span></td>";
         echo "<td><span class='cLoginName'>" . $row["login_name"] . "</span></td>";
         echo "<td><span class='cPassword'><input type=password name=modifyCustomerPassword size=20></span></td>";
         echo "<td><span class='cValidcode'><input type=text name=modifyCustomerValidcode size=20 value='".$row["validcode"]."'></span></td>";
         echo "<td><span class='cEmail'><input type=text name=modifyCustomerEmail size=20 value='".$row["contact_email"]."'></span></td>";
         echo "<td><span class='cActionAdm'>";
         echo "<A onclick=\"modifyCustomerDelete('" . $row["GUID"] . "');\">刪除</a>&nbsp;&nbsp;";
         echo "<A onclick=\"modifyCustomerPasswd('" . $row["GUID"] . "',$total_count);\">修改密碼</a></br>";
         echo "<A onclick=\"modifyCustomerValidcode('" . $row["GUID"] . "',$total_count);\">修改盤點密碼</a></br>";
         echo "<A onclick=\"modifyCustomerEmail('" . $row["GUID"] . "',$total_count);\">修改 Email</a>";
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
               <th>分行名稱</th>
               <th>分行管理者帳號</th>
               <th>分行管理者密碼</th>
               <th>用戶端盤點密碼</th>
               <th>分行管理者 Email</th>
               <th>功能</th>

            </tr>
            <tr>
               <td><span class='cIndex'>NEW</span></td>
               <td><span class='cName'><Input type=text name=newCustomerName size=20></span></td>
               <td><span class='cLoginName'><Input type=text name=newCustomerLoginName size=20></span></td>
               <td><span class='cPassword'><Input type=password name=newCustomerPassword size=20></span></td>
               <td><span class='cValidcode'><Input type=text name=newCustomerValidcode size=20></span></td>
               <td><span class='cEmail'><Input type=text name=newCustomerEmail size=20></span></td>
               <td class="uSubmitW"><span class='cActionAdm'>
                  <A onclick="newCustomer();">新增</a>
               </span></td>
            </tr>
         </table>
