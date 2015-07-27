<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchRollCallsContentFunc()
{
   if ($('span.RollCallName, span.RollCallReason').hasClass('fixWidth'))
   {
      $('span.RollCallName, span.RollCallReason').removeClass('fixWidth');
      $('.RollCallsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.RollCallName, span.RollCallReason').addClass('fixWidth');
      $('.RollCallsexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchRollCalls(RollCallId, Status)
{
   //ajax
   str = "cmd=actionRollCalls" + "&RollCallId=" + RollCallId + "&Status=" + Status;
   url_str = "RollCall/RollCalls_action.php?";
   
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
               document.getElementsByName("searchRollCallsButton")[0].click();
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

//***Step10 列表中动作删除Ajax呼叫
function deleteSearchRollCalls(RollCallId)
{
   ret = confirm("确定要删除此公告吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteRollCalls" + "&" + "RollCallId=" + RollCallId;
   url_str = "RollCall/RollCalls_delete.php?";
   
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
               document.getElementsByName("searchRollCallsButton")[0].click();
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
function modifySearchRollCalls(RollCallId)
{
   str = "cmd=read&RollCallId=" + RollCallId;
   url_str = "RollCall/RollCalls_modify.php?";
   window.open(url_str + str);
}

function clickSearchRollCallsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_RollCall_page active")
      return;
   nPage = document.getElementsByName("search_RollCall_page_no")[0].value;
   document.getElementsByName("search_RollCall_page_no")[0].value = n;
   str = "search_RollCall_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_RollCall_page";
   str = "search_RollCall_page_end_no_" + nPage;
   document.getElementById(str).className = "search_RollCall_page";
   str = "search_RollCall_page_begin_no_" + n;
   document.getElementById(str).className = "search_RollCall_page active";
   str = "search_RollCall_page_end_no_" + n;
   document.getElementById(str).className = "search_RollCall_page active";	
   
   //clear current table
   str = "search_RollCall_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_RollCall_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function RollCallSearchRollCallsContentFunc()
{ 
   str = "cmd=read&RollCallId=0";
   url_str = "RollCall/RollCalls_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchRollCallsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课程名称/用户名称 ：</th>
               <td><input id="searchRollCallsName" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchRollCallsCheckBox1" type="checkbox" checked>未到</label>
                  <label><input id="searchRollCallsCheckBox2" type="checkbox" checked>已到</label>
                  <label><input id="searchRollCallsCheckBox3" type="checkbox" checked>取消</label>
               </td>
            </tr>
            <tr>
               <th>点名开始时间 ：</th>
               <td colspan="3">
                  <input id="from12" type="text" name="searchRollCallsfrom12" class="from" readonly="true"/> ~ <input id="to12" type="text" class="to" name="searchRollCallsto12" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchRollCalls"><input name="searchRollCallsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchRollCallsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span class="btn RollCallsexpandSR" OnClick="expandSearchRollCallsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TrainingName" />
                  <col class="UserName" />
                  <col class="IssueDate" />
                  <col class="Status" />
                  <col class="CreatedUser" />
                  <col class="Reason" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>用户名称</th>
                  <th>点名时间</th>
                  <th>到场状态</th>
                  <th>发起点名用户</th>
                  <th>原因说明</th>
               </tr>
               <tr>
                  <td colspan="7" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span class="btn RollCallsexpandSR" OnClick="expandSearchRollCallsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->