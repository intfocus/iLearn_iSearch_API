<div class="container systemAdmnMgtC" style="display:none;">
   <div class="searchW">
      <div class="uResultW" id="systemAdminMgtPages">
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
      </div>
   </div>
   <form name=modifySystemAdminForm>
      <input type=hidden name="submitAdminAction" value="">
      <input type=hidden name="submitAdminAccount" value="">
      <input type=hidden name="submitAdminPassword" value="">
      <input type="button" style="display:none" name="submitAdminButton" class="link_action modifyAdmin">
   </form>
</div>
