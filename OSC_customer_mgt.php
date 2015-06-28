<?php
/*****************************************
 * created by Phantom
 * #001 modified by Odie   2014/10/09
 *      1. 若安泰conf存在，顯示remain欄位，否則不顯示。
 * 
 * #002 各分行remain欄位移除，因統一由super user扣除，不需要此欄位
 *
*****************************************/

//////////////////////////////
// #001 Check 安泰銀行 conf 檔案是否存在 
//////////////////////////////

define(ANTIE_FILE_NAME, "/usr/local/www/apache22/entie.conf");
if (file_exists(ANTIE_FILE_NAME))
   $entieFlag = 1;
else
   $entieFlag = 0;
?>

<div class="container systemAdmnMgtC" style="display:none;">
   <div class="searchW">
      <div class="toolMenu">
         <span class="btn new" OnClick="window.open('upload_customerListPage.php');">匯入分行清單</span>
      </div>
      <div class="uResultW" id="customerMgtPages">
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
      </div>
   </div>
   <form name=modifyCustomerForm>
      <input type=hidden name="submitCustomerAction" value="">
      <input type=hidden name="submitCustomerGUID" value="">
      <input type=hidden name="submitCustomerName" value="">
      <input type=hidden name="submitCustomerLoginName" value="">
      <input type=hidden name="submitCustomerPassword" value="">
      <input type=hidden name="submitCustomerValidcode" value="">
      <input type=hidden name="submitCustomerEmail" value="">
      <input type="button" style="display:none" name="submitCustomerButton" class="link_action modifyCustomer">
   </form>
</div>
