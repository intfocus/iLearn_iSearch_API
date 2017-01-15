<script type="text/javascript">
//***Step11 列表中动作修改Ajax长出修改页面
function modifySearchPrivileges(UserId)
{
   str = "cmd=read&UserId=" + UserId;
   url_str = "Admin/Privileges_modify.php?";
   window.open(url_str + str);
}

function clickSearchPrivilegesPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_privilege_page active")
      return;
   nPage = document.getElementsByName("search_privilege_page_no")[0].value;
   document.getElementsByName("search_privilege_page_no")[0].value = n;
   str = "search_privilege_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_privilege_page";
   str = "search_privilege_page_end_no_" + nPage;
   document.getElementById(str).className = "search_privilege_page";
   str = "search_privilege_page_begin_no_" + n;
   document.getElementById(str).className = "search_privilege_page active";
   str = "search_privilege_page_end_no_" + n;
   document.getElementById(str).className = "search_privilege_page active";	
   
   //clear current table
   str = "search_privilege_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_privilege_page" + n;
   document.getElementById(str).style.display = "block";
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchPrivilegesContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>用户名称/用户邮箱/工号/部门名称 ：</th>
               <td><input id="searchPrivilegesNameEmail" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchPrivilegesCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchPrivilegesCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td>
                  <input id="from7" type="text" name="searchPrivilegesfrom1" class="from" readonly="true"/> ~ <input id="to7" type="text" class="to" name="searchPrivilegesto1" readonly="true"/>
               </td>
               <th>是否为审批者 ：</th>
               <td colspan="3">
                  <label><input id="searchPrivilegesRadio1" name="CanA" type="radio" value="" />是</label>
                  <label><input id="searchPrivilegesRadio2" name="CanA" type="radio" value="" />否</label>
				  <label><input id="searchPrivilegesRadio3" name="CanA" type="radio" value="" checked />全部</label>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchPrivileges"><input name="searchPrivilegesButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchPrivilegesPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="UserName" />
                  <col class="EmployeeId" />
                  <col class="UserEmail" />
                  <col class="UserDept" />
                  <col class="Status" />
                  <col class="CanApprove" />
                  <col class="EditTime" />
                  <col class="action" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>用户名称</th>
                  <th>工号</th>
                  <th>用户邮箱</th>
                  <th>所属部门</th>
                  <th>状态</th>
                  <th>是否为审批者</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="9" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->