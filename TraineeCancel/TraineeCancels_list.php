<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchTraineeCancelsContentFunc()
{
   if ($('span.TraineeName, span.SpeakerName, span.TrainingMemo, span.CancelMsg').hasClass('fixWidth'))
   {
      $('span.TraineeName, span.SpeakerName, span.TrainingMemo, span.CancelMsg').removeClass('fixWidth');
      $('.TraineeCancelsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.TraineeName, span.SpeakerName, span.TrainingMemo, span.CancelMsg').addClass('fixWidth');
      $('.TraineeCancelsexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchTraineeCancels(TrainingId, Status, UserId)
{
   ret = confirm("确定要通过审核吗?\n\r无法撤销本操作!");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=actionTraineeCancels&TrainingId=" + TrainingId + "&Status=" + Status + "&UserId=" + UserId;
   url_str = "TraineeCancel/TraineeCancels_action.php?";

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
               document.getElementsByName("searchTraineeCancelsButton")[0].click();
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
function deleteSearchTraineeCancels(TrainingId,UserId)
{
   ret = confirm("确定要审核驳回吗?\n\r无法撤销本操作!");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteTraineeCancels&TrainingId=" + TrainingId + "&UserId=" + UserId;
   url_str = "TraineeCancel/TraineeCancels_delete.php?";
   
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
               document.getElementsByName("searchTraineeCancelsButton")[0].click();
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
function modifySearchTraineeCancelExamines(TraineeId)
{
   str = "cmd=read&TraineeId=" + TraineeId;
   url_str = "TraineeCancel/TraineeCancels_modify.php?";
   window.open(url_str + str);
}

function clickSearchTraineeCancelsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_TraineeCancel_page active")
      return;
   nPage = document.getElementsByName("search_TraineeCancel_page_no")[0].value;
   document.getElementsByName("search_TraineeCancel_page_no")[0].value = n;
   str = "search_TraineeCancel_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_TraineeCancel_page";
   str = "search_TraineeCancel_page_end_no_" + nPage;
   document.getElementById(str).className = "search_TraineeCancel_page";
   str = "search_TraineeCancel_page_begin_no_" + n;
   document.getElementById(str).className = "search_TraineeCancel_page active";
   str = "search_TraineeCancel_page_end_no_" + n;
   document.getElementById(str).className = "search_TraineeCancel_page active";	
   
   //clear current table
   str = "search_TraineeCancel_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_TraineeCancel_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function TraineesearchTraineeCancelsContentFunc()
{ 
   str = "cmd=read&TraineeId=0";
   url_str = "TraineeCancel/TraineeCancels_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
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
               <td><input id="searchTraineeCancelsNameSpeaker" type="text" maxlength="50"></td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchTraineeCancels"><input name="searchTraineeCancelsButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchTraineeCancelsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu2">
               <span class="btn btn-primary btn-rounded m-b-5" OnClick="expandSearchTraineeCancelsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TraineeName" />
                  <col class="TrainingMemo" />
                  <col class="Speaker" />
                  <col class="UserName" />
                  <col class="EmployeeId" />
                  <col class="Date" />
                  <col class="CancelMsg" />
                  <col class="TraineeCancelsAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>课程简介</th>
                  <th>讲师</th>
                  <th>学员姓名</th>
                  <th>学员编号</th>
                  <th>报名时间</th>
                  <th>撤销原因</th>
                  <th>操作</th>
               </tr>
               <tr>
                  <td colspan="9" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu2">
               <span class="btn btn-primary btn-rounded m-b-5" OnClick="expandSearchTraineeCancelsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->