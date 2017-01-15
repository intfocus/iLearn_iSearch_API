<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchTrainingLogsContentFunc()
{
   if ($('span.TrainingMemo, span.CancelMsg, span.TraineeName').hasClass('fixWidth'))
   {
      $('span.TrainingMemo, span.CancelMsg, span.TraineeName').removeClass('fixWidth');
      $('.TrainingLogsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.TrainingMemo, span.CancelMsg, span.TraineeName').addClass('fixWidth');
      $('.TrainingLogsexpandSR').text('显示过长内容');
   }
}

function clickSearchTrainingLogsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_TrainingLog_page active")
      return;
   nPage = document.getElementsByName("search_TrainingLog_page_no")[0].value;
   document.getElementsByName("search_TrainingLog_page_no")[0].value = n;
   str = "search_TrainingLog_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_TrainingLog_page";
   str = "search_TrainingLog_page_end_no_" + nPage;
   document.getElementById(str).className = "search_TrainingLog_page";
   str = "search_TrainingLog_page_begin_no_" + n;
   document.getElementById(str).className = "search_TrainingLog_page active";
   str = "search_TrainingLog_page_end_no_" + n;
   document.getElementById(str).className = "search_TrainingLog_page active";	
   
   //clear current table
   str = "search_TrainingLog_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_TrainingLog_page" + n;
   document.getElementById(str).style.display = "block";
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchTraineesContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课程名称/讲师名称/学员（名称/编号)搜索 ：</th>
               <td><input id="searchTrainingLogsNameSpeaker" type="text" maxlength="50"></td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchTrainingLogs"><input name="searchTrainingLogsButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchTrainingLogsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu2">
               <span class="btn btn-primary btn-rounded m-b-5" OnClick="expandSearchTrainingLogsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TraineeName" />
                  <col class="TrainingMemo" />
                  <col class="Speaker" />
                  <col class="UserName" />
                  <col class="ActionName" />
				  <col class="strteStatus" />
                  <col class="CancelMsg" />
				  <col class="struser" />
                  <col class="ActionDate" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>课程简介</th>
                  <th>讲师</th>
                  <th>学员姓名</th>
                  <th>操作</th>
				  <th>审核状态</th>
                  <th>撤销原因</th>
				  <th>审核人</th>
                  <th>操作时间</th>
               </tr>
               <tr>
                  <td colspan="10" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu2">
               <span class="btn btn-primary btn-rounded m-b-5" OnClick="expandSearchTrainingLogsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->