<script type="text/javascript">
//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchUsers(UserId, Status)
{
   //ajax
   str = "cmd=actionUsers" + "&UserId=" + UserId + "&Status=" + Status;
   url_str = "User/Users_action.php?";
   
   //alert(str);
   //$('#loadingWrap').show();
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(url_str + str);
      },
      type: 'GET',
      url: url_str + str,
      cache: false,
      success: function(res)
      {
         //alert(res);
         $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
         {
            if (!res.match(/^-\d+$/))  //success
            {
               document.getElementsByName("searchUsersButton")[0].click();
            }
            else  //failed
            {  
               //alert(res);
               alert(MSG_SEARCH_ERROR);
            }
         });
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}

//***Step10 列表中动作删除Ajax呼叫
function deleteSearchUsers(UserId)
{
   ret = confirm("确定要删除此用户吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteUsers" + "&" + "UserId=" + UserId;
   url_str = "User/Users_delete.php?";
   
   //alert(str);
   //$('#loadingWrap').show();
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(url_str + str);
      },
      type: 'GET',
      url: url_str + str,
      cache: false,
      success: function(res)
      {
         //alert(res);
         $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
         {
            if (!res.match(/^-\d+$/))  //success
            {
               document.getElementsByName("searchUsersButton")[0].click();
            }
            else  //failed
            {  
               //echo "1.0";
               alert(MSG_SEARCH_ERROR);
            }
         });
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}

//***Step11 列表中动作修改Ajax长出修改页面
function modifySearchUsers(UserId)
{
   str = "cmd=read&UserId=" + UserId;
   url_str = "User/Users_modify.php?";
   window.open(url_str + str);

   //alert(str);
   // $.ajax
   // ({
      // beforeSend: function()
      // {
         // //alert(str);
      // },
      // type: "GET",
      // url: url_str + str,
      // cache: false,
      // success: function(res)
      // {
         // //alert("Data Saved: " + res);
         // if (res.match(/^-\d+$/))  //failed
         // {
            // alert(MSG_OPEN_CONTENT_ERROR);
         // }
         // else  //success
         // {
            // document.getElementById("searchUsersContent").innerHTML = res;		
            // $('.blockUI').show();
         // }
      // },
      // error: function(xhr)
      // {
         // alert("ajax error: " + xhr.status + " " + xhr.statusText);
      // }
   // });
}

function clickSearchUsersPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_user_page active")
      return;
   nPage = document.getElementsByName("search_user_page_no")[0].value;
   document.getElementsByName("search_user_page_no")[0].value = n;
   str = "search_user_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_user_page";
   str = "search_user_page_end_no_" + nPage;
   document.getElementById(str).className = "search_user_page";
   str = "search_user_page_begin_no_" + n;
   document.getElementById(str).className = "search_user_page active";
   str = "search_user_page_end_no_" + n;
   document.getElementById(str).className = "search_user_page active";	
   
   //clear current table
   str = "search_user_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_user_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchUsersContentFunc()
{ 
   str = "cmd=read&UserId=0";
   url_str = "User/Users_modify.php?";
   window.open(url_str + str);

   //alert(str);
   // $.ajax
   // ({
      // beforeSend: function()
      // {
         // //alert(str);
      // },
      // type: "GET",
      // url: url_str + str,
      // cache: false,
      // success: function(res)
      // {
         // //alert("Data Saved: " + res);
         // if (res.match(/^-\d+$/))  //failed
         // {
            // alert(MSG_OPEN_CONTENT_ERROR);
         // }
         // else  //success
         // {
            // document.getElementById("searchUsersContent").innerHTML = res;		
            // $('.blockUI').show();
         // }
      // },
      // error: function(xhr)
      // {
         // alert("ajax error: " + xhr.status + " " + xhr.statusText);
      // }
   // });
}

function newUsersBatch()
{
   window.open("User/Users_batch_add.php?cmd=read");
}

function delUsersBatch()
{
   window.open("User/Users_batch_del.php?cmd=read");
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchUsersContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>用户名称/用户邮箱/工号/部门名称 ：</th>
               <td><input id="searchUsersNameEmail" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchUsersCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchUsersCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td>
                  <input id="from3" type="text" name="searchUsersfrom1" class="from" readonly="true"/> ~ <input id="to3" type="text" class="to" name="searchUsersto1" readonly="true"/>
               </td>
               <th>是否为审批者 ：</th>
               <td colspan="3">
                  <label><input id="searchUsersRadio1" name="CanA" type="radio" value="" />是</label>
                  <label><input id="searchUsersRadio2" name="CanA" type="radio" value="" />否</label>
                  <label><input id="searchUsersRadio3" name="CanA" type="radio" value="" checked />全部</label>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchUsers"><input name="searchUsersButton" type="button" value="开始查询"></a>
                  <a class="btn_submit_new newUsersBatch">
                     <input name="newUsersBatchButton" type="button" value="批次新增" OnClick="newUsersBatch();"></a>
                  <a class="btn_submit_new delUsersBatch">
                     <input name="delUsersBatchButton" type="button" value="批次离职" OnClick="delUsersBatch();"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchUsersPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchUsersContentFunc();">新增</span>
            </div>
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
                  <col class="UserAction" />
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
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchUsersContentFunc();">新增</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->